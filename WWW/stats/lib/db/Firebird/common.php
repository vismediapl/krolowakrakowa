<?php
class estats_db {
var $About;
var $Link;
var $Prefix;
function estats_db ($Connect = 1) {
	$this->About = array (
	'en' => 'This module uses Firebird database version 2.0 and compatible.',
	'pl' => 'Moduł wykorzystuje bazę danych FireBird 2.0 i kompatybilne.',
	);
	if ($Connect && !$this->connect (0, $GLOBALS['DBPrefix'], $GLOBALS['PConnect'], $GLOBALS['DBAddress'], $GLOBALS['DBUser'], $GLOBALS['DBPass'])) e_error_message ('Could not connect to database!', __FILE__, __LINE__, 1);
	}
function connect ($Test, $DBPrefix, $PConnect, $DBAddress, $DBUser, $DBPass) {
	$GLOBALS['DBInfo']['DBVersion'] = '?';
	$ConnectionType = 'ibase_'.($PConnect?'p':'').'connect';
	if (function_exists ('ibase_query')) {
		if ($this->Link = $ConnectionType ($DBAddress, $DBUser, $DBPass, 'UTF8')) $this->Prefix = $DBPrefix;
		else return (0);
		}
	else {
		if (!$Test) e_error_message ('This module does not supported on this server!', __FILE__, __LINE__, 1);
		return (0);
		}
	return (1);
	}
function escape ($String) {
	return (str_replace ('\'', '\'\'', $String));
	}
function query ($Query, $ReturnResult = 0) {
	$Result = ibase_query ($this->Link, $Query);
	if ($ReturnResult) {
		$Result = ibase_fetch_row ($Result);
		return (($ReturnResult && is_array ($Result))?implode ('', $Result):$Result);
		}
	else return ($Result);
	}
function update ($Table, $Key) {
	ibase_query ($this->Link, 'UPDATE "'.$this->Prefix.$Table.'" SET "amount" = "amount" + 1 WHERE "name" = \''.$this->escape ($Key).'\' AND "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'\'');
	if (!ibase_affected_rows ($this->Link)) ibase_query ($this->Link, 'INSERT INTO "'.$this->Prefix.$Table.'" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'\', \''.$this->escape ($Key).'\', 1)');
	}
function update_software ($Table, $Array) {
	ibase_query ($this->Link, 'UPDATE "'.$this->Prefix.$Table.'" SET "amount" = "amount" + 1 WHERE "name" = \''.$this->escape ($Array[0]).'\' AND "version" = \''.$this->escape ($Array[1]).'\' AND "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'\'');
	if (!ibase_affected_rows ($this->Link)) ibase_query ($this->Link, 'INSERT INTO "'.$this->Prefix.$Table.'" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'\', \''.$this->escape ($Array[0]).'\', 1, \''.$this->escape ($Array[1]).'\')');
	}
function update_sites ($Array) {
	ibase_query ($this->Link, 'UPDATE "'.$this->Prefix.'sites" SET "amount" = "amount" + 1, "name" = \''.$this->escape ($Array[0]).'\' WHERE "address" = \''.$this->escape ($Array[1]).'\' AND "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['sites']].'\'');
	if (!ibase_affected_rows ($this->Link)) ibase_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'sites" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['sites']].'\', \''.$this->escape ($Array[0]).'\', 1, \''.$this->escape ($Array[1]).'\')');
	}
function update_geoip ($Array) {
	if (!$Array) return (0);
	ibase_query ($this->Link, 'UPDATE "'.$this->Prefix.'geoip" SET "amount" = "amount" + 1 WHERE "city" = \''.$this->escape ($Array['city']).'\' AND "country" = \''.$this->escape ($Array['country']).'\' AND "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['geoip']].'\'');
	if (!ibase_affected_rows ($this->Link)) ibase_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'geoip" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['geoip']].'\', \''.$this->escape ($Array['city']).'\', \''.$this->escape ($Array['region']).'\', \''.$this->escape ($Array['country']).'\', \''.$this->escape ($Array['continent']).'\', \''.$this->escape ($Array['latitude']).'\', \''.$this->escape ($Array['longitude']).'\', 1)');
	}
function update_time ($Type) {
	ibase_query ($this->Link, 'UPDATE "'.$this->Prefix.'time" SET "'.$Type.'" = "'.$Type.'" + 1 WHERE "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['time']].'\'');
	if (!ibase_affected_rows ($this->Link)) ibase_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'time" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['time']].'\', '.(int) ($Type == 'views').', '.(int) ($Type == 'unique').', '.(int) ($Type == 'returns').')');
	}
function update_visit_details ($ID, $Address) {
	$Date = date ('Y-m-d H:i:s');
	ibase_query ($this->Link, 'UPDATE "'.$this->Prefix.'visitors" SET "lastvisit" = \''.$Date.'\', "visitsamount" = "visitsamount" + 1 WHERE "id" = '.$ID);
	ibase_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'details" VALUES('.$ID.', \''.$this->escape ($Address).'\', \''.date ('Y-m-d H:i:s').'\')');
	}
function update_visits_ignored ($IP, $Blocked = 0) {
	$Date = date ('Y-m-d H:i:s');
	ibase_query ($this->Link, 'UPDATE "'.$this->Prefix.'ignored" SET '.($this->query ('SELECT "ip" FROM "'.$this->Prefix.'ignored" WHERE "ip" = \''.$this->escape ($IP).'\' AND "firstvisit" > \''.date ('Y-m-d H:i:s', (time () - 4320)).'\'', 1)?'"views" = "views" + 1, "lastview"':'"unique" = "unique" + 1, "useragent" = \''.$this->escape ($_SERVER['HTTP_USER_AGENT']).'\', "lastvisit"').' = \''.$Date.'\' WHERE "ip" = \''.$this->escape ($IP).'\' AND "type" = '.(int) $Blocked);
	if (!ibase_affected_rows ($this->Link)) ibase_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'ignored" VALUES(\''.$Date.'\', \''.$Date.'\', \''.$Date.'\', \''.$this->escape ($IP).'\', 1, 0, \''.$this->escape ($_SERVER['HTTP_USER_AGENT']).'\', '.(int) $Blocked.')');
	}
function update_info ($ID, $Array) {
	ibase_query ($this->Link, 'UPDATE "'.$this->Prefix.'visitors" SET "javascript" = \''.$Array['javascript'].'\', "cookies" = \''.$Array['cookies'].'\', "flash" = \''.$Array['flash'].'\', "java" = \''.$Array['java'].'\', "screen" = \''.$Array['screen'].'\', "info" = 1 WHERE "id" = '.$ID);
	}
function time_clause ($From = 0, $To = 0) {
	if ($From) return (' WHERE "time" >= \''.date ('Y-m-d H:i:s', $From).'\''.($To?' AND "time" < \''.date ('Y-m-d H:i:s', $To).'\'':''));
	return ('');
	}
function visits_amount ($Type, $From = 0, $To = 0) {
	$Amount = (int) $this->query ('SELECT SUM("'.$Type.'") FROM "'.$this->Prefix.'time"'.$this->time_clause ($From, $To), 1);
	if ($Type == 'returns') return ($Amount);
	else if ($Type == 'unique') $Amount += $this->visits_amount ('returns', $From, $To);
	else if ($Type == 'views') $Amount += $this->visits_amount ('unique', $From, $To);
	return ($Amount);
	}
function visitor_id_exists ($ID) {
	return ($this->query ('SELECT "id" FROM "'.$this->Prefix.'visitors" WHERE "id" = '.$ID, 1));
	}
function visitor_id_get ($IP) {
	return ($this->query ('SELECT FIRST 1 "id" FROM "'.$this->Prefix.'visitors" WHERE "ip" = \''.$this->escape ($IP).'\' AND "firstvisit" > \''.date ('Y-m-d H:i:s', (time () - (int) ($GLOBALS['VisitTime'] / 2))).'\' ORDER BY "id" DESC', 1));
	}
function visitor_id_max () {
	return ($this->query ('SELECT MAX("id") FROM "'.$this->Prefix.'visitors"', 1));
	}
function visitor_add ($ID, $Array) {
	$Date = date ('Y-m-d H:i:s');
	ibase_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'visitors" VALUES('.$ID.', \''.$Date.'\', \''.$Date.'\', 0, \''.$this->escape ($Array['ip']).'\', \''.$this->escape ($Array['useragent']).'\', \''.$this->escape ($Array['host']).'\', \''.$this->escape ($Array['referrer']).'\', \''.$this->escape ($Array['language']).'\', \''.$Array['javascript'].'\', \''.$Array['cookies'].'\', \''.$this->escape ($Array['flash']).'\', \''.$Array['java'].'\', \''.$this->escape ($Array['screen']).'\', \''.$Array['info'].'\', \''.$this->escape ($Array['robot']).'\', \''.$this->escape ($Array['proxy']).'\', \''.$this->escape ($Array['proxyip']).'\', \''.$Array['returned'].'\')');
	}
function visitor_info_exists ($ID) {
	return ((int) $this->query ('SELECT "info" FROM "'.$this->Prefix.'visitors" WHERE "id" = '.$ID, 1));
	}
function backup_create ($Tables, $TablesStructure, $ReplaceData, $SQLFormat) {
	if ($SQLFormat) {
		if ($TablesStructure && !include (ESTATS_PATH.'lib/db/Firebird/schema.php')) return (0);
		$Backup = 'SET TRANSACTION;
';
		}
	else $Backup = '';
	for ($i = 0, $c = count ($Tables); $i < $c; ++$i) {
		$Backup.= '
/*Table: '.$Tables[$i].'*/

';
		if ($SQLFormat) {
			if ($ReplaceData) $Backup.= 'DELETE FROM "'.$this->Prefix.$Tables[$i].'";
	';
			if ($TablesStructure) $Backup.= 'DROP TABLE "'.$this->Prefix.$Tables[$i].'";
	CREATE TABLE "'.$Tables[$i].'" '.$Schema[$Tables[$i]].';
	';
			else {
				$Result = ibase_query ($this->Link, 'SELECT "RDB$FIELD_NAME" FROM "RDB$RELATION_FIELDS" WHERE "RDB$RELATION_NAME" = \''.$this->Prefix.$Tables[$i].'\'');
				$FieldsNames = array ();
				while ($Row = ibase_fetch_row ($Result)) $FieldsNames[] = $Row[0];
				ibase_free_result ($Result);
				}
			}
		$Result = ibase_query ($this->Link, 'SELECT * FROM "'.$this->Prefix.$Tables[$i].'"');
		$Fields = ibase_num_fields ($Result);
		while ($Row = ibase_fetch_row ($Result)) {
		$Values = array ();
		if ($SQLFormat) {
			for ($k = 0; $k < $Fields; $k++) $Values[] = $this->escape ($Array[$j][$k]);
			$Backup.= 'INSERT INTO "'.$this->Prefix.$Tables[$i].'"'.((!$TablesStructure && $SQLFormat)?'':' ("'.implode ('", "', $FieldsNames).'")').' VALUES(\''.implode ('\', \'', $Values).'\');
';
			}
		else {
			for ($k = 0; $k < $Fields; $k++) $Values[] = strtr ($Array[$j][$k], array (
	"\r" => '\r',
	"\n" => '\n',
	chr (30) => ''
	));
			$Backup.= implode (chr (30), $Values).'
';
			}
		}
		ibase_free_result ($Result);
		}
	return ($Backup.($SQLFormat?'COMMIT;':''));
	}
function log ($Log, $Info = '') {
	if (!defined ('ESTATS_CRITICAL')) ibase_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'logs" VALUES(\''.date ('Y-m-d H:i:s').'\', '.(int) $Log.', \''.($Info?$this->escape ($Info):'').'\')');
	}
function config_get ($Mode) {
	$Data = array ();
	$Result = ibase_query ($this->Link, 'SELECT * FROM "'.$this->Prefix.'configuration" WHERE "mode" '.($Mode?'':'!').'= 1');
	while ($Row = ibase_fetch_row ($Result)) $Data[$Row[0]] = $Row[1];
	ibase_free_result ($Result);
	return ($Data);
	}
function config_set ($Array, $Notify = 1) {
	foreach ($Array as $Key => $Value) {
		ibase_query ($this->Link, 'UPDATE "'.$this->Prefix.'configuration" SET "value" = \''.$this->escape ($Value).'\' WHERE "name" = \''.$Key.'\'');
		if (!ibase_affected_rows ($this->Link)) ibase_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'configuration" VALUES(\''.$Key.'\', \''.$this->escape ($Value).'\', 1)');
		}
	e_config_get (0, 1);
	e_config_get (1, 1);
	if ($Notify) e_log (2, 1);
	}
function disconnect () {
	if (!$GLOBALS['PConnect']) ibase_close ($this->Link);
	}
}
?>