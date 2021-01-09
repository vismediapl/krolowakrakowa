<?php
class estats_db {
var $About;
var $Link;
var $Prefix;
function estats_db ($Connect = 1) {
	$this->About = array (
	'en' => 'This module uses MySQL database, version 3.23 and above (including <em>MySQLi</em> extension).',
	'pl' => 'Moduł wykorzystuje bazę danych MySQL w wersji co najmniej 3.23 (włączając rozszerzenie <em>MySQLi</em>).',
	);
	if ($Connect && !$this->connect (0, $GLOBALS['DBPrefix'], $GLOBALS['PConnect'], $GLOBALS['DBHost'], $GLOBALS['DBUser'], $GLOBALS['DBPass'], $GLOBALS['DBName'])) e_error_message ('Could not connect to database!', __FILE__, __LINE__, 1);
	}
function connect ($Test, $DBPrefix, $PConnect, $DBHost, $DBUser, $DBPass, $DBName) {
	if (!defined ('MySQLi')) {
		if (function_exists ('mysql_query')) define ('MySQLi', 0);
		else if (function_exists ('mysqli_query')) define ('MySQLi', 1);
		else {
			if (!$Test) e_error_message ('This module does not supported on this server!', __FILE__, __LINE__, 1);
			return (0);
			}
		}
	$Version = '?';
	$this->Prefix = $DBPrefix;
	if (defined ('MySQLi')) {
		if (MySQLi) {
			if ($this->Link = mysqli_connect ($DBHost, $DBUser, $DBPass, $DBName)) $Version = mysqli_get_server_info ($this->Link);
			else return (0);
			}
		else {
			$ConnectionType = 'mysql_'.($PConnect?'p':'').'connect';
			if ($this->Link = $ConnectionType ($DBHost, $DBUser, $DBPass)) {
				$Version = mysql_get_server_info ();
				if (!mysql_select_db ($DBName)) return (0);
				}
			}
		}
	$GLOBALS['DBInfo']['DBVersion'] = $Version;
	return (1);
	}
function escape ($String) {
	if (MySQLi) return (mysqli_escape_string ($this->Link, $String));
	else return (mysql_real_escape_string ($String));
	}
function changes () {
	if (MySQLi) return (mysqli_affected_rows ($this->Link));
	else return (mysql_affected_rows ());
	}
function fetch ($Result, $Assoc = 0) {
	if (!$Result) return (0);
	if (MySQLi) return (mysqli_fetch_array ($Result, ($Assoc?MYSQLI_ASSOC:MYSQLI_NUM)));
	else return (mysql_fetch_array ($Result, ($Assoc?MYSQL_ASSOC:MYSQL_NUM)));
	}
function query ($Query, $ReturnResult = 0) {
	if (MySQLi) $Result = mysqli_query ($this->Link, $Query);
	else $Result = mysql_query ($Query);
	if ($ReturnResult) {
		$Result = $this->fetch ($Result);
		return (($ReturnResult && is_array ($Result))?implode ('', $Result):$Result);
		}
	else return ($Result);
	}
function free ($Result) {
	if (!$Result) return (0);
	if (MySQLi) mysqli_free_result ($Result);
	else mysql_free_result ($Result);
	}
function update ($Table, $Key) {
	$this->query ('UPDATE `'.$this->Prefix.$Table.'` SET `amount` = `amount` + 1 WHERE `name` = "'.$this->escape ($Key).'" AND `time` = "'.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'"');
	if (!$this->changes ()) $this->query ('INSERT INTO `'.$this->Prefix.$Table.'` VALUES ("'.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'", "'.$this->escape ($Key).'", 1)');
	}
function update_software ($Table, $Array) {
	$this->query ('UPDATE `'.$this->Prefix.$Table.'` SET `amount` = `amount` + 1 WHERE `name` = "'.$this->escape ($Array[0]).'" AND `version` = "'.$this->escape ($Array[1]).'" AND `time` = "'.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'"');
	if (!$this->changes ()) $this->query ('INSERT INTO `'.$this->Prefix.$Table.'` VALUES ("'.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'", "'.$this->escape ($Array[0]).'", 1, "'.$this->escape ($Array[1]).'")');
	}
function update_sites ($Array) {
	$this->query ('UPDATE `'.$this->Prefix.'sites` SET `amount` = `amount` + 1, `name` = "'.$this->escape ($Array[0]).'" WHERE `address` = "'.$this->escape ($Array[1]).'" AND `time` = "'.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['sites']].'"');
	if (!$this->changes ()) $this->query ('INSERT INTO `'.$this->Prefix.'sites` VALUES ("'.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['sites']].'", "'.$this->escape ($Array[0]).'", 1, "'.$this->escape ($Array[1]).'")');
	}
function update_geoip ($Array) {
	if (!$Array) return (0);
	$this->query ('UPDATE `'.$this->Prefix.'geoip` SET `amount` = `amount` + 1 WHERE `city` = "'.$this->escape ($Array['city']).'" AND `country` = "'.$this->escape ($Array['country']).'" AND `time` = "'.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['geoip']].'"');
	if (!$this->changes ()) $this->query ('INSERT INTO `'.$this->Prefix.'geoip` VALUES ("'.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['geoip']].'", "'.$this->escape ($Array['city']).'", "'.$this->escape ($Array['region']).'", "'.$this->escape ($Array['country']).'", "'.$this->escape ($Array['continent']).'", "'.$this->escape ($Array['latitude']).'", "'.$this->escape ($Array['longitude']).'", 1)');
	}
function update_time ($Type) {
	$this->query ('UPDATE `'.$this->Prefix.'time` SET `'.$Type.'` = `'.$Type.'` + 1 WHERE `time` = "'.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['time']].'"');
	if (!$this->changes ()) $this->query ('INSERT INTO `'.$this->Prefix.'time` VALUES ("'.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['time']].'", '.(int) ($Type == 'views').', '.(int) ($Type == 'unique').', '.(int) ($Type == 'returns').')');
	}
function update_visit_details ($ID, $Address) {
	$Date = date ('Y-m-d H:i:s');
	$this->query ('UPDATE `'.$this->Prefix.'visitors` SET `lastvisit` = "'.$Date.'", `visitsamount` = `visitsamount` + 1 WHERE `id` = '.$ID);
	$this->query ('INSERT INTO `'.$this->Prefix.'details` VALUES('.$ID.', "'.$this->escape ($Address).'", "'.$Date.'")');
	}
function update_visits_ignored ($IP, $Blocked = 0) {
	$Date = date ('Y-m-d H:i:s');
	$this->query ('UPDATE `'.$this->Prefix.'ignored` SET '.($this->query ('SELECT `ip` FROM `'.$this->Prefix.'ignored` WHERE `ip` = "'.$this->escape ($IP).'" AND ('.time ().' - UNIX_TIMESTAMP(`lastvisit`) < 4320)', 1)?'`views` = `views` + 1, `lastview`':'`unique` = `unique` + 1, `useragent` = "'.$this->escape ($_SERVER['HTTP_USER_AGENT']).'", `lastvisit`').' = "'.$Date.'" WHERE `ip` = "'.$this->escape ($IP).'" AND `type` = '.(int) $Blocked);
	if (!$this->changes ()) $this->query ('INSERT INTO `'.$this->Prefix.'ignored` VALUES("'.$Date.'", "'.$Date.'", "'.$Date.'", "'.$this->escape ($IP).'", 1, 0, "'.$this->escape ($_SERVER['HTTP_USER_AGENT']).'", '.(int) $Blocked.')');
	}
function update_info ($ID, $Array) {
	$this->query ('UPDATE `'.$this->Prefix.'visitors` SET `javascript` = '.$Array['javascript'].', `cookies` = '.$Array['cookies'].', `flash` = "'.$Array['flash'].'", `java` = '.$Array['java'].', `screen` = "'.$Array['screen'].'", `info` = 1 WHERE `id` = '.$ID);
	}
function time_clause ($From = 0, $To = 0) {
	if ($From) return (' WHERE `time` >= "'.date ('Y-m-d H:i:s', $From).'"'.($To?' AND `time` < "'.date ('Y-m-d H:i:s', $To).'"':''));
	return ('');
	}
function visits_amount ($Type, $From = 0, $To = 0) {
	$Amount = (int) $this->query ('SELECT SUM(`'.$Type.'`) FROM `'.$this->Prefix.'time`'.$this->time_clause ($From, $To), 1);
	if ($Type == 'returns') return ($Amount);
	else if ($Type == 'unique') $Amount += $this->visits_amount ('returns', $From, $To);
	else if ($Type == 'views') $Amount += $this->visits_amount ('unique', $From, $To);
	return ($Amount);
	}
function visitor_id_exists ($ID) {
	return ($this->query ('SELECT `id` FROM `'.$this->Prefix.'visitors` WHERE `id` = '.$ID, 1));
	}
function visitor_id_get ($IP) {
	return ($this->query ('SELECT `id` FROM `'.$this->Prefix.'visitors` WHERE `ip` = "'.$this->escape ($IP).'" AND ('.time ().' - UNIX_TIMESTAMP(`firstvisit`) < '.(int) ($GLOBALS['VisitTime'] / 2).') ORDER BY `id` DESC LIMIT 1', 1));
	}
function visitor_id_max () {
	return ($this->query ('SELECT MAX(`id`) FROM `'.$this->Prefix.'visitors`', 1));
	}
function visitor_add ($ID, $Array) {
	$Date = date ('Y-m-d H:i:s');
	$this->query ('INSERT INTO `'.$this->Prefix.'visitors` VALUES('.$ID.', "'.$Date.'", "'.$Date.'", 0, "'.$this->escape ($Array['ip']).'", "'.$this->escape ($Array['useragent']).'", "'.$this->escape ($Array['host']).'", "'.$this->escape ($Array['referrer']).'", "'.$this->escape ($Array['language']).'", "'.$Array['javascript'].'", "'.$Array['cookies'].'", "'.$this->escape ($Array['flash']).'", "'.$Array['java'].'", "'.$this->escape ($Array['screen']).'", "'.$Array['info'].'", "'.$this->escape ($Array['robot']).'", "'.$this->escape ($Array['proxy']).'", "'.$this->escape ($Array['proxyip']).'", "'.$Array['returned'].'")');
	}
function visitor_info_exists ($ID) {
	return ((int) $this->query ('SELECT `info` FROM `'.$this->Prefix.'visitors` WHERE `id` = '.$ID, 1));
	}
function backup_create ($Tables, $TablesStructure, $ReplaceData, $SQLFormat) {
	if ($SQLFormat) {
		if ($TablesStructure && !include (ESTATS_PATH.'lib/db/MySQL/schema.php')) return (0);
		$Backup = 'START TRANSACTION;
';
		}
	else $Backup = '';
	for ($i = 0, $c = count ($Tables); $i < $c; ++$i) {
		$Backup.= '
/*Table: '.$Tables[$i].'*/

';
		$Result = $this->query ('SELECT * FROM '.$this->Prefix.$Tables[$i]);
		$Fields = (MySQLi?mysqli_num_fields ($Result):mysql_num_fields ($Result));
		if ($SQLFormat) {
			if ($ReplaceData) $Backup.= 'DELETE FROM `'.$this->Prefix.$Tables[$i].'`;
';
			if ($TablesStructure) $Backup.= 'DROP TABLE `'.$this->Prefix.$Tables[$i].'`;
CREATE TABLE `'.$this->Prefix.$Tables[$i].'` '.$Schema[$Tables[$i]].';
LOCK TABLES `'.$this->Prefix.$Tables[$i].'` WRITE;
';
			else {
				if (MySQLi) {
					$FieldsList = mysqli_fetch_fields ($Result);
					$FieldsNames = array ();
					foreach ($FieldsList as $Value) $FieldsNames[] = $Value->name;
					}
				else {
					$FieldsList = mysql_list_fields ($GLOBALS['DBName'], $this->Prefix.$Tables[$i], $this->Link);
					$FieldsNames = array ();
					for ($j = 0; $j < $Fields; ++$j) $FieldsNames[] = mysql_field_name ($FieldsList, $j);
					}
				}
			}
		while ($Row = $this->fetch ($Result)) {
			$Values = array ();
			if ($SQLFormat) {
				for ($k = 0; $k < $Fields; $k++) $Values[] = $this->escape ($Row[$k]);
				$Backup.= ($j?',':'INSERT INTO `'.$this->Prefix.$Tables[$i].'`'.((!$TablesStructure && $SQLFormat)?'':' (`'.implode ('`, `', $FieldsNames).'`)').' VALUES').'("'.implode ('", "', $Values).'");
	';
				}
			else {
				for ($k = 0; $k < $Fields; $k++)$Values[] = strtr ($Row[$k], array (
		"\r" => '\r',
		"\n" => '\n',
		chr (30) => ''
		));
				$Backup.= implode (chr (30), $Values).'
	';
				}
			}
		if ($SQLFormat && $TablesStructure) $Backup.= 'UNLOCK TABLES;
';
		$this->free ($Result);
		}
	return ($Backup.($SQLFormat?'COMMIT;':''));
	}
function log ($Log, $Info = '') {
	if (!defined ('ESTATS_CRITICAL')) $this->query ('INSERT INTO `'.$this->Prefix.'logs` VALUES("'.date ('Y-m-d H:i:s').'", '.(int) $Log.', "'.($Info?$this->escape ($Info):'').'")');
	}
function config_get ($Mode) {
	$Data = array ();
	$Result = $this->query ('SELECT * FROM `'.$this->Prefix.'configuration` WHERE `mode` = '.(int) $Mode);
	while ($Row = $this->fetch ($Result)) $Data[$Row[0]] = $Row[1];
	$this->free ($Result);
	return ($Data);
	}
function config_set ($Array, $Notify = 1) {
	foreach ($Array as $Key => $Value) {
		$this->query ('UPDATE `'.$this->Prefix.'configuration` SET `value` = "'.$this->escape ($Value).'" WHERE `name` = "'.$Key.'"');
		if ($this->changes () < 1) $this->query ('INSERT INTO `'.$this->Prefix.'configuration` VALUES("'.$Key.'", "'.$this->escape ($Value).'", 1)');
		}
	e_config_get (0, 1);
	e_config_get (1, 1);
	if ($Notify) e_log (2, 1);
	}
function disconnect () {
	if (!isset ($GLOBALS['PConnect']) || !$GLOBALS['PConnect']) (MySQLi?mysqli_close ($this->Link):mysql_close ());
	}
}
?>