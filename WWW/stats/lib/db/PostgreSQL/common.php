<?php
class estats_db {
var $About;
var $Link;
var $Prefix;
function estats_db ($Connect = 1) {
	$this->About = array (
	'en' => 'This module uses PostgreSQL database (tested on 8.1 version).',
	'pl' => 'Moduł wykorzystuje bazę danych PostgreSQL (testowany na wersji 8.1).',
	);
	if ($Connect && !$this->connect (0, $GLOBALS['DBPrefix'], $GLOBALS['PConnect'], $GLOBALS['DBString'])) e_error_message ('Could not connect to database!', __FILE__, __LINE__, 1);
	}
function connect ($Test, $DBPrefix, $PConnect, $DBString) {
	$Version = '?';
	$ConnectionType = 'pg_'.($PConnect?'p':'').'connect';
	if (function_exists ('pg_query')) {
		if ($this->Link = $ConnectionType ($DBString)) $Version = pg_version ($this->Link);
		else return (0);
		$Version = $Version['server'];
		$this->Prefix = $DBPrefix;
		}
	else {
		if (!$Test) e_error_message ('This module does not supported on this server!', __FILE__, __LINE__, 1);
		return (0);
		}
	$GLOBALS['DBInfo']['DBVersion'] = $Version;
	return (1);
	}
function query ($Query, $String = 0) {
	$Result = pg_query ($this->Link, $Query);
	return ($String?(pg_num_rows ($Result)?pg_fetch_result ($Result, 0, 0):''):pg_fetch_all ($Result));
	}
function update ($Table, $Key) {
	$Result = pg_query ($this->Link, 'UPDATE "'.$this->Prefix.$Table.'" SET "amount" = "amount" + 1 WHERE "name" = \''.pg_escape_string ($Key).'\' AND "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'\'');
	if (!pg_affected_rows ($Result)) pg_query ($this->Link, 'INSERT INTO "'.$this->Prefix.$Table.'" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'\', \''.pg_escape_string ($Key).'\', 1)');
	}
function update_software ($Table, $Array) {
	$Result = pg_query ($this->Link, 'UPDATE "'.$this->Prefix.$Table.'" SET "amount" = "amount" + 1 WHERE "name" = \''.pg_escape_string ($Array[0]).'\' AND "version" = \''.pg_escape_string ($Array[1]).'\' AND "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'\'');
	if (!pg_affected_rows ($Result)) pg_query ($this->Link, 'INSERT INTO "'.$this->Prefix.$Table.'" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency'][$Table]].'\', \''.pg_escape_string ($Array[0]).'\', 1, \''.pg_escape_string ($Array[1]).'\')');
	}
function update_sites ($Array) {
	$Result = pg_query ($this->Link, 'UPDATE "'.$this->Prefix.'sites" SET "amount" = "amount" + 1, "name" = \''.pg_escape_string ($Array[0]).'\' WHERE "address" = \''.pg_escape_string ($Array[1]).'\' AND "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['sites']].'\'');
	if (!pg_affected_rows ($Result)) pg_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'sites" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['sites']].'\', \''.pg_escape_string ($Array[0]).'\', 1, \''.pg_escape_string ($Array[1]).'\')');
	}
function update_geoip ($Array) {
	if (!$Array) return (0);
	$Result = pg_query ($this->Link, 'UPDATE "'.$this->Prefix.'geoip" SET "amount" = "amount" + 1 WHERE "city" = \''.pg_escape_string ($Array['city']).'\' AND "country" = \''.pg_escape_string ($Array['country']).'\' AND "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['geoip']].'\'');
	if (!pg_affected_rows ($Result)) pg_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'geoip" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['geoip']].'\', \''.pg_escape_string ($Array['city']).'\', \''.pg_escape_string ($Array['region']).'\', \''.pg_escape_string ($Array['country']).'\', \''.pg_escape_string ($Array['continent']).'\', \''.pg_escape_string ($Array['latitude']).'\', \''.pg_escape_string ($Array['longitude']).'\', 1)');
	}
function update_time ($Type) {
	$Result = pg_query ($this->Link, 'UPDATE "'.$this->Prefix.'time" SET "'.$Type.'" = "'.$Type.'" + 1 WHERE "time" = \''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['time']].'\'');
	if (!pg_affected_rows ($Result)) pg_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'time" VALUES (\''.$GLOBALS['TimeStamp'][$GLOBALS['CollectFrequency']['time']].'\', '.(int) ($Type == 'views').', '.(int) ($Type == 'unique').', '.(int) ($Type == 'returns').')');
	}
function update_visit_details ($ID, $Address) {
	$Date = date ('Y-m-d H:i:s');
	pg_query ($this->Link, 'UPDATE "'.$this->Prefix.'visitors" SET "lastvisit" = \''.$Date.'\', "visitsamount" = "visitsamount" + 1 WHERE "id" = '.$ID);
	pg_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'details" VALUES('.$ID.', \''.pg_escape_string ($Address).'\', \''.$Date.'\')');
	}
function update_visits_ignored ($IP, $Blocked = 0) {
	$Date = date ('Y-m-d H:i:s');
	$Result = pg_query ($this->Link, 'UPDATE "'.$this->Prefix.'ignored" SET '.($this->query ($this->Link, 'SELECT "ip" FROM "'.$this->Prefix.'ignored" WHERE "ip" = \''.pg_escape_string ($IP).'\' AND (('.time ().' - EXTRACT(EPOCH FROM "firstvisit")) < 4320)', 1)?'"views" = "views" + 1, "lastview"':'"unique" = "unique" + 1, "useragent" = \''.pg_escape_string ($_SERVER['HTTP_USER_AGENT']).'\', "lastvisit"').' = \''.$Date.'\' WHERE "ip" = \''.pg_escape_string ($IP).'\' AND "type" = '.(int) $Blocked);
	if (!pg_affected_rows ($Result)) pg_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'ignored" VALUES(\''.$Date.'\', \''.$Date.'\', \''.$Date.'\', \''.pg_escape_string ($IP).'\', 1, 0, \''.pg_escape_string ($_SERVER['HTTP_USER_AGENT']).'\', '.(int) $Blocked.')');
	}
function update_info ($ID, $Array) {
	pg_query ($this->Link, 'UPDATE "'.$this->Prefix.'visitors" SET "javascript" = '.$Array['javascript'].', "cookies" = '.$Array['cookies'].', "flash" = \''.$Array['flash'].'\', "java" = '.$Array['java'].', "screen" = \''.$Array['screen'].'\', "info" = 1 WHERE "id" = '.$ID);
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
	return ($this->query ('SELECT "id" FROM "'.$this->Prefix.'visitors" WHERE "ip" = \''.pg_escape_string ($IP).'\' AND (('.time ().' - EXTRACT(EPOCH FROM "firstvisit")) < '.(int) ($GLOBALS['VisitTime'] / 2).') ORDER BY "id" DESC LIMIT 1', 1));
	}
function visitor_id_max () {
	return ($this->query ('SELECT MAX("id") FROM "'.$this->Prefix.'visitors"', 1));
	}
function visitor_add ($ID, $Array) {
	$Date = date ('Y-m-d H:i:s');
	pg_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'visitors" VALUES('.$ID.', \''.$Date.'\', \''.$Date.'\', 0, \''.pg_escape_string ($Array['ip']).'\', \''.pg_escape_string ($Array['useragent']).'\', \''.pg_escape_string ($Array['host']).'\', \''.pg_escape_string ($Array['referrer']).'\', \''.pg_escape_string ($Array['language']).'\', \''.$Array['javascript'].'\', \''.$Array['cookies'].'\', \''.pg_escape_string ($Array['flash']).'\', \''.$Array['java'].'\', \''.pg_escape_string ($Array['screen']).'\', \''.$Array['info'].'\', \''.pg_escape_string ($Array['robot']).'\', \''.pg_escape_string ($Array['proxy']).'\', \''.pg_escape_string ($Array['proxyip']).'\', \''.$Array['returned'].'\')');
	}
function visitor_info_exists ($ID) {
	return ($this->query ('SELECT "info" FROM "'.$this->Prefix.'visitors" WHERE "id" = '.$ID, 1));
	}
function backup_create ($Tables, $TablesStructure, $ReplaceData, $SQLFormat) {
	if ($SQLFormat) {
		if ($TablesStructure && !include (ESTATS_PATH.'lib/db/PostgreSQL/schema.php')) return (0);
		$Backup = 'BEGIN WORK;
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
CREATE TABLE "'.$this->Prefix.$Tables[$i].'" '.$Schema[$Tables[$i]].';
LOCK TABLE "'.$this->Prefix.$Tables[$i].'" IN EXCLUSIVE MODE;
';
			}
		$Result = pg_query ($this->Link, 'SELECT * FROM '.$this->Prefix.$Tables[$i]);
		$Fields = pg_num_fields ($Result);
		for ($j = 0, $Rows = pg_num_rows ($Result); $j < $Rows; ++$j) {
			$Row = pg_fetch_row ($Result, $i);
			if (!$j && $SQLFormat && !$TablesStructure) $FieldsNames = array_keys (pg_fetch_assoc ($Result, $i));;
			$Values = array ();
			if ($SQLFormat) {
				for ($k = 0; $k < $Fields; $k++) $Values[] = pg_escape_string ($Row[$k]);
				$Backup.= ($j?',':'INSERT INTO "'.$this->Prefix.$Tables[$i].'"'.((!$TablesStructure && $SQLFormat)?'':' ("'.implode ('", "', $FieldsNames).'")').' VALUES').'(\''.implode ('\', \'', $Values).'\');
		';
				}
			else {
				for ($k = 0; $k < $Fields; $k++) $Values[] = strtr ($Row[$k], array (
		"\r" => '\r',
		"\n" => '\n',
		chr (30) => ''
		));
				$Backup.= implode (chr (30), $Values).'
	';
				}
			}
		pg_free_result ($Result);
		}
	return ($Backup.($SQLFormat?'COMMIT WORK;':''));
	}
function log ($Log, $Info = '') {
	if (!defined ('ESTATS_CRITICAL')) pg_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'logs" VALUES(\''.date ('Y-m-d H:i:s').'\', '.(int) $Log.', \''.($Info?pg_escape_string ($Info):'').'\')');
	}
function config_get ($Mode) {
	$Data = array ();
	$Result = pg_query ($this->Link, 'SELECT * FROM "'.$this->Prefix.'configuration" WHERE "mode" = '.(int) $Mode);
	while ($Row = pg_fetch_row ($Result)) $Data[$Row[0]] = $Row[1];
	pg_free_result ($Result);
	return ($Data);
	}
function config_set ($Array, $Notify = 1) {
	foreach ($Array as $Key => $Value) {
		$Result = pg_query ('UPDATE "'.$this->Prefix.'configuration" SET "value" = \''.pg_escape_string ($Value).'\' WHERE "name" = \''.$Key.'\'');
		if (!pg_affected_rows ($Result)) pg_query ($this->Link, 'INSERT INTO "'.$this->Prefix.'configuration" VALUES(\''.$Key.'\', \''.pg_escape_string ($Value).'\', 1)');
		}
	e_config_get (0, 1);
	e_config_get (1, 1);
	if ($Notify) e_log (2, 1);
	}
function disconnect () {
	if (!$GLOBALS['PConnect']) pg_close ($this->Link);
	}
}
?>