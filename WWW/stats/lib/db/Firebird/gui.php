<?php
class estats_db_gui extends estats_db {
function data ($Group, $Amount, $From = 0, $To = 0) {
	$Data['data'] = array ();
	$Query = $this->time_clause ($From, $To);
	if ($Group == 'browsersversions' || $Group == 'osesversions') $Table = substr ($Group, 0, -8);
	else if ($Group == 'cities' || $Group == 'countries' || $Group == 'continents' || $Group == 'world' || substr ($Group, 0, 7) == 'country' || substr ($Group, 0, 6) == 'cities' || substr ($Group, 0, 7) == 'regions') $Table = 'geoip';
	else $Table = $Group;
	$GroupBy = '"name"';
	if ($Group == 'browsersversions' || $Group == 'osesversions') {
		$Result = ibase_query ($this->Link, 'SELECT FIRST '.$Amount.' "name", "version", SUM("amount") AS "amount" FROM "'.$this->Prefix.substr ($Group, 1).'"'.$Query.' GROUP BY "name", "version" ORDER BY "amount" DESC');
		while ($Row = ibase_fetch_row ($Result)) $Data['data'][$Row[0].' '.$Row[1]] = $Row[2];
		$GroupBy = '"name", "version"';
		}
	else if ($Group == 'sites') {
		$Result = ibase_query ($this->Link, 'SELECT FIRST '.$Amount.' "name", SUM("amount") AS "amount", "address" FROM "'.$this->Prefix.'sites"'.$Query.' GROUP BY "name", "address" ORDER BY "amount" DESC');
		while ($Row = ibase_fetch_row ($Result)) $Data['data'][$Row[2]] = array ($Row[1], ($Row[0]?$Row[0]:$Row[2]));
		$GroupBy = '"name", "address"';
		}
	else if (substr ($Group, 0, 6) == 'cities') {
		if (isset ($Group[6]) && $Group[6] == '_') $Country = substr ($Group, 7);
		else $Country = 0;
		$Result = ibase_query ($this->Link, 'SELECT FIRST '.$Amount.' "city", "country", "latitude", "longitude", SUM("amount") AS "amount" FROM "'.$this->Prefix.'geoip"'.$Query.' '.($Query?'AND':'WHERE').' "city" != \'\''.($Country?' AND "country" = \''.$this->escape ($Country).'\'':'').' GROUP BY "city", "country", "latitude", "longitude" ORDER BY "amount" DESC');
		while ($Row = ibase_fetch_row ($Result)) $Data['data'][$Row[0].($Country?'':'-'.$Row[1])] = array ($Row[4], $Row[2], $Row[1]);
		$GroupBy = '"city", "country"';
		$Query.= ' '.($Query?'AND':'WHERE').' "city" != \'\''.($Country?' AND "country" = \''.$this->escape ($Country).'\'':'');
		}
	else if (substr ($Group, 0, 7) == 'regions') {
		$Result = ibase_query ($this->Link, 'SELECT FIRST '.$Amount.' \''.substr ($Group, 8).'\' || \'-\' || "region", SUM("amount") AS "amount" FROM "'.$this->Prefix.'geoip"'.$Query.' '.($Query?'AND':'WHERE').' "country" = \''.$this->escape (substr ($Group, 8)).'\' GROUP BY "region" ORDER BY "amount" DESC');
		while ($Row = ibase_fetch_row ($Result)) $Data['data'][$Row[0]] = $Row[1];
		$Query.= ' '.($Query?'AND':'WHERE').' "country" = \''.$this->escape (substr ($Group, 8)).'\'';
		$GroupBy = '"region"';
		}
	else if ($Group == 'countries') {
		$Result = ibase_query ($this->Link, 'SELECT FIRST '.$Amount.' "country", SUM("amount") AS "amount" FROM "'.$this->Prefix.'geoip"'.$Query.' GROUP BY "country" ORDER BY "amount" DESC');
		while ($Row = ibase_fetch_row ($Result)) $Data['data'][$Row[0]] = $Row[1];
		$GroupBy = '"country"';
		}
	else if ($Group == 'continents') {
		$Result = ibase_query ($this->Link, 'SELECT FIRST '.$Amount.' "continent", SUM("amount") AS "amount" FROM "'.$this->Prefix.'geoip"'.$Query.' GROUP BY "continent" ORDER BY "amount" DESC');
		while ($Row = ibase_fetch_row ($Result)) $Data['data'][$Row[0]] = $Row[1];
		$GroupBy = '"continent"';
		}
	else if ($Group == 'world') {
		$Result = ibase_query ($this->Link, 'SELECT "country", "continent", SUM("amount") AS "amount" FROM "'.$this->Prefix.'geoip"'.$Query.' GROUP BY "country", "continent" ORDER BY "amount" DESC');
		while ($Row = ibase_fetch_row ($Result)) $Data['data'][$Row[0]] = array ($Row[1], $Row[2]);
		$GroupBy = '"country", "continent"';
		}
	else {
		$Result = ibase_query ($this->Link, 'SELECT FIRST '.$Amount.' "name", SUM("amount") AS "amount" FROM "'.$this->Prefix.$Table.'"'.$Query.' GROUP BY "name" ORDER BY "amount" DESC');
		while ($Row = ibase_fetch_row ($Result)) $Data['data'][$Row[0]] = $Row[1];
		}
	ibase_free_result ($Result);
	$Data['amount'] = 0;
	$Result = ibase_query ($this->Link, 'SELECT COUNT("amount") FROM "'.$this->Prefix.$Table.'"'.$Query.' GROUP BY '.$GroupBy);
	while ($Row = ibase_fetch_row ($Result)) ++$Data['amount'];
	ibase_free_result ($Result);
	$Data['sum'] = $this->query ('SELECT SUM("amount") FROM "'.$this->Prefix.$Table.'"'.$Query, 1);
	return ($Data);
	}
function data_countries () {
	$Result = ibase_query ($this->Link, 'SELECT "country" FROM "'.$this->Prefix.'geoip" GROUP BY "country"');
	$Array = array ();
	while ($Row = ibase_fetch_row ($Result)) $Array[] = $Row[0];
	ibase_free_result ($Result);
	return ($Array);
	}
function visit_details ($ID, $Page) {
	if (!$Data = ibase_fetch_assoc (ibase_query ($this->Link, 'SELECT * FROM "'.$this->Prefix.'visitors" WHERE "id" = '.(int) $ID))) return (0);
	global $Detailed;
	if ($Page < 1 || $Page > ceil ($Data['visitsamount'] / $Detailed['detailsamount'])) $Page = 1;
	$Result = ibase_query ($this->Link, 'SELECT FIRST '.$Detailed['detailsamount'].' SKIP '.($Detailed['detailsamount'] * ($Page - 1)).' * FROM "'.$this->Prefix.'details" WHERE "id" = '.(int) $ID.' ORDER BY "time" DESC');
	$i = 0;
	while ($Row = ibase_fetch_assoc ($Result)) {
		$Sites[$i] = $Row;
		$Sites[$i]['title'] = $this->query ('SELECT FIRST 1 "name" FROM "'.$this->Prefix.'sites" WHERE "address" = \''.$this->escape ($Sites[$i]['address']).'\' ORDER BY "time" DESC', 1);
		++$i;
		}
	ibase_free_result ($Result);
	return (array ('data' => $Data, 'sites' => $Sites, 'page' => $Page));
	}
function visits ($Robots, $Page) {
	global $Detailed;
	$Amount = $this->query ('SELECT COUNT(*) FROM "'.$this->Prefix.'visitors"'.($Robots?'':' WHERE "robot" = 0'), 1);
	$PagesAmount = ceil ($Amount / $Detailed['amount']);
	if ($PagesAmount > $Detailed['maxpages'] && ESTATS_USERLEVEL < 2) {
		$Amount = ($Detailed['amount'] * $Detailed['maxpages']);
		$PagesAmount = $Detailed['maxpages'];
		}
	if ($Page < 1 || $Page > $PagesAmount) $Page = 1;
	$Data = array ('data' => array (), 'page' => $Page, 'amount' => $Amount);
	$Result = ibase_query ($this->Link, 'SELECT FIRST '.$Detailed['amount'].' SKIP '.($Detailed['amount'] * ($Page - 1)).' * FROM "'.$this->Prefix.'visitors"'.($Robots?'':' WHERE "robot" = 0').' ORDER BY "lastvisit" DESC');
	$i = 0;
	while ($Row = ibase_fetch_assoc ($Result)) {
		$Data['data'][$i] = $Row;
		$Data['data'][$i]['details'] = (boolean) ibase_query ($this->Link, 'SELECT FIRST 1 "id" FROM "'.$this->Prefix.'details" WHERE "id" = '.$Data['data'][$i]['id']);
		++$i;
		}
	ibase_free_result ($Result);
	return ($Data);
	}
function visits_previous ($ID, $Level = 0) {
	static $Array;
	if (!$Array) $Array = array ();
	if ($Level == 10) return (0);
	$Data = ibase_fetch_assoc (ibase_query ($this->Link, 'SELECT * FROM "'.$this->Prefix.'visitors" WHERE "id" = '.(int) $ID));
	if (!isset ($Data['id'])) return (0);
	$Data['details'] = (boolean) ibase_query ($this->Link, 'SELECT FIRST 1 "id" FROM "'.$this->Prefix.'details" WHERE "id" = '.$Data['id']);
	$Array[] = $Data;
	if ($Data['previous']) $this->visits_previous ($Data['previous'], ++$Level);
	return ($Array);
	}
function visits_next ($ID, $Level = 0) {
	static $Array;
	if (!$Array) $Array = array ();
	if ($Level == 10) return (0);
	$Data = ibase_fetch_assoc (ibase_query ($this->Link, 'SELECT * FROM "'.$this->Prefix.'visitors" WHERE "previous" = '.(int) $ID));
	if (!isset ($Data['id'])) return (0);
	$Data['details'] = (boolean) ibase_query ($this->Link, 'SELECT FIRST 1 "id" FROM "'.$this->Prefix.'details" WHERE "id" = '.$Data['id']);
	$Array[] = $Data;
	$this->visits_next ($Data['id'], ++$Level);
	if ($Array) return (array_reverse ($Array));
	return (0);
	}
function visits_online () {
	return ($this->query ('SELECT COUNT("id") FROM "'.$this->Prefix.'visitors" WHERE "lastvisit" > \''.date ('Y-m-d H:i:s', (time () - 300)).'\'', 1));
	}
function visits_most ($Type, $From = 0, $To = 0, $Unit = 'day') {
	$Units = array (
	'hour' => 'EXTRACT(YEAR FROM "time") AS "year", EXTRACT(MONTH FROM "time") AS "month", EXTRACT(DAY FROM "time") AS "day", EXTRACT(HOUR FROM "time") AS "hour"',
	'day' => 'EXTRACT(YEAR FROM "time") AS "year", EXTRACT(MONTH FROM "time") AS "month", EXTRACT(DAY FROM "time") AS "day"',
	'month' => 'EXTRACT(YEAR FROM "time") AS "year", EXTRACT(MONTH FROM "time") AS "month"',
	'year' => 'EXTRACT(YEAR FROM "time") AS "year"'
	);
	$Fields = array (
	'hour' => '"time", "year", "month", "day", "hour"',
	'day' => '"time", "year", "month", "day"',
	'month' => '"time", "year", "month"',
	'year' => '"year"'
	);
	$Data = ibase_fetch_assoc (ibase_query ($this->Link, 'SELECT FIRST 1 '.$Units[$Unit].', "time", SUM("unique") AS "unique", SUM("views") AS "views", SUM("returns") AS "returns" FROM "'.$this->Prefix.'time"'.$this->time_clause ($From, $To).' GROUP BY '.$Fields[$Unit].', "time", "unique", "views", "returns" ORDER BY "'.$Type.'" DESC'));
	if (!$Data) return (0);
	switch ($Type) {
		case 'views':
		$Amount = ($Data['unique'] + $Data['views'] + $Data['returns']);
		break;
		case 'unique':
		$Amount = ($Data['unique'] + $Data['returns']);
		break;
		case 'returns':
		$Amount = (int) $Data['returns'];
		}
	return (array (
	'time' => strtotime ($Data['time']),
	'amount' => $Amount
	));
	}
function visits_excluded ($From = 0, $To = 0) {
	return ((int) $this->query ('SELECT SUM("unique") FROM "'.$this->Prefix.'ignored"'.($From?' WHERE "lastvisit" >= \''.date ('Y-m-d H:i:s', $From).'\''.($To?' AND "lastvisit" < \''.date ('Y-m-d H:i:s', $To).'\'':''):''), 1));
	}
function visits_ignored ($Amount, $From) {
	$Data = array ();
	$Result = ibase_query ($this->Link, 'SELECT'.($Amount?' FIRST '.$Amount.' SKIP '.$From:'').' * FROM "'.$this->Prefix.'ignored" ORDER BY "lastview" DESC');
	while ($Row = ibase_fetch_assoc ($Result)) $Data[] = $Row;
	return ($Data);
	}
function time ($Unit, $From = 0, $To = 0, $Types = 0) {
	$AvailableTypes = array ('views', 'unique', 'returns');
	if (!$Types) $Types = $AvailableTypes;
	$Select = array ();
	$Bits = 0;
	$Bit = 1;
	for ($i = 0; $i < 3; ++$i) {
		$Add = in_array ($AvailableTypes[$i], $Types);
		if ($Add) $Bits += $Bit;
		$Bit *= 2;
		if ($Select || $Add) $Select[] = 'SUM("'.$AvailableTypes[$i].'") AS "'.$AvailableTypes[$i].'"';
		}
	$Units = array (
	'hour' => 'EXTRACT(YEAR FROM "time") AS "year", EXTRACT(MONTH FROM "time") AS "month", EXTRACT(DAY FROM "time") AS "day", EXTRACT(HOUR FROM "time") AS "hour"',
	'dayhour' => 'EXTRACT(HOUR FROM "time") AS "hour"',
	'day' => 'EXTRACT(YEAR FROM "time") AS "year", EXTRACT(MONTH FROM "time") AS "month", EXTRACT(DAY FROM "time") AS "day"',
	'weekday' => 'EXTRACT(WEEKDAY FROM "time") AS "weekday"',
	'month' => 'EXTRACT(YEAR FROM "time") AS "year", EXTRACT(MONTH FROM "time") AS "month"',
	'year' => 'EXTRACT(YEAR FROM "time") AS "year"'
	);
	$Fields = array (
	'hour' => '"time", "year", "month", "day", "hour"',
	'dayhour' => '"hour"',
	'day' => '"time", "year", "month", "day"',
	'weekday' => '"weekday"',
	'month' => '"time", "year", "month"',
	'year' => '"year"'
	);
	$Result = ibase_query ($this->Link, 'SELECT '.$Units[$Unit].', '.implode (', ', $Select).' FROM "'.$this->Prefix.'time"'.$this->time_clause ($From, $To).' GROUP BY '.$Fields[$Unit].' ORDER BY '.$Fields[$Unit].' ASC');
	$Data = array ();
	while ($Row = ibase_fetch_assoc ($Result)) {
		switch ($Unit) {
			case 'hour':
			$Row['unit'] = $Row['year'].'.'.(($Row['month'] < 10)?'0':'').$Row['month'].'.'.(($Row['day'] < 10)?'0':'').$Row['day'].' '.(($Row['hour'] < 10)?'0':'').$Row['hour'];
			break;
			case 'dayhour':
			$Row['unit'] = $Row['hour'];
			if ($Row['unit'] < 10) $Row['unit'] = '0'.$Row['unit'];
			break;
			case 'day':
			$Row['unit'] = $Row['year'].'.'.(($Row['month'] < 10)?'0':'').$Row['month'].'.'.(($Row['day'] < 10)?'0':'').$Row['day'];
			break;
			case 'weekday':
			$Row['unit'] = $Row['weekday'];
			break;
			case 'month':
			$Row['unit'] = $Row['year'].'.'.(($Row['month'] < 10)?'0':'').$Row['month'];
			break;
			case 'year':
			$Row['unit'] = $Row['year'];
			break;
			}
		if ($Bits & 1) $Data[$Row['unit']]['views'] = ($Row['views'] + $Row['unique'] + $Row['returns']);
		if ($Bits & 2) $Data[$Row['unit']]['unique'] = ($Row['unique'] + $Row['returns']);
		if ($Bits & 4) $Data[$Row['unit']]['returns'] = (int) $Row['returns'];
		}
	ibase_free_result ($Result);
	return ($Data);
	}
function logs ($Amount, $From, $Clause = '') {
	$Data = array ();
	$Result = ibase_query ($this->Link, 'SELECT'.($Amount?' FIRST '.$Amount.' SKIP '.$From:'').' * FROM "'.$this->Prefix.'logs"'.$Clause.' ORDER BY "time"');
	while ($Row = ibase_fetch_assoc ($Result)) $Data[] = $Row;
	ibase_free_result ($Result);
	return ($Data);
	}
function logs_search_clause ($Array) {
	return ((($Array['from'] && $Array['to'])?'WHERE "time" >= \''.date ('Y-m-d H:i:s', strtotime ($Array['from'])).'\' AND "time" <= \''.date ('Y-m-d H:i:s', strtotime ($Array['to'])).'\'':'').(isset ($Array['filter'])?' AND ("log" = '.implode (' OR "log" = ', $Array['filter']).')':'').($Array['search']?' AND ("'.implode ('" LIKE \'%'.sqlite_escape_string ($Array['search']).'%\' OR "', array ('log', 'time', 'user', 'ip', 'db', 'table', 'additional')).'" LIKE \'%'.sqlite_escape_string ($Array['search']).'%\')':''));
	}
function backup_restore ($BackupID) {
	if (!is_file ($GLOBALS['DataDir'].'backups/'.$BackupID.'.bak')) return (0);
	$Status = 1;
	if (strstr ($BackupID, 'sql')) ibase_query ($this->Link, file_get_contents ($GLOBALS['DataDir'].'backups/'.$BackupID.'.bak')) or $Status = 0;
	else {
		ibase_query ($this->Link, 'SET TRANSACTION');
		$File = fopen ($GLOBALS['DataDir'].'backups/'.$BackupID.'.bak', 'r');
		$Buffer = '';
		$Replace = $Recreate = $Create = $Table = $Fields = $Line = 0;
		while (!feof ($File)) {
			$Byte = fread ($File, 1);
			if ($Byte == "\n") {
				if (!$Buffer || $Line < 10) {
					if ($Line == 3) {
						if (strstr ($Buffer, 'replace data')) $Replace = 1;
						if (strstr ($Buffer, 'create tables')) {
							if (strstr ($Buffer, 'recreate tables')) $Recreate = 1;
							else $Create = 1;
							if (!include ('./lib/db/Firebird/schema.php')) {
								e_error_message ('lib/db/Firebird/schema.php', __FILE__, __LINE__);
								return (0);
								}
							}
						}
					++$Line;
					$Buffer = '';
					continue;
					}
				if (substr ($Buffer, 0, 8) == '/*Table:') {
					$Table = substr ($Buffer, 9, -2);
					$Fields = 0;
					if ($Replace) ibase_query ($this->Link, 'DELETE FROM "'.$this->Prefix.$Table.'"');
					else if ($Recreate || $Create) {
						if ($this->query ('SELECT "RDB$RELATION_NAME" FROM "RDB$RELATIONS" WHERE "RDB$RELATION_NAME" = \''.$this->Prefix.$Table.'\'', 1)) {
/// FIXME
							//if ($Create) return (0);
							//else
							ibase_query ($this->Link, 'DROP TABLE "'.$this->Prefix.$Table.'"');
							}
						if (!ibase_query ($this->Link, 'CREATE TABLE "'.$this->Prefix.$Table.'" '.$Schema[$Table])) $Status = 0;
						}
					}
				else {
					$Array = explode (chr (30), strtr ($Buffer, array (
			'\r' => "\r",
			'\n' =>"\n"
			)));
					if (!$Fields) $Fields = count ($Array);
					for ($i = 0; $i < $Fields; ++$i) {
						if (!$Array[$i] && $Array[$i] !== 0 && $Table == 'visitors') $Array[$i] = 'NULL';
						else $Array[$i] = '\''.$this->escape ($Array[$i]).'\'';
						}
					if (!ibase_query ($this->Link, 'INSERT INTO "'.$this->Prefix.$Table.'" VALUES('.implode (', ', $Array).')')) $Status = 0;
					}
				$Buffer = '';
				}
			else $Buffer.= $Byte;
			}
		ibase_query ($this->Link, 'COMMIT');
		}
	return ($Status);
	}
function clean () {
	if (!isset ($GLOBALS['Detailed']['keepalldata']) || $GLOBALS['Detailed']['keepalldata']) return;
	global $Detailed;
	$UniqueIDs = array ();
	$MaxTime = (int) $this->query ('SELECT FIRST 1 SKIP '.($Detailed['amount'] * $Detailed['maxpages']).' MAX("time") AS "maxtime" FROM "'.$this->Prefix.'details" GROUP BY "id" ORDER BY "maxtime" DESC', 1);
	$Result = ibase_query ($this->Link, 'SELECT "id", MAX("time"), MIN("time") FROM "'.$this->Prefix.'details" GROUP BY "id"');
	while ($Row = ibase_fetch_row ($Result)) {
		if ($Row[1] <= $MaxTime && (time () - strtotime ($Row[2])) > $Detailed['period'] && (time () - strtotime ($Row[2])) > $GLOBALS['VisitTime']) $UniqueIDs[] = $Row[0];
		}
	ibase_free_result ($Result);
	if ($UniqueIDs) {
		if (!$Detailed['keepalldata']) ibase_query ($this->Link, 'DELETE FROM "'.$this->Prefix.'visitors" WHERE "id" = '.implode (' OR "id" = ', $UniqueIDs));
		ibase_query ($this->Link, 'DELETE FROM "'.$this->Prefix.'details" WHERE "id" = '.implode (' OR "id" = ', $UniqueIDs));
		}
	}
function verify ($Schema) {
/// FIXME
	return (1);
//	foreach ($Schema as $Key => $Value) {
//		if (!$this->query ('SELECT "RDB$RELATION_NAME" FROM "RDB$RELATIONS" WHERE "RDB$RELATION_NAME" = \''.$this->Prefix.$Key.'\'', 1)) return (0);
//		}
//	return (e_verify_configuration ());
	}
function verify_configuration_option ($Name, $Mode) {
	return ((boolean) $this->query ('SELECT "name" FROM "'.$this->Prefix.'configuration" WHERE "name" = \''.$Name.'\' AND "mode" = '.$Mode, 1));
	}
function table_reset ($Tables) {
	for ($i = 0, $c = count ($Tables); $i < $c; ++$i) {
		if (!in_array ($Tables[$i], array ('configuration', 'logs'))) ibase_query ($this->Link, 'DELETE FROM "'.$this->Prefix.$Tables[$i].'"');
		}
	}
function table_row_delete ($Table, $Row) {
	ibase_query ($this->Link, 'DELETE FROM "'.$this->Prefix.$Table.'" WHERE "name" = "'.$Row.'"');
	return (ibase_affected_rows ($this->Link));
	}
function table_rows_amount ($Table, $Clause = '') {
	return ($this->query ('SELECT COUNT(*) FROM "'.$this->Prefix.$Table.'"'.($Clause?' '.$Clause:''), 1));
	}
function db_size () {
	if (!is_file ($GLOBALS['DBAddress'])) return ('?');
	return (filesize ($GLOBALS['DBAddress']));
	}
}
?>