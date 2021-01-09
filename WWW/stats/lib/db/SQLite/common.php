<?php
class estats_db {
var $About;
var $Link;
function estats_db ($Connect = 1) {
	$this->About = array (
	'en' => 'This module uses SQLite database (tested on 2.8 version).',
	'pl' => 'Moduł wykorzystuje bazę danych SQLite (testowany na wersji 2.8).',
	);
	if ($Connect && !$this->connect (0, '', $GLOBALS['PConnect'])) e_error_message ('Could not connect to database!', __FILE__, __LINE__, 1);
	}
function connect ($Test, $DBPrefix, $PConnect) {
	$GLOBALS['DBInfo']['DBVersion'] = (function_exists ('sqlite_libversion')?sqlite_libversion ():'?');
	$ConnectionType = 'sqlite_'.($PConnect?'p':'').'open';
	if (function_exists ('sqlite_query')) {
		if (is_readable (ESTATS_PATH.$GLOBALS['DataDir'].'estats_'.$GLOBALS['DBID'].'.sqlite')) $this->Link = $ConnectionType (ESTATS_PATH.$GLOBALS['DataDir'].'estats_'.$GLOBALS['DBID'].'.sqlite');
		if (!$this->Link) return (0);
		}
	else {
		if (!$Test) e_error_message ('This module does not supported on this server!', __FILE__, __LINE__, 1);
		return (0);
		}
	return (1);
	}
function update ($Table, $Key) {
	sqlite_query ($this->Link, 'UPDATE "'.$Table.'" SET "amount" = "amount" + 1 WHERE "name" = \''.sqlite_escape_string ($Key).'\' AND "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'\'');
	if (!sqlite_changes ($this->Link)) sqlite_query ($this->Link, 'INSERT INTO "'.$Table.'" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'\', \''.sqlite_escape_string ($Key).'\', 1)');
	}
function update_software ($Table, $Array) {
	sqlite_query ($this->Link, 'UPDATE "'.$Table.'" SET "amount" = "amount" + 1 WHERE "name" = \''.sqlite_escape_string ($Array[0]).'\' AND "version" = \''.sqlite_escape_string ($Array[1]).'\' AND "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'\'');
	if (!sqlite_changes ($this->Link)) sqlite_query ($this->Link, 'INSERT INTO "'.$Table.'" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'\', \''.sqlite_escape_string ($Array[0]).'\', 1, \''.sqlite_escape_string ($Array[1]).'\')');
	}
function update_sites ($Array) {
	sqlite_query ($this->Link, 'UPDATE "sites" SET "amount" = "amount" + 1, "name" = \''.sqlite_escape_string ($Array[0]).'\' WHERE "address" = \''.sqlite_escape_string ($Array[1]).'\' AND "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['sites']].'\'');
	if (!sqlite_changes ($this->Link)) sqlite_query ($this->Link, 'INSERT INTO "sites" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['sites']].'\', \''.sqlite_escape_string ($Array[0]).'\', 1, \''.sqlite_escape_string ($Array[1]).'\')');
	}
function update_geoip ($Array) {
	if (!$Array) return (0);
	sqlite_query ($this->Link, 'UPDATE "geoip" SET "amount" = "amount" + 1 WHERE "city" = \''.sqlite_escape_string ($Array['city']).'\' AND "country" = \''.sqlite_escape_string ($Array['country']).'\' AND "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['geoip']].'\'');
	if (!sqlite_changes ($this->Link)) sqlite_query ($this->Link, 'INSERT INTO "geoip" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['geoip']].'\', \''.sqlite_escape_string ($Array['city']).'\', \''.sqlite_escape_string ($Array['region']).'\', \''.sqlite_escape_string ($Array['country']).'\', \''.sqlite_escape_string ($Array['continent']).'\', \''.sqlite_escape_string ($Array['latitude']).'\', \''.sqlite_escape_string ($Array['longitude']).'\', 1)');
	}
function update_time ($Type) {
	sqlite_query ($this->Link, 'UPDATE "time" SET "'.$Type.'" = "'.$Type.'" + 1 WHERE "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['time']].'\'');
	if (!sqlite_changes ($this->Link)) sqlite_query ($this->Link, 'INSERT INTO "time" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['time']].'\', '.(int) ($Type == 'views').', '.(int) ($Type == 'unique').', '.(int) ($Type == 'returns').')');
	}
function update_visit_details ($ID, $Address) {
	$Date = date ('Y-m-d H:i:s');
	sqlite_query ($this->Link, 'UPDATE "visitors" SET "lastvisit" = \''.$Date.'\', "visitsamount" = "visitsamount" + 1 WHERE "id" = '.$ID);
	sqlite_query ($this->Link, 'INSERT INTO "details" VALUES('.$ID.', \''.sqlite_escape_string ($Address).'\', \''.$Date.'\')');
	}
function update_visits_ignored ($IP, $Blocked = 0) {
	$Date = date ('Y-m-d H:i:s');
	sqlite_query ($this->Link, 'UPDATE "ignored" SET '.(sqlite_single_query ($this->Link, 'SELECT "ip" FROM "ignored" WHERE "ip" = \''.sqlite_escape_string ($IP).'\' AND ('.time ().' - PHP("strtotime", "firstvisit") < 4320)', 1)?'"views" = "views" + 1, "lastview"':'"unique" = "unique" + 1, "useragent" = \''.sqlite_escape_string ($_SERVER['HTTP_USER_AGENT']).'\', "lastvisit"').' = \''.$Date.'\' WHERE "ip" = \''.sqlite_escape_string ($IP).'\' AND "type" = '.(int) $Blocked);
	if (!sqlite_changes ($this->Link)) sqlite_query ($this->Link, 'INSERT INTO "ignored" VALUES(\''.$Date.'\', \''.$Date.'\', \''.$Date.'\', \''.sqlite_escape_string ($IP).'\', 1, 0, \''.sqlite_escape_string ($_SERVER['HTTP_USER_AGENT']).'\', '.(int) $Blocked.')');
	}
function update_info ($ID, $Array) {
	sqlite_query ($this->Link, 'UPDATE "visitors" SET "javascript" = '.$Array['javascript'].', "cookies" = '.$Array['cookies'].', "flash" = \''.$Array['flash'].'\', "java" = '.$Array['java'].', "screen" = \''.$Array['screen'].'\', "info" = 1 WHERE "id" = '.$ID);
	}
function time_clause ($From = 0, $To = 0) {
	if ($From) return (' WHERE "time" >= \''.date ('Y-m-d H:i:s', $From).'\''.($To?' AND "time" < \''.date ('Y-m-d H:i:s', $To).'\'':''));
	return ('');
	}
function visits_amount ($Type, $From = 0, $To = 0) {
	$Amount = (int) sqlite_single_query ($this->Link, 'SELECT SUM("'.$Type.'") FROM "time"'.$this->time_clause ($From, $To), 1);
	if ($Type == 'returns') return ($Amount);
	else if ($Type == 'unique') $Amount += $this->visits_amount ('returns', $From, $To);
	else if ($Type == 'views') $Amount += $this->visits_amount ('unique', $From, $To);
	return ($Amount);
	}
function visitor_id_exists ($ID) {
	return (sqlite_single_query ($this->Link, 'SELECT "id" FROM "visitors" WHERE "id" = '.$ID, 1));
	}
function visitor_id_get ($IP) {
	return (sqlite_single_query ($this->Link, 'SELECT "id" FROM "visitors" WHERE "ip" = \''.sqlite_escape_string ($IP).'\' AND ('.time ().' - PHP("strtotime", "firstvisit") < '.(int) ($GLOBALS['VisitTime'] / 2).') ORDER BY "id" DESC LIMIT 1', 1));
	}
function visitor_id_max () {
	return (sqlite_single_query ($this->Link, 'SELECT MAX("id") FROM "visitors"', 1));
	}
function visitor_add ($ID, $Array) {
	$Date = date ('Y-m-d H:i:s');
	sqlite_query ($this->Link, 'INSERT INTO "visitors" VALUES('.$ID.', \''.$Date.'\', \''.$Date.'\', 0, \''.sqlite_escape_string ($Array['ip']).'\', \''.sqlite_escape_string ($Array['useragent']).'\', \''.sqlite_escape_string ($Array['host']).'\', \''.sqlite_escape_string ($Array['referrer']).'\', \''.sqlite_escape_string ($Array['language']).'\', \''.$Array['javascript'].'\', \''.$Array['cookies'].'\', \''.sqlite_escape_string ($Array['flash']).'\', \''.$Array['java'].'\', \''.sqlite_escape_string ($Array['screen']).'\', \''.$Array['info'].'\', \''.sqlite_escape_string ($Array['robot']).'\', \''.sqlite_escape_string ($Array['proxy']).'\', \''.sqlite_escape_string ($Array['proxyip']).'\', \''.$Array['returned'].'\')');
	}
function visitor_info_exists ($ID) {
	return ((int) sqlite_single_query ($this->Link, 'SELECT "info" FROM "visitors" WHERE "id" = '.$ID, 1));
	}
function backup_create ($Tables, $TablesStructure, $ReplaceData, $SQLFormat) {
	if ($SQLFormat) {
		if ($TablesStructure && !include (ESTATS_PATH.'lib/db/SQLite/schema.php')) return (0);
		$Backup = ($TablesStructure?'/*
If You have experienced problems with restoring of this backup You could try to remove lines starting with "DROP TABLE" and try to restore it again.
*/

':'').'BEGIN;
';
		}
	else $Backup = '';
	for ($i = 0, $c = count ($Tables); $i < $c; ++$i) {
		$Backup.= '
/*Table: '.$Tables[$i].'*/

';
		if ($SQLFormat) {
			if ($ReplaceData) $Backup.= 'DELETE FROM "'.$Tables[$i].'";
';
			if ($TablesStructure) $Backup.= 'DROP TABLE "'.$Tables[$i].'";
CREATE TABLE "'.$Tables[$i].'" '.$Schema[$Tables[$i]].';
';
			}
		else $FieldsNames = array_keys (sqlite_fetch_column_types ($Tables[$i], $this->Link));
		$Array = sqlite_array_query ($this->Link, 'SELECT * FROM "'.$Tables[$i].'"', SQLITE_NUM);
		if (!count ($Array)) continue;
		$Fields = count ($Array[0]);
		for ($j = 0, $Rows = count ($Array); $j < $Rows; ++$j) {
			$Values = array ();
			if ($SQLFormat) {
				for ($k = 0; $k < $Fields; $k++) $Values[] = sqlite_escape_string ($Array[$j][$k]);
				$Backup.= 'INSERT INTO "'.$Tables[$i].'"'.((!$TablesStructure && $SQLFormat)?'':' ("'.implode ('", "', $FieldsNames).'")').' VALUES(\''.implode ('\', \'', $Values).'\');
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
		}
	return ($Backup.($SQLFormat?'COMMIT;':''));
	}
function log ($Log, $Info = '') {
	if (!defined ('ESTATS_CRITICAL')) sqlite_query ($this->Link, 'INSERT INTO "logs" VALUES(\''.date ('Y-m-d H:i:s').'\', '.(int) $Log.', \''.($Info?sqlite_escape_string ($Info):'').'\')');
	}
function config_get ($Mode) {
	$Data = array ();
	$Result = sqlite_query ($this->Link, 'SELECT * FROM "configuration" WHERE "mode" '.($Mode?'':'!').'= 1');
	while ($Row = sqlite_fetch_array ($Result, SQLITE_NUM)) $Data[$Row[0]] = $Row[1];
	return ($Data);
	}
function config_set ($Array, $Notify = 1) {
	foreach ($Array as $Key => $Value) {
		sqlite_query ($this->Link, 'UPDATE "configuration" SET "value" = \''.sqlite_escape_string ($Value).'\' WHERE "name" = \''.$Key.'\'');
		if (!sqlite_changes ($this->Link)) sqlite_query ($this->Link, 'INSERT INTO "configuration" VALUES(\''.$Key.'\', \''.sqlite_escape_string ($Value).'\', 1)');
		}
	e_config_get (0, 1);
	e_config_get (1, 1);
	if ($Notify) e_log (2, 1);
	}
function disconnect () {
	if (!$GLOBALS['PConnect']) sqlite_close ($this->Link);
	}
}
?>