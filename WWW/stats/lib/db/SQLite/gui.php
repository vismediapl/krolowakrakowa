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
		$Result = sqlite_query ($this->Link, 'SELECT "name" || \' \' || "version" AS "var", SUM("amount") AS "amount" FROM "'.$Table.'"'.$Query.' GROUP BY "var" ORDER BY "amount" DESC LIMIT '.$Amount);
		while ($Row = sqlite_fetch_array ($Result, SQLITE_NUM)) $Data['data'][$Row[0]] = $Row[1];
		$GroupBy = '"name", "version"';
		}
	else if ($Group == 'sites') {
		$Result = sqlite_query ($this->Link, 'SELECT "name", SUM("amount") AS "amount", "address" FROM "sites"'.$Query.' GROUP BY "address" ORDER BY "amount" DESC LIMIT '.$Amount);
		while ($Row = sqlite_fetch_array ($Result, SQLITE_NUM)) $Data['data'][$Row[2]] = array ($Row[1], ($Row[0]?$Row[0]:$Row[2]));
		$GroupBy = '"name", "address"';
		}
	else if (substr ($Group, 0, 6) == 'cities') {
		$Result = sqlite_query ($this->Link, 'SELECT "city"'.((strlen ($Group) < 7)?' || \'-\' || "country"':'').' AS "var", "latitude", "longitude", SUM("amount") AS "amount" FROM "geoip"'.$Query.' '.($Query?'AND':'WHERE').' "city" != \'\''.((isset ($Group[6]) && $Group[6] == '_')?' AND "country" = \''.sqlite_escape_string (substr ($Group, 7)).'\'':'').' GROUP BY "var" ORDER BY "amount" DESC LIMIT '.$Amount, SQLITE_ASSOC);
		while ($Row = sqlite_fetch_array ($Result, SQLITE_NUM)) $Data['data'][$Row[0]] = array ($Row[3], $Row[1], $Row[2]);
		$GroupBy = '"city", "country"';
		$Query.= ' '.($Query?'AND':'WHERE').' "city" != \'\''.((isset ($Group[6]) && $Group[6] == '_')?' AND "country" = \''.sqlite_escape_string (substr ($Group, 7)).'\'':'');
		}
	else if (substr ($Group, 0, 7) == 'regions') {
		$Result = sqlite_query ($this->Link, 'SELECT \''.substr ($Group, 8).'\' || \'-\' || "region", SUM("amount") AS "amount" FROM "geoip"'.$Query.' '.($Query?'AND':'WHERE').' "country" = \''.sqlite_escape_string (substr ($Group, 8)).'\' GROUP BY "region" ORDER BY "amount" DESC LIMIT '.$Amount, SQLITE_ASSOC);
		while ($Row = sqlite_fetch_array ($Result, SQLITE_NUM)) $Data['data'][$Row[0]] = $Row[1];
		$GroupBy = '"region"';
		$Query.= ' '.($Query?'AND':'WHERE').' "country" = \''.sqlite_escape_string (substr ($Group, 8)).'\'';
		}
	else if ($Group == 'countries') {
		$Result = sqlite_query ($this->Link, 'SELECT "country", SUM("amount") AS "amount" FROM "geoip"'.$Query.' GROUP BY "country" ORDER BY "amount" DESC LIMIT '.$Amount, SQLITE_ASSOC);
		while ($Row = sqlite_fetch_array ($Result, SQLITE_NUM)) $Data['data'][$Row[0]] = $Row[1];
		$GroupBy = '"country"';
		}
	else if ($Group == 'continents') {
		$Result = sqlite_query ($this->Link, 'SELECT "continent", SUM("amount") AS "amount" FROM "geoip"'.$Query.' GROUP BY "continent" ORDER BY "amount" DESC LIMIT '.$Amount, SQLITE_ASSOC);
		while ($Row = sqlite_fetch_array ($Result, SQLITE_NUM)) $Data['data'][$Row[0]] = $Row[1];
		$GroupBy = '"continent"';
		}
	else if ($Group == 'world') {
		$Result = sqlite_query ($this->Link, 'SELECT "country", "continent", SUM("amount") AS "amount" FROM "geoip"'.$Query.' GROUP BY "country", "continent" ORDER BY "amount" DESC', SQLITE_ASSOC);
		while ($Row = sqlite_fetch_array ($Result, SQLITE_NUM)) $Data['data'][$Row[0]] = array ($Row[1], $Row[2]);
		$GroupBy = '"country", "continent"';
		}
	else {
		$Result = sqlite_query ($this->Link, 'SELECT "name", SUM("amount") AS "amount" FROM "'.$Group.'"'.$Query.' GROUP BY "name" ORDER BY "amount" DESC LIMIT '.$Amount);
		while ($Row = sqlite_fetch_array ($Result, SQLITE_NUM)) $Data['data'][$Row[0]] = $Row[1];
		}
	$Data['amount'] = sqlite_num_rows (sqlite_query ($this->Link, 'SELECT COUNT("amount") FROM "'.$Table.'"'.$Query.' GROUP BY '.$GroupBy));
	$Data['sum'] = sqlite_single_query ($this->Link, 'SELECT SUM("amount") FROM "'.$Table.'"'.$Query, 1);
	return ($Data);
	}
function data_countries () {
	$Result = sqlite_array_query ($this->Link, 'SELECT "country" FROM "geoip" GROUP BY "country"');
	$Array = array ();
	for ($i = 0, $c = count ($Result); $i < $c; ++$i) $Array[] = $Result[$i][0];
	return ($Array);
	}
function visit_details ($ID, $Page) {
	if (!$Data = sqlite_array_query ($this->Link, 'SELECT * FROM "visitors" WHERE "id" = '.(int) $ID.' GROUP BY "id"', SQLITE_ASSOC)) return (0);
	global $Detailed;
	if ($Page < 1 || $Page > ceil ($Data[0]['visitsamount'] / $Detailed['detailsamount'])) $Page = 1;
	$Sites = sqlite_array_query ($this->Link, 'SELECT * FROM "details" WHERE "id" = '.(int) $ID.' ORDER BY "time" DESC LIMIT '.($Detailed['detailsamount'] * ($Page - 1)).', '.$Detailed['detailsamount'], SQLITE_ASSOC);
	for ($i = 0, $c = count ($Sites); $i < $c; ++$i) $Sites[$i]['title'] = sqlite_single_query ($this->Link, 'SELECT "name" FROM "sites" WHERE "address" = \''.sqlite_escape_string ($Sites[$i]['address']).'\' ORDER BY "time" DESC LIMIT 1', 1);
	return (array ('data' => $Data[0], 'sites' => $Sites, 'page' => $Page));
	}
function visits ($Robots, $Page) {
	global $Detailed;
	$Amount = sqlite_single_query ($this->Link, 'SELECT COUNT(*) FROM "visitors"'.($Robots?'':' WHERE "robot" = 0'), 1);
	$PagesAmount = ceil ($Amount / $Detailed['amount']);
	if ($PagesAmount > $Detailed['maxpages'] && ESTATS_USERLEVEL < 2) {
		$Amount = ($Detailed['amount'] * $Detailed['maxpages']);
		$PagesAmount = $Detailed['maxpages'];
		}
	if ($Page < 1 || $Page > $PagesAmount) $Page = 1;
	$Data = array ('data' => array (), 'page' => $Page, 'amount' => $Amount);
	$Data['data'] = sqlite_array_query ($this->Link, 'SELECT * FROM "visitors"'.($Robots?'':' WHERE "robot" = 0').' ORDER BY "lastvisit" DESC LIMIT '.($Detailed['amount'] * ($Page - 1)).', '.$Detailed['amount'], SQLITE_ASSOC);
	for ($i = 0, $c = count ($Data['data']); $i < $c; ++$i) $Data['data'][$i]['details'] = (boolean) sqlite_query ($this->Link, 'SELECT "id" FROM "details" WHERE "id" = '.$Data['data'][$i]['id'].' LIMIT 1');
	return ($Data);
	}
function visits_previous ($ID, $Level = 0) {
	static $Array;
	if (!$Array) $Array = array ();
	if ($Level == 10) return (0);
	$Data = sqlite_array_query ($this->Link, 'SELECT * FROM "visitors" WHERE "id" = '.(int) $ID, SQLITE_ASSOC);
	if (!isset ($Data[0])) return (0);
	$Data[0]['details'] = (boolean) sqlite_query ($this->Link, 'SELECT "id" FROM "details" WHERE "id" = '.$Data[0]['id'].' LIMIT 1');
	$Array[] = $Data[0];
	if ($Data[0]['previous']) $this->visits_previous ($Data[0]['previous'], ++$Level);
	return ($Array);
	}
function visits_next ($ID, $Level = 0) {
	static $Array;
	if (!$Array) $Array = array ();
	if ($Level == 10) return (0);
	$Data = sqlite_array_query ($this->Link, 'SELECT * FROM "visitors" WHERE "previous" = '.(int) $ID, SQLITE_ASSOC);
	if (!isset ($Data[0])) return (0);
	$Data[0]['details'] = (boolean) sqlite_query ($this->Link, 'SELECT "id" FROM "details" WHERE "id" = '.$Data[0]['id'].' LIMIT 1');
	$Array[] = $Data[0];
	$this->visits_next ($Data[0]['id'], ++$Level);
	if ($Array) return (array_reverse ($Array));
	return (0);
	}
function visits_online () {
	return (sqlite_single_query ($this->Link, 'SELECT COUNT(*) FROM "visitors" WHERE ("lastvisit" > \''.date ('Y-m-d H:i:s', (time () - 300)).'\')', 1));
	}
function visits_most ($Type, $From = 0, $To = 0, $Unit = 'day') {
	$Units = array (
	'hour' => 'Y.m.d H',
	'day' => 'Y.m.d',
	'month' => 'Y.m',
	'year' => 'Y'
	);
	$Data = sqlite_array_query ($this->Link, 'SELECT PHP(\'e_date_format\', \''.$Units[$Unit].'\', "time") AS "unit", "time", SUM("unique") AS "unique", SUM("views") AS "views", SUM("returns") AS "returns" FROM "time"'.$this->time_clause ($From, $To).' GROUP BY "unit" ORDER BY "'.$Type.'" DESC LIMIT 1', SQLITE_ASSOC);
	if (!$Data) return (0);
	switch ($Type) {
		case 'views':
		$Amount = ($Data[0]['unique'] + $Data[0]['views'] + $Data[0]['returns']);
		break;
		case 'unique':
		$Amount = ($Data[0]['unique'] + $Data[0]['returns']);
		break;
		case 'returns':
		$Amount = (int) $Data[0]['returns'];
		}
	return (array (
	'time' => strtotime ($Data[0]['"time"']),
	'amount' => $Amount
	));
	}
function visits_excluded ($From = 0, $To = 0) {
	return ((int) sqlite_single_query ($this->Link, 'SELECT SUM("unique") FROM "ignored"'.($From?' WHERE "lastvisit" >= '.$From.($To?' AND "lastvisit" < '.$To:''):''), 1));
	}
function visits_ignored ($Amount, $From) {
	$Data = array ();
	$Result = sqlite_query ($this->Link, 'SELECT * FROM "ignored" ORDER BY "lastview" DESC'.($Amount?' LIMIT '.$From.', '.$Amount:''));
	while ($Row = sqlite_fetch_array ($Result, SQLITE_ASSOC)) $Data[] = $Row;
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
	'hour' => 'Y.m.d H',
	'dayhour' => 'H',
	'day' => 'Y.m.d',
	'weekday' => 'w',
	'month' => 'Y.m',
	'year' => 'Y'
	);
	$Result = sqlite_array_query ($this->Link, 'SELECT PHP(\'e_date_format\', \''.$Units[$Unit].'\', "time") AS "unit", '.implode (', ', $Select).' FROM "time"'.$this->time_clause ($From, $To).' GROUP BY "unit" ORDER BY "unit" ASC', SQLITE_ASSOC);
	$Data = array ();
	for ($i = 0, $c = count ($Result); $i < $c; ++$i) {
		if ($Bits & 1) $Data[$Result[$i]['unit']]['views'] = ($Result[$i]['views'] + $Result[$i]['unique'] + $Result[$i]['returns']);
		if ($Bits & 2) $Data[$Result[$i]['unit']]['unique'] = ($Result[$i]['unique'] + $Result[$i]['returns']);
		if ($Bits & 4) $Data[$Result[$i]['unit']]['returns'] = (int) $Result[$i]['returns'];
		}
	unset ($Result);
	return ($Data);
	}
function logs ($Amount, $From, $Clause = '') {
	return (sqlite_array_query ($this->Link, 'SELECT * FROM "logs"'.$Clause.' ORDER BY "time"'.($Amount?' LIMIT '.$From.', '.$Amount:''), SQLITE_ASSOC));
	}
function logs_search_clause ($Array) {
	return ((($Array['from'] && $Array['to'])?'WHERE "time" >= \''.date ('Y-m-d H:i:s', strtotime ($Array['from'])).'\' AND "time" <= \''.date ('Y-m-d H:i:s', strtotime ($Array['to'])).'\'':'').(isset ($Array['filter'])?' AND ("log" = '.implode (' OR "log" = ', $Array['filter']).')':'').($Array['search']?' AND ("'.implode ('" LIKE \'%'.sqlite_escape_string ($Array['search']).'%\' OR "', array ('log', 'time', 'user', 'ip', 'db', 'table', 'additional')).'" LIKE \'%'.sqlite_escape_string ($Array['search']).'%\')':''));
	}
function backup_restore ($BackupID) {
	if (!is_file ($GLOBALS['DataDir'].'backups/'.$BackupID.'.bak')) return (0);
	$Status = 1;
	if (strstr ($BackupID, 'sql')) sqlite_exec ($this->Link, file_get_contents ($GLOBALS['DataDir'].'backups/'.$BackupID.'.bak')) or $Status = 0;
	else {
		sqlite_query ($this->Link, 'BEGIN');
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
							if (!include ('./lib/db/SQLite/schema.php')) {
							e_error_message ('lib/db/SQLite/schema.php', __FILE__, __LINE__);
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
					if ($Replace) sqlite_query ($this->Link, 'DELETE FROM "'.$Table.'"');
					else if ($Recreate || $Create) {
						if (sqlite_single_query ($this->Link, 'SELECT "name" FROM "sqlite_master" WHERE "name" = \''.$Table.'\' AND "type" = \'table\'', 1)) {
							if ($Create) return (0);
							else sqlite_query ($this->Link, 'DROP TABLE "'.$Table.'"');
							}
						sqlite_query ($this->Link, 'CREATE TABLE "'.$Table.'" '.$Schema[$Table]);
						}
					}
				else {
					$Array = explode (chr (30), strtr ($Buffer, array (
		'\r' => "\r",
		'\n' =>"\n"
		)));
					if (!$Fields) $Fields = count ($Array);
					for ($i = 0; $i < $Fields; ++$i) $Array[$i] = sqlite_escape_string ($Array[$i]);
					sqlite_query ($this->Link, 'INSERT INTO "'.$Table.'" VALUES(\''.implode ('\', \'', $Array).'\')');
					}
				$Buffer = '';
				}
			else $Buffer.= $Byte;
			}
		$Status = (boolean) sqlite_query ($this->Link, 'COMMIT');
		}
	return ($Status);
	}
function clean () {
	if (!isset ($GLOBALS['Detailed']['keepalldata']) || $GLOBALS['Detailed']['keepalldata']) return;
	global $Detailed;
	$UniqueIDs = array ();
	$MaxTime = (int) sqlite_single_query ($this->Link, 'SELECT MAX("time") AS "maxtime" FROM "details" GROUP BY "id" ORDER BY "maxtime" DESC LIMIT '.($Detailed['amount'] * $Detailed['maxpages']).', 1', 1);
	$Array = sqlite_array_query ($this->Link, 'SELECT "id", MAX("time"), MIN("time") FROM "details" GROUP BY "id"', SQLITE_NUM);
	for ($i = 0, $c = count ($Array); $i < $c; ++$i) {
		if ($Array[$i][1] <= $MaxTime && (time () - strtotime ($Array[$i][2])) > $Detailed['period'] && (time () - strtotime ($Array[$i][2])) > $GLOBALS['VisitTime']) $UniqueIDs[] = $Array[$i][0];
		}
	if ($UniqueIDs) {
		if (!$Detailed['keepalldata']) sqlite_query ($this->Link, 'DELETE FROM "visitors" WHERE "id" = '.implode (' OR "id" = ', $UniqueIDs));
		sqlite_query ($this->Link, 'DELETE FROM "details" WHERE "id" = '.implode (' OR "id" = ', $UniqueIDs));
		}
	}
function verify ($Schema) {
	foreach ($Schema as $Key => $Value) {
		if (!sqlite_query ($this->Link, 'SELECT * FROM "sqlite_master" WHERE "name" = \''.$Key.'\' AND "type" = \'table\'')) return (0);
		}
	return (e_verify_configuration ());
	}
function verify_configuration_option ($Name, $Mode) {
	return ((boolean) sqlite_query ($this->Link, 'SELECT * FROM "configuration" WHERE "name" = \''.$Name.'\' AND "mode" = '.$Mode));
	}
function table_reset ($Tables) {
	for ($i = 0, $c = count ($Tables); $i < $c; ++$i) {
		if (!in_array ($Tables[$i], array ('configuration', 'logs'))) sqlite_query ($this->Link, 'DELETE FROM "'.$Tables[$i].'"');
		}
	sqlite_query ($this->Link, 'VACUUM');
	}
function table_row_delete ($Table, $Row) {
	sqlite_query ($this->Link, 'DELETE FROM "'.$Table.'" WHERE "name" = "'.$Row.'"');
	return (sqlite_changes ($this->Link));
	}
function table_rows_amount ($Table, $Clause = '') {
	return (sqlite_single_query ($this->Link, 'SELECT COUNT(*) FROM "'.$Table.'"'.($Clause?' '.$Clause:''), 1));
	}
function db_size () {
	return (filesize ($GLOBALS['DataDir'].'estats_'.$GLOBALS['DBID'].'.sqlite'));
	}
}
?>