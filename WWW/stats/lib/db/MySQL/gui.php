<?php
class estats_db_gui extends estats_db {
function data ($Group, $Amount, $From = 0, $To = 0) {
	$Data['data'] = array ();
	$Query = $this->time_clause ($From, $To);
	if ($Group == 'browsersversions' || $Group == 'osesversions') $Table = substr ($Group, 0, -8);
	else if ($Group == 'cities' || $Group == 'countries' || $Group == 'continents' || $Group == 'world' || substr ($Group, 0, 7) == 'country' || substr ($Group, 0, 6) == 'cities' || substr ($Group, 0, 7) == 'regions') $Table = 'geoip';
	else $Table = $Group;
	$GroupBy = '`name`';
	if ($Group == 'browsersversions' || $Group == 'osesversions') {
		$Result = $this->query ('SELECT CONCAT(`name`, " ", `version`) AS `var`, SUM(`amount`) AS `amount` FROM `'.$this->Prefix.$Table.'`'.$Query.' GROUP BY `var` ORDER BY `amount` DESC LIMIT '.$Amount);
		while ($Row = $this->fetch ($Result)) $Data['data'][$Row[0]] = $Row[1];
		$GroupBy = '`name`, `version`';
		}
	else if ($Group == 'sites') {
		$Result = $this->query ('SELECT `name`, SUM(`amount`) AS `amount`, `address` FROM `'.$this->Prefix.'sites`'.$Query.' GROUP BY `address` ORDER BY `amount` DESC LIMIT '.$Amount);
		while ($Row = $this->fetch ($Result)) $Data['data'][$Row[2]] = array ($Row[1], ($Row[0]?$Row[0]:$Row[2]));
		$GroupBy = '`name`, `address`';
		}
	else if (substr ($Group, 0, 6) == 'cities') {
		$Result = $this->query ('SELECT '.((strlen ($Group) < 7)?'CONCAT(`city`, "-", `country`)':'`city`').' AS `var`, `latitude`, `longitude`, SUM(`amount`) AS `amount` FROM `'.$this->Prefix.'geoip`'.$Query.' '.($Query?'AND':'WHERE').' `city` != ""'.((isset ($Group[6]) && $Group[6] == '_')?' AND `country` = "'.$this->escape (substr ($Group, 7)).'"':'').' GROUP BY `var` ORDER BY `amount` DESC LIMIT '.$Amount);
		while ($Row = $this->fetch ($Result)) $Data['data'][$Row[0]] = array ($Row[3], $Row[1], $Row[2]);
		$GroupBy = '`city`, `country`';
		$Query.= ' '.($Query?'AND':'WHERE').' `city` != ""'.((isset ($Group[6]) && $Group[6] == '_')?' AND `country` = "'.$this->escape (substr ($Group, 7)).'"':'');
		}
	else if (substr ($Group, 0, 7) == 'regions') {
		$Result = $this->query ('SELECT CONCAT(`'.substr ($Group, 8).'`, "-", `region`), SUM(`amount`) AS `amount` FROM `'.$this->Prefix.'geoip`'.$Query.' '.($Query?'AND':'WHERE').' `country` = "'.$this->escape (substr ($Group, 8)).'" GROUP BY `region` ORDER BY `amount` DESC LIMIT '.$Amount);
		while ($Row = $this->fetch ($Result)) $Data['data'][$Row[0]] = $Row[1];
		$GroupBy = '`region`';
		$Query.= ' '.($Query?'AND':'WHERE').' `country` = "'.$this->escape (substr ($Group, 8)).'"';
		}
	else if ($Group == 'countries') {
		$Result = $this->query ('SELECT `country`, SUM(`amount`) AS `amount` FROM `'.$this->Prefix.'geoip`'.$Query.' GROUP BY `country` ORDER BY `amount` DESC LIMIT '.$Amount);
		while ($Row = $this->fetch ($Result)) $Data['data'][$Row[0]] = $Row[1];
		$GroupBy = '`country`';
		}
	else if ($Group == 'continents') {
		$Result = $this->query ('SELECT `continent`, SUM(`amount`) AS `amount` FROM `'.$this->Prefix.'geoip`'.$Query.' GROUP BY `continent` ORDER BY `amount` DESC LIMIT '.$Amount);
		while ($Row = $this->fetch ($Result)) $Data['data'][$Row[0]] = $Row[1];
		$GroupBy = '`continent`';
		}
	else if ($Group == 'world') {
		$Result = $this->query ('SELECT `country`, `continent`, SUM(`amount`) AS `amount` FROM `'.$this->Prefix.'geoip`'.$Query.' GROUP BY `country`, `continent` ORDER BY `amount` DESC');
		while ($Row = $this->fetch ($Result)) $Data['data'][$Row[0]] = array ($Row[1], $Row[2]);
		$GroupBy = '`country`, `continent`';
		}
	else {
		$Result = $this->query ('SELECT `name`, SUM(`amount`) AS `amount` FROM `'.$this->Prefix.$Table.'`'.$Query.' GROUP BY `name` ORDER BY `amount` DESC LIMIT '.$Amount);
		while ($Row = $this->fetch ($Result)) $Data['data'][$Row[0]] = $Row[1];
		}
	$this->free ($Result);
	$Result = $this->query ('SELECT COUNT(`amount`) FROM `'.$this->Prefix.$Table.'`'.$Query.' GROUP BY '.$GroupBy);
	$Data['amount'] = (MySQLi?mysqli_num_rows ($Result):mysql_num_rows ($Result));
	$this->free ($Result);
	$Data['sum'] = $this->query ('SELECT SUM(`amount`) FROM `'.$this->Prefix.$Table.'`'.$Query, 1);
	return ($Data);
	}
function data_countries () {
	$Result = $this->query ('SELECT `'.$this->Prefix.'country` FROM `geoip` GROUP BY `country`');
	$Array = array ();
	while ($Row = $this->fetch ($Result)) $Array[] = $Row[0];
	$this->free ($Result);
	return ($Array);
	}
function visit_details ($ID, $Page) {
	if (!$Data = $this->fetch ($this->query ('SELECT * FROM `'.$this->Prefix.'visitors` WHERE `id` = '.(int) $ID), 1)) return (0);
	global $Detailed;
	if ($Page < 1 || $Page > ceil ($Data['visitsamount'] / $Detailed['detailsamount'])) $Page = 1;
	$Result = $this->query ('SELECT * FROM `'.$this->Prefix.'details` WHERE `id` = '.(int) $ID.' ORDER BY `time` DESC LIMIT '.($Detailed['detailsamount'] * ($Page - 1)).', '.$Detailed['detailsamount']);
	$Sites = array ();
	$i = 0;
	while ($Row = $this->fetch ($Result, 1)) {
		$Sites[$i] = $Row;
		$Sites[$i]['title'] = $this->query ('SELECT `name` FROM `'.$this->Prefix.'sites` WHERE `address` = \''.$this->escape ($Sites[$i]['address']).'\' ORDER BY `time` DESC LIMIT 1', 1);
		++$i;
		}
	$this->free ($Result);
	return (array ('data' => $Data, 'sites' => $Sites, 'page' => $Page));
	}
function visits ($Robots, $Page) {
	global $Detailed;
	$Amount = $this->query ('SELECT COUNT(*) FROM `'.$this->Prefix.'visitors`'.($Robots?'':' WHERE `robot` LIKE 0'), 1);
	$PagesAmount = ceil ($Amount / $Detailed['amount']);
	if ($PagesAmount > $Detailed['maxpages'] && ESTATS_USERLEVEL < 2) {
		$Amount = ($Detailed['amount'] * $Detailed['maxpages']);
		$PagesAmount = $Detailed['maxpages'];
		}
	if ($Page < 1 || $Page > $PagesAmount) $Page = 1;
	$Data = array ('data' => array (), 'page' => $Page, 'amount' => $Amount);
	$Result = $this->query ('SELECT * FROM `'.$this->Prefix.'visitors`'.($Robots?'':' WHERE `robot` LIKE 0').' ORDER BY `lastvisit` DESC LIMIT '.($Detailed['amount'] * ($Page - 1)).', '.$Detailed['amount']);
	$i = 0;
	while ($Row = $this->fetch ($Result, 1)) {
		$Data['data'][$i] = $Row;
		$Data['data'][$i]['details'] = (boolean) $this->query ('SELECT `id` FROM `'.$this->Prefix.'details` WHERE `id` = '.$Data['data'][$i]['id'].' LIMIT 1');
		++$i;
		}
	$this->free ($Result);
	return ($Data);
	}
function visits_previous ($ID, $Level = 0) {
	static $Array;
	if (!$Array) $Array = array ();
	if ($Level == 10) return (0);
	$Data = $this->fetch ($this->query ('SELECT * FROM `'.$this->Prefix.'visitors` WHERE `id` = '.(int) $ID), 1);
	if (!$Data) return (0);
	$Data['details'] = (boolean) $this->query ('SELECT `id` FROM `'.$this->Prefix.'details` WHERE `id` = '.$Data['id'].' LIMIT 1');
	$Array[] = $Data;
	if ($Data['previous']) $this->visits_previous ($Data['previous'], ++$Level);
	return ($Array);
	}
function visits_next ($ID, $Level = 0) {
	static $Array;
	if (!$Array) $Array = array ();
	if ($Level == 10) return (0);
	$Data = $this->fetch ($this->query ('SELECT * FROM `'.$this->Prefix.'visitors` WHERE `previous` = '.(int) $ID), 1);
	if (!$Data) return (0);
	$Data['details'] = (boolean) $this->query ('SELECT `id` FROM `'.$this->Prefix.'details` WHERE `id` = '.$Data['id'].' LIMIT 1');
	$Array[] = $Data;
	$this->visits_next ($Data['id'], ++$Level);
	if ($Array) return (array_reverse ($Array));
	return (0);
	}
function visits_online () {
	return ($this->query ('SELECT COUNT(`id`) FROM `'.$this->Prefix.'visitors` WHERE (('.time ().' - UNIX_TIMESTAMP(`lastvisit`)) < 300)', 1));
	}
function visits_most ($Type, $From = 0, $To = 0, $Unit = 'day') {
	$Units = array (
	'hour' => '%Y.%m.%d %H',
	'day' => '%Y.%m.%d',
	'month' => '%Y.%m',
	'year' => '%Y'
	);
	$Data = $this->fetch ($this->query ('SELECT DATE_FORMAT(`time`, \''.$Units[$Unit].'\') AS `unit`, `time`, SUM(`unique`) AS `unique`, SUM(`views`) AS `views`, SUM(`returns`) AS `returns` FROM `'.$this->Prefix.'time`'.$this->time_clause ($From, $To).' GROUP BY `unit` ORDER BY `'.$Type.'` DESC LIMIT 1'), 1);
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
	return ((int) $this->query ('SELECT SUM(`unique`) FROM `'.$this->Prefix.'ignored`'.($From?' WHERE `lastvisit` >= "'.date ('Y-m-d H:i:s', $From).'"'.($To?' AND `lastvisit` < "'.date ('Y-m-d H:i:s', $To).'"':''):''), 1));
	}
function visits_ignored ($Amount, $From) {
	$Data = array ();
	$Result = $this->query ('SELECT * FROM `'.$this->Prefix.'ignored` ORDER BY `lastview` DESC'.($Amount?' LIMIT '.$From.', '.$Amount:''));
	while ($Row = $this->fetch ($Result, 1)) $Data[] = $Row;
	$this->free ($Result);
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
		if ($Select || $Add) $Select[] = 'SUM(`'.$AvailableTypes[$i].'`) AS `'.$AvailableTypes[$i].'`';
		}
	$Units = array (
	'hour' => '%Y.%m.%d %H',
	'dayhour' => '%H',
	'day' => '%Y.%m.%d',
	'weekday' => '%w',
	'month' => '%Y.%m',
	'year' => '%Y'
	);
	$Result = $this->query ('SELECT DATE_FORMAT(`time`, \''.$Units[$Unit].'\') AS `unit`, '.implode (', ', $Select).' FROM `'.$this->Prefix.'time`'.$this->time_clause ($From, $To).' GROUP BY `unit` ORDER BY `unit` ASC');
	$Data = array ();
	while ($Row = $this->fetch ($Result, 1)) {
		if ($Bits & 1) $Data[$Row['unit']]['views'] = ($Row['views'] + $Row['unique'] + $Row['returns']);
		if ($Bits & 2) $Data[$Row['unit']]['unique'] = ($Row['unique'] + $Row['returns']);
		if ($Bits & 4) $Data[$Row['unit']]['returns'] = (int) $Row['returns'];
		}
	$this->free ($Result);
	return ($Data);
	}
function logs ($Amount, $From, $Clause = '') {
	$Data = array ();
	$Result = $this->query ('SELECT * FROM `'.$this->Prefix.'logs`'.$Clause.' ORDER BY `time`'.($Amount?' LIMIT '.$From.', '.$Amount:''));
	while ($Row = $this->fetch ($Result, 1)) $Data[] = $Row;
	$this->free ($Result);
	return ($Data);
	}
function logs_search_clause ($Array) {
	return ((($Array['from'] && $Array['to'])?'WHERE `time` >= "'.date ('Y-m-d H:i:s', strtotime ($Array['from'])).'" AND `time` <= "'.date ('Y-m-d H:i:s', strtotime ($Array['to'])).'"':'').(isset ($Array['filter'])?' AND (`log` = '.implode (' OR `log` = ', $Array['filter']).')':'').($Array['search']?' AND (`'.implode ('` LIKE "%'.sqlite_escape_string ($Array['search']).'%" OR `', array ('log', 'time', 'user', 'ip', 'db', 'table', 'additional')).'` LIKE "%'.sqlite_escape_string ($Array['search']).'%")':''));
	}
function backup_restore ($BackupID) {
	if (!is_file ($GLOBALS['DataDir'].'backups/'.$BackupID.'.bak')) return (0);
	$Status = 1;
	if (strstr ($BackupID, 'sql')) {
		$Array = explode (';
', file_get_contents ($GLOBALS['DataDir'].'backups/'.$BackupID.'.bak'));
		for ($i = 0, $c = count ($Array); $i < $c; ++$i) {
			if ($Array[$i]) $this->query ($Array[$i]) or $Status = 0;
			}
		unset ($Array);
		}
	else {
		$this->query ('START TRANSACTION');
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
							if (!include ('./lib/db/MySQL/schema.php')) {
								e_error_message ('lib/db/MySQL/schema.php', __FILE__, __LINE__);
								return (0);
								}
							}
						}
					++$Line;
					$Buffer = '';
					continue;
					}
				if (substr ($Buffer, 0, 8) == '/*Table:') {
					if ($Table) $this->query ('UNLOCK TABLES');
					$Table = substr ($Buffer, 9, -2);
					$Fields = 0;
					if ($Replace) $this->query ('DELETE FROM `'.$this->Prefix.$Table.'`');
					else if ($Recreate || $Create) {
						if ($this->query ('SHOW TABLES LIKE "'.$this->Prefix.$Table.'"', 1)) {
							if ($Create) return (0);
							else $this->query ('DROP TABLE IF EXISTS `'.$this->Prefix.$Table.'`');
							}
						if (!$this->query ('CREATE TABLE `'.$this->Prefix.$Table.'` '.$Schema[$Table])) $Status = 0;
						}
					$this->query ('LOCK TABLES `'.$this->Prefix.$Table.'` WRITE');
					}
				else {
					$Array = explode (chr (30), strtr ($Buffer, array (
			'\r' => "\r",
			'\n' =>"\n"
			)));
					if (!$Fields) $Fields = count ($Array);
					for ($i = 0; $i < $Fields; ++$i) {
						$Array[$i] = $this->escape ($Array[$i]);
						}
					if (!$this->query ('INSERT INTO `'.$this->Prefix.$Table.'` VALUES("'.implode ('", "', $Array).'")')) $Status = 0;
					}
				$Buffer = '';
				}
			else $Buffer.= $Byte;
			}
		$this->query ('COMMIT');
		}
	return ($Status);
	}
function clean () {
	if (!isset ($GLOBALS['Detailed']['keepalldata']) || $GLOBALS['Detailed']['keepalldata']) return;
	global $Detailed;
	$UniqueIDs = array ();
	$Time = (int) $this->query ('SELECT MAX(`time`) AS `maxtime` FROM `'.$this->Prefix.'details` GROUP BY `id` ORDER BY `maxtime` DESC LIMIT '.($Detailed['amount'] * $Detailed['maxpages']).', 1', 1);
	$Result = $this->query ('SELECT `id`, MAX(`time`), MIN(`time`) FROM `'.$this->Prefix.'details` GROUP BY `id`');
	while ($Row = $this->fetch ($Result)) {
		if ($Row[1] <= $Time && (time () - strtotime ($Row[2])) > $Detailed['period'] && (time () - strtotime ($Row[2])) > $GLOBALS['VisitTime']) $UniqueIDs[] = $Row[0];
		}
	$this->free ($Result);
	if ($UniqueIDs) {
		if (!$Detailed['keepalldata']) $this->query ('DELETE FROM `'.$this->Prefix.'details` WHERE `id` = '.implode (' OR `id` = ', $UniqueIDs));
		$this->query ('DELETE FROM `'.$this->Prefix.'visitors` WHERE `id` = '.implode (' OR `id` = ', $UniqueIDs));
		}
	}
function verify ($Schema) {
	foreach ($Schema as $Key => $Value) {
		if (!$this->query ('SHOW TABLES LIKE "'.$this->Prefix.$Key.'"', 1)) return (0);
		}
	return (1);
/// FIXME
//	return (e_verify_configuration ());
	}
function verify_configuration_option ($Name, $Mode) {
	return ((boolean) $this->query ('SELECT `name` FROM `'.$this->Prefix.'configuration` WHERE `name` = "'.$Name.'" AND `mode` = '.$Mode));
	}
function table_reset ($Tables) {
	for ($i = 0, $c = count ($Tables); $i < $c; ++$i) {
		if (!in_array ($Tables[$i], array ('configuration', 'logs'))) $this->query ('DELETE FROM `'.$this->Prefix.$Tables[$i].'`');
		}
	}
function table_row_delete ($Table, $Row) {
	$this->query ('DELETE FROM `'.$this->Prefix.$Table.'` WHERE `name` = "'.$Row.'"');
	return ($this->changes ());
	}
function table_rows_amount ($Table, $Clause = '') {
	return ($this->query ('SELECT COUNT(*) FROM `'.$this->Prefix.$Table.'`'.($Clause?' '.$Clause:''), 1));
	}
function db_size () {
	global $DBTables, $DBName;
	$DBSize = 0;
	$Result = $this->query ('SHOW TABLE STATUS FROM `'.$DBName.'` LIKE "'.$this->Prefix.'%"');
	while ($Row = $this->fetch ($Result, 1)) if (in_array (substr ($Row['Name'], strlen ($this->Prefix)), $DBTables)) $DBSize += $Row['Data_length'];
	$this->free ($Result);
	return ($DBSize);
	}
}
?>