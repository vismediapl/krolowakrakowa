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
		$Result = pg_query ($this->Link, 'SELECT "name" || \' \' || "version" AS "var", SUM("amount") AS "amount" FROM "'.$this->Prefix.substr ($Group, 1).'"'.$Query.' GROUP BY "var" ORDER BY "amount" DESC LIMIT '.$Amount);
		for ($i = 0, $c = pg_num_rows ($Result); $i < $c; ++$i) {
			$Row = pg_fetch_row ($Result, $i);
			$Data['data'][$Row[0]] = $Row[1];
			}
		$GroupBy = '"name", "version"';
		}
	else if ($Group == 'sites') {
		$Result = pg_query ($this->Link, 'SELECT "name", SUM("amount") AS "amount", "address" FROM "'.$this->Prefix.'sites"'.$Query.' GROUP BY "name", "address" ORDER BY "amount" DESC LIMIT '.$Amount);
		for ($i = 0, $c = pg_num_rows ($Result); $i < $c; ++$i) {
			$Row = pg_fetch_row ($Result, $i);
			$Data['data'][$Row[2]] = array ($Row[1], ($Row[0]?$Row[0]:$Row[2]));
			}
		$GroupBy = '"name", "address"';
		}
	else if (substr ($Group, 0, 6) == 'cities') {
		$Result = pg_query ($this->Link, 'SELECT "city"'.((strlen ($Group) < 7)?' || \'-\' || "country"':'').' AS "var", "latitude", "longitude", SUM("amount") AS "amount" FROM "'.$this->Prefix.'geoip"'.$Query.' '.($Query?'AND':'WHERE').' "city" != \'\''.((isset ($Group[6]) && $Group[6] == '_')?' AND "country" = \''.pg_escape_string (substr ($Group, 7)).'\'':'').' GROUP BY "var", "latitude", "longitude" ORDER BY "amount" DESC LIMIT '.$Amount);
		for ($i = 0, $c = pg_num_rows ($Result); $i < $c; ++$i) {
			$Row = pg_fetch_row ($Result, $i);
			$Data['data'][$Row[0]] = array ($Row[3], $Row[1], $Row[2]);
			}
		$GroupBy = '"city", "country"';
		$Query.= ' '.($Query?'AND':'WHERE').' "city" != \'\''.((isset ($Group[6]) && $Group[6] == '_')?' AND "country" = \''.pg_escape_string (substr ($Group, 7)).'\'':'');
		}
	else if (substr ($Group, 0, 7) == 'regions') {
		$Result = pg_query ($this->Link, 'SELECT \''.substr ($Group, 8).'\' || \'-\' || "region", SUM("amount") AS "amount" FROM "'.$this->Prefix.'geoip"'.$Query.' '.($Query?'AND':'WHERE').' "country" = \''.pg_escape_string (substr ($Group, 8)).'\' GROUP BY "region" ORDER BY "amount" DESC LIMIT '.$Amount);
		for ($i = 0, $c = pg_num_rows ($Result); $i < $c; ++$i) {
			$Row = pg_fetch_row ($Result, $i);
			$Data['data'][$Row[0]] = $Row[1];
			}
		$GroupBy = '"region"';
		$Query.= ' '.($Query?'AND':'WHERE').' "country" = \''.pg_escape_string (substr ($Group, 8)).'\'';
		}
	else if ($Group == 'countries') {
		$Result = pg_query ($this->Link, 'SELECT "country", SUM("amount") AS "amount" FROM "'.$this->Prefix.'geoip"'.$Query.' GROUP BY "country" ORDER BY "amount" DESC LIMIT '.$Amount);
		for ($i = 0, $c = pg_num_rows ($Result); $i < $c; ++$i) {
			$Row = pg_fetch_row ($Result, $i);
			$Data['data'][$Row[0]] = $Row[1];
			}
		$GroupBy = '"country"';
		}
	else if ($Group == 'continents') {
		$Result = pg_query ($this->Link, 'SELECT "continent", SUM("amount") AS "amount" FROM "'.$this->Prefix.'geoip"'.$Query.' GROUP BY "continent" ORDER BY "amount" DESC LIMIT '.$Amount);
		for ($i = 0, $c = pg_num_rows ($Result); $i < $c; ++$i) {
			$Row = pg_fetch_row ($Result, $i);
			$Data['data'][$Row[0]] = $Row[1];
			}
		$GroupBy = '"continent"';
		}
	else if ($Group == 'world') {
		$Result = pg_query ($this->Link, 'SELECT "country", "continent", SUM("amount") AS "amount" FROM "'.$this->Prefix.'geoip"'.$Query.' GROUP BY "country", "continent" ORDER BY "amount" DESC');
		for ($i = 0, $c = pg_num_rows ($Result); $i < $c; ++$i) {
			$Row = pg_fetch_row ($Result, $i);
			$Data['data'][$Row[0]] = array ($Row[1], $Row[2]);
			}
		$GroupBy = '"country", "continent"';
		}
	else {
		$Result = pg_query ($this->Link, 'SELECT "name", SUM("amount") AS "amount" FROM "'.$this->Prefix.$Table.'"'.$Query.' GROUP BY "name" ORDER BY "amount" DESC LIMIT '.$Amount);
		for ($i = 0, $c = pg_num_rows ($Result); $i < $c; ++$i) {
			$Row = pg_fetch_row ($Result, $i);
			$Data['data'][$Row[0]] = $Row[1];
			}
		}
	pg_free_result ($Result);
	$Result = pg_query ($this->Link, 'SELECT COUNT("amount") FROM "'.$this->Prefix.$Table.'"'.$Query.' GROUP BY '.$GroupBy);
	$Data['amount'] = pg_num_rows ($Result);
	pg_free_result ($Result);
	$Data['sum'] = $this->query ('SELECT SUM("amount") FROM "'.$this->Prefix.$Table.'"'.$Query, 1);
	return ($Data);
	}
function data_countries () {
	$Result = pg_query ($this->Link, 'SELECT "country" FROM "'.$this->Prefix.'geoip" GROUP BY "country"');
	$Array = array ();
	for ($i = 0, $c = pg_num_rows ($Result); $i < $c; ++$i) {
		$Row = pg_fetch_row ($Result, $i);
		$Array[] = $Row[0];
		}
	pg_free_result ($Result);
	return ($Array);
	}
function visit_details ($ID, $Page) {
	if (!$Data = pg_fetch_assoc (pg_query ($this->Link, 'SELECT * FROM "'.$this->Prefix.'visitors" WHERE "id" = '.(int) $ID), 0)) return (0);
	global $Detailed;
	if ($Page < 1 || $Page > ceil ($Data['visitsamount'] / $Detailed['detailsamount'])) $Page = 1;
	$Result = pg_query ($this->Link, 'SELECT * FROM "'.$this->Prefix.'details" WHERE "id" = '.(int) $ID.' ORDER BY "time" DESC LIMIT '.$Detailed['detailsamount'].' OFFSET '.($Detailed['detailsamount'] * ($Page - 1)));
	for ($i = 0, $c = pg_num_rows ($Result); $i < $c; ++$i) {
		$Sites[$i] = pg_fetch_assoc ($Result, $i);
		$Sites[$i]['title'] = $this->query ('SELECT "name" FROM "'.$this->Prefix.'sites" WHERE "address" = \''.pg_escape_string ($Sites[$i]['address']).'\' ORDER BY "time" DESC LIMIT 1', 1);
		}
	pg_free_result ($Result);
	return (array ('data' => $Data, 'sites' => $Sites, 'page' => $Page));
	}
function visits ($Robots, $Page) {
	global $Detailed;
	$Amount = $this->query ('SELECT COUNT(*) FROM "'.$this->Prefix.'visitors"'.($Robots?'':' WHERE "robot" = \'0\''), 1);
	$PagesAmount = ceil ($Amount / $Detailed['amount']);
	if ($PagesAmount > $Detailed['maxpages'] && ESTATS_USERLEVEL < 2) {
		$Amount = ($Detailed['amount'] * $Detailed['maxpages']);
		$PagesAmount = $Detailed['maxpages'];
		}
	if ($Page < 1 || $Page > $PagesAmount) $Page = 1;
	$Data = array ('data' => array (), 'page' => $Page, 'amount' => $Amount);
	$Result = pg_query ($this->Link, 'SELECT * FROM "'.$this->Prefix.'visitors" '.($Robots?'':' WHERE "robot" = \'0\'').' ORDER BY "lastvisit" DESC OFFSET '.($Detailed['amount'] * ($Page - 1)).' LIMIT '.$Detailed['amount']);
	for ($i = 0, $c = pg_num_rows ($Result); $i < $c; ++$i) {
		$Data['data'][$i] = pg_fetch_assoc ($Result, $i);
		$Data['data'][$i]['details'] = (boolean) pg_query ($this->Link, 'SELECT "id" FROM "'.$this->Prefix.'details" WHERE "id" = '.$Data['data'][$i]['id'].' LIMIT 1');
		}
	pg_free_result ($Result);
	return ($Data);
	}
function visits_previous ($ID, $Level = 0) {
	static $Array;
	if (!$Array) $Array = array ();
	if ($Level == 10) return (0);
	if (!pg_num_rows ($Data = pg_query ($this->Link, 'SELECT * FROM "'.$this->Prefix.'visitors" WHERE "id" = '.(int) $ID))) return (0);
	$Data = pg_fetch_assoc ($Data, 0);
	if (!$Data) return (0);
	$Data['details'] = (boolean) pg_query ($this->Link, 'SELECT "id" FROM "'.$this->Prefix.'details" WHERE "id" = '.$Data['id'].' LIMIT 1');
	$Array[] = $Data;
	if ($Data['previous']) $this->visits_previous ($Data['previous'], ++$Level);
	return ($Array);
	}
function visits_next ($ID, $Level = 0) {
	static $Array;
	if (!$Array) $Array = array ();
	if ($Level == 10) return (0);
	if (!pg_num_rows ($Data = pg_query ($this->Link, 'SELECT * FROM "'.$this->Prefix.'visitors" WHERE "previous" = '.(int) $ID))) return (0);
	$Data = pg_fetch_assoc ($Data, 0);
	if (!$Data) return (0);
	$Data['details'] = (boolean) pg_query ($this->Link, 'SELECT "id" FROM "'.$this->Prefix.'details" WHERE "id" = '.$Data['id'].' LIMIT 1');
	$Array[] = $Data;
	$this->visits_next ($Data['id'], ++$Level);
	if ($Array) return (array_reverse ($Array));
	return (0);
	}
function visits_online () {
	return ($this->query ('SELECT COUNT("id") FROM "'.$this->Prefix.'visitors" WHERE (('.time ().' - EXTRACT(EPOCH FROM "lastvisit")) < 300)', 1));
	}
function visits_most ($Type, $From = 0, $To = 0, $Unit = 'day') {
	$Units = array (
	'hour' => 'YYYY.MM.DD HH',
	'day' => 'YYYY.MM.DD',
	'month' => 'YYYY.MM',
	'year' => 'YYYY'
	);
	$Data = pg_fetch_assoc (pg_query ($this->Link, 'SELECT to_char("time", \''.$Units[$Unit].'\') AS "unit", "time", SUM("unique") AS "unique", SUM("views") AS "views", SUM("returns") AS "returns" FROM "'.$this->Prefix.'time"'.$this->time_clause ($From, $To).' GROUP BY "unit", "time", "unique", "views", "returns" ORDER BY "'.$Type.'" DESC LIMIT 1'), 0);
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
	return ((int) $this->query ('SELECT SUM("unique") FROM "'.$this->Prefix.'ignored"'.($From?' WHERE "lastvisit" >= '.$From.($To?' AND "lastvisit" < '.$To:''):''), 1));
	}
function visits_ignored ($Amount, $From) {
	$Data = array ();
	$Result = pg_query ($this->Link, 'SELECT * FROM "'.$this->Prefix.'ignored" ORDER BY "lastview" DESC'.($Amount?' OFFSET '.$From.' LIMIT '.$Amount:''));
	for ($i = 0, $c = pg_num_rows ($Result); $i < $c; ++$i) $Data[] = pg_fetch_assoc ($Result, $i);
	pg_free_result ($Result);
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
	'hour' => 'YYYY.MM.DD HH',
	'dayhour' => 'HH',
	'day' => 'YYYY.MM.DD',
	'weekday' => 'D',
	'month' => 'YYYY.MM',
	'year' => 'YYYY'
	);
	$Result = pg_query ($this->Link, 'SELECT to_char("time", \''.$Units[$Unit].'\') AS "unit", '.implode (', ', $Select).' FROM "'.$this->Prefix.'time"'.$this->time_clause ($From, $To).' GROUP BY "unit" ORDER BY "unit" ASC');
	$Data = array ();
	for ($i = 0, $c = pg_num_rows ($Result); $i < $c; ++$i) {
		$Row = pg_fetch_assoc ($Result, $i);
		if ($Unit == 'weekday') --$Row['unit'];
		if ($Bits & 1) $Data[$Row['unit']]['views'] = ($Row['views'] + $Row['unique'] + $Row['returns']);
		if ($Bits & 2) $Data[$Row['unit']]['unique'] = ($Row['unique'] + $Row['returns']);
		if ($Bits & 4) $Data[$Row['unit']]['returns'] = (int) $Row['returns'];
		}
	pg_free_result ($Result);
	return ($Data);
	}
function logs ($Amount, $From, $Clause = '') {
	$Result = pg_query ($this->Link, 'SELECT * FROM "'.$this->Prefix.'logs"'.$Clause.' ORDER BY "time"'.($Amount?' OFFSET '.$From.' LIMIT '.$Amount:''));
	$Data = pg_fetch_all ($Result);
	pg_free_result ($Result);
	return ($Data);
	}
function logs_search_clause ($Array) {
	return ((($Array['from'] && $Array['to'])?'WHERE "time" >= \''.date ('Y-m-d H:i:s', strtotime ($Array['from'])).'\' AND "time" <= \''.date ('Y-m-d H:i:s', strtotime ($Array['to'])).'\'':'').(isset ($Array['filter'])?' AND ("log" = '.implode (' OR "log" = ', $Array['filter']).')':'').($Array['search']?' AND ("'.implode ('" LIKE \'%'.sqlite_escape_string ($Array['search']).'%\' OR "', array ('log', 'time', 'user', 'ip', 'db', 'table', 'additional')).'" LIKE \'%'.sqlite_escape_string ($Array['search']).'%\')':''));
	}
function backup_restore ($BackupID) {
	if (!is_file ($GLOBALS['DataDir'].'backups/'.$BackupID.'.bak')) return (0);
	$Status = 1;
	if (strstr ($BackupID, 'sql')) {
		$Array = explode (';
', file_get_contents ($GLOBALS['DataDir'].'backups/'.$BackupID.'.bak'));
		for ($i = 0, $c = count ($Array); $i < $c; ++$i) {
			if ($Array[$i]) pg_query ($this->Link, $Array[$i]) or $Status = 0;
			}
		}
	else {
		pg_query ($this->Link, 'BEGIN WORK');
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
							if (!include ('./lib/db/PostgreSQL/schema.php')) {
								e_error_message ('lib/db/PostgreSQL/schema.php', __FILE__, __LINE__);
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
					if ($Replace) pg_query ($this->Link, 'DELETE FROM "'.$this->Prefix.$Table.'"');
					else if ($Recreate || $Create) {
						if ($this->query ('SELECT "tablename" FROM "pg_tables" WHERE schemaname=\'public\' AND "tablename" = \''.$this->Prefix.$Table.'\'', 1)) {
						if ($Create) return (0);
						else pg_query ($this->Link, 'DROP TABLE IF EXISTS "'.$this->Prefix.$Table.'"');
						}
						pg_query ($this->Link, 'CREATE TABLE "'.$this->Prefix.$Table.'" '.$Schema[$Table]);
						}
					pg_query ($this->Link, 'LOCK TABLE "'.$this->Prefix.$Table.'" IN EXCLUSIVE MODE');
					}
				else {
					$Array = explode (chr (30), strtr ($Buffer, array (
			'\r' => "\r",
			'\n' =>"\n"
			)));
					if (!$Fields) $Fields = count ($Array);
					for ($i = 0; $i < $Fields; ++$i) {
						if (!$Array[$i] && $Array[$i] !== 0 && $Table == 'visitors') $Array[$i] = 'NULL';
						else $Array[$i] = '\''.pg_escape_string ($Array[$i]).'\'';
						}
					if (!pg_query ($this->Link, 'INSERT INTO "'.$this->Prefix.$Table.'" VALUES('.implode (',', $Array).')')) $Status = 0;
					}
				$Buffer = '';
				}
			else $Buffer.= $Byte;
			}
		$Status = (boolean) pg_query ($this->Link, 'COMMIT WORK');
		}
	return ($Status);
	}
function clean () {
	if (!isset ($GLOBALS['Detailed']['keepalldata']) || $GLOBALS['Detailed']['keepalldata']) return;
	global $Detailed;
	$UniqueIDs = array ();
	$MaxTime = (int) $this->query ('SELECT MAX("time") AS "maxtime" FROM "'.$this->Prefix.'details" GROUP BY "id" ORDER BY "maxtime" DESC OFFSET '.($Detailed['amount'] * $Detailed['maxpages']).' LIMIT 1', 1);
	$Result = pg_query ($this->Link, 'SELECT "id", MAX("time"), MIN("time") FROM "'.$this->Prefix.'details" GROUP BY "id"');
	for ($i = 0, $c = pg_num_rows ($Result); $i < $c; ++$i) {
		$Row = pg_fetch_row ($Result, $i);
		if ($Row[1] <= $MaxTime && (time () - strtotime ($Row[2])) > $Detailed['period'] && (time () - strtotime ($Row[2])) > $GLOBALS['VisitTime']) $UniqueIDs[] = $Row[0];
		}
	pg_free_result ($Result);
	if ($UniqueIDs) {
		if (!$Detailed['keepalldata']) pg_query ($this->Link, 'DELETE FROM "'.$this->Prefix.'details" WHERE "id" = '.implode (' OR "id" = ', $UniqueIDs));
		pg_query ($this->Link, 'DELETE FROM "'.$this->Prefix.'visitors" WHERE "id" = '.implode (' OR "id" = ', $UniqueIDs));
		}
	}
function verify ($Schema) {
	foreach ($Schema as $Key => $Value) {
		if (!pg_query ($this->Link, 'SELECT * FROM "pg_class" WHERE "relname" = \''.$this->Prefix.$Key.'\'')) return (0);
		}
	return (e_verify_configuration ());
	}
function verify_configuration_option ($Name, $Mode) {
	return ((boolean) pg_query ($this->Link, 'SELECT * FROM "'.$this->Prefix.'configuration" WHERE "name" = \''.$Name.'\' AND "mode" = '.$Mode));
	}
function table_reset ($Tables) {
	for ($i = 0, $c = count ($Tables); $i < $c; ++$i) {
		if (!in_array ($Tables[$i], array ('configuration', 'logs'))) pg_query ($this->Link, in_array ($Tables[$i], 'TRUNCATE TABLE "'.$this->Prefix.$Tables[$i].'"'));
		}
	}
function delete_row ($Table, $Row) {
	pg_query ($this->Link, 'DELETE FROM "'.$this->Prefix.$Table.'" WHERE "name" = "'.$Row.'"');
	return (pg_affected_rows ($this->Link));
	}
function table_rows_amount ($Table, $Clause = '') {
	return ($this->query ('SELECT COUNT(*) FROM "'.$this->Prefix.$Table.'"'.($Clause?' '.$Clause:''), 1));
	}
function db_size () {
	global $DBTables;
	$DBSize = 0;
	for ($i = 0, $c = count ($DBTables); $i < $c; ++$i) $DBSize += $this->query ('SELECT PG_RELATION_SIZE(\''.$this->Prefix.$DBTables[$i].'\')', 1);
	return ($DBSize);
	}
}
?>