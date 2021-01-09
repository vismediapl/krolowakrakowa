<?php
function e_stats_init ($DBType) {
	if (function_exists ('date_default_timezone_set') && $GLOBALS['TimeZone']) date_default_timezone_set ($GLOBALS['TimeZone']);
	if ($DBType) {
		if (!is_readable (ESTATS_PATH.'lib/db/'.$DBType.'/db.ini')) e_error_message ('lib/db/'.$DBType.'/db.ini', __FILE__, __LINE__);
		else $GLOBALS['DBInfo'] = parse_ini_file (ESTATS_PATH.'lib/db/'.$DBType.'/db.ini');
		$GLOBALS['DBInfo']['DBVersion'] = '?';
		}
	$GLOBALS['DBTables'] = array ('browsers', 'cookies', 'details', 'flash', 'geoip', 'hosts', 'ignored', 'java', 'javascript', 'keywords', 'languages', 'oses', 'proxy', 'referrers', 'robots', 'screens', 'sites', 'time', 'visitors', 'websearchers');
	$GLOBALS['TimeStamp'] = array (
	'yearly' => date ('Y-01-01 00:00:00'),
	'monthly' => date ('Y-m-01 00:00:00'),
	'daily' => date ('Y-m-d 00:00:00'),
	'hourly' => date ('Y-m-d H:00:00'),
	'none' => 0
	);
	}
function e_language_detect () {
	if (!isset ($_SERVER['HTTP_ACCEPT_LANGUAGE']) || !$_SERVER['HTTP_ACCEPT_LANGUAGE']) return ('?');
	$String = strtolower ($_SERVER['HTTP_ACCEPT_LANGUAGE']);
	return (substr ($String, 0, (strlen ($String) > 2 && $String[2] == '-')?5:2));
	}
function e_ip_get () {
	if (!isset ($_SERVER['HTTP_VIA'])) return (define ('ESTATS_IP', $_SERVER['REMOTE_ADDR']));
	define ('ESTATS_PROXYIP', $_SERVER['REMOTE_ADDR']);
	define ('ESTATS_PROXY', $_SERVER['HTTP_VIA']);
	define ('ESTATS_IP', $_SERVER[isset ($_SERVER['HTTP_X_FORWARDED_FOR'])?'HTTP_X_FORWARDED_FOR':(isset ($_SERVER['HTTP_X_FORWARDED'])?'HTTP_X_FORWARDED':$_SERVER['HTTP_CLIENT_IP'])]);
	}
function e_ip_check ($IP, $IPs) {
	for ($i = 0, $c = count ($IPs); $i < $c; ++$i) {
		if ($IPs[$i] == $IP || (strstr ($IPs[$i], '*') && substr ($IP, 0, (strlen ($IPs[$i]) - 1)) == substr ($IPs[$i], 0, - 1))) return (1);
		}
	}
function e_data_read ($Path) {
	$FileName = ESTATS_PATH.$GLOBALS['DataDir'].$Path.'_'.$GLOBALS['DBID'].'.dat';
	return (is_file ($FileName)?unserialize (file_get_contents ($FileName)):'');
	}
function e_data_save ($Path, $Data) {
	$FileName = ESTATS_PATH.$GLOBALS['DataDir'].$Path.'_'.$GLOBALS['DBID'].'.dat';
	if (!is_writable ($FileName)) {
		touch ($FileName);
		chmod ($FileName, 0666);
		}
	file_put_contents ($FileName, serialize ($Data));
	}
function e_data_load ($Type) {
	if (!$Data = parse_ini_file (ESTATS_PATH.'share/data/'.$Type.'.ini', 1)) {
		e_error_message ('share/data/'.$Type.'.ini', __FILE__, __LINE__);
		return (0);
		}
	else return ($Data);
	}
function e_visitor () {
	global $DB, $CollectedFrom;
	if (e_cookie_get ('visitor')) {
		if (!isset ($_SESSION['eStats']['visitor'])) $_SESSION['eStats']['visitor'] = e_cookie_get ('visitor');
		if (!isset ($_SESSION['eStats']['visits'])) $_SESSION['eStats']['visits'] = e_cookie_get ('visits');
		}
	if (!isset ($_SESSION['eStats']['visits'])) $_SESSION['eStats']['visits'] = array ();
	if (isset ($_SESSION['eStats']['visitor']) && ((time () - $_SESSION['eStats']['visitor']['time']) > $GLOBALS['VisitTime'] || $_SESSION['eStats']['visitor']['time'] < $CollectedFrom || ($_SESSION['eStats']['visitor'] && !$DB->visitor_id_exists ($_SESSION['eStats']['visitor']['id'])))) unset ($_SESSION['eStats']['visitor']);
	if (!isset ($_SESSION['eStats']['visitor'])) {
		$VisitorID = $DB->visitor_id_get (ESTATS_IP);
		if (!$VisitorID) {
			if (!defined ('ESTATS_COUNT')) return (0);
			$Max = $DB->visitor_id_max ();
			$Uni = $DB->visits_amount ('unique');
			$VisitorID = ((($Max > $Uni)?$Max:$Uni) + 1);
			define ('ESTATS_NEWVISIT', 1);
			if (isset ($_SESSION['eStats']['visits']) && count ($_SESSION['eStats']['visits']) > 1) define ('ESTATS_RETURNED', 1);
			$_SESSION['eStats']['visits'][$VisitorID] = array ('first' => time (), 'last' => time ());
			}
		$_SESSION['eStats']['visitor'] = array ('time' => time (), 'id' => $VisitorID);
		}
	else $_SESSION['eStats']['visits'][$_SESSION['eStats']['visitor']['id']]['last'] = time ();
	define ('ESTATS_VISITORID', $_SESSION['eStats']['visitor']['id']);
	e_cookie_set ('visitor', $_SESSION['eStats']['visitor'], 31356000, '/');
	e_cookie_set ('visits', $_SESSION['eStats']['visits'], 31356000, '/');
	if (defined ('ESTATS_NEWVISIT') || !$DB->visitor_info_exists (ESTATS_VISITORID)) define ('ESTATS_NOINFO', 1);
	}
function e_visit ($Array) {
	global $DB;
	if (defined ('ESTATS_NOINFO') && $Array['info']) $DB->update_info (ESTATS_VISITORID, $Array);
	if (!defined ('ESTATS_COUNT')) return (0);
	if (defined ('ESTATS_NEWVISIT')) $DB->visitor_add (ESTATS_VISITORID, $Array);
	if ($GLOBALS['CollectData']['details']) $DB->update_visit_details (ESTATS_VISITORID, ESTATS_ADDRESS);
	if (ESTATS_ROBOT && !$GLOBALS['CountRobots']) return (0);
	if (defined ('ESTATS_NEWVISIT')) {
		if (defined ('ESTATS_RETURNED')) $Type = 'returns';
		else $Type = 'unique';
		}
	else $Type = 'views';
	if ($GLOBALS['CollectData']['time']) $DB->update_time ($Type);
	}
function e_backup_create ($Profile = 0, $Tables = 0, $TablesStructure = 0, $ReplaceData = 1, $SQLFormat = 0) {
	global $DB, $DBTables, $DBInfo;
	$Error = 0;
	$BackupID = $GLOBALS['CollectedFrom'].'-'.time ().'.'.$Profile.'.'.($SQLFormat?strtolower ($GLOBALS['DBType']).'.sql':'estats');
	switch ($Profile) {
		case 'data':
		$Tables = $DBTables;
		break;
		case 'full':
		$Tables = array_merge ($DBTables, array ('logs', 'configuration'));
		break;
		case 'user':
		$Tables = $GLOBALS['Backups']['usertables'];
		$TablesStructure = $GLOBALS['Backups']['tablesstructure'];
		$ReplaceData = $GLOBALS['Backups']['replacedata'];
		}
	if ($Backup = $DB->backup_create ($Tables, $TablesStructure, $ReplaceData, $SQLFormat)) {
		$FileName = ESTATS_PATH.$GLOBALS['DataDir'].'backups/'.$BackupID.'.bak';
		touch ($FileName) or $Error = 1;
		chmod ($FileName, 0666);
		file_put_contents ($FileName, '/*
eStats v'.ESTATS_VERSION.' database backup
Format: '.($SQLFormat?'SQL':'eStats').'
Mode: '.$Profile.($ReplaceData?' (replace data)':'').'
Time range: '.date ('m.d.Y H:i:s', $GLOBALS['CollectedFrom']).' - '.date ('m.d.Y H:i:s').'
Database: '.$DBInfo['DB'].(($DBInfo['DBVersion'] != '?')?' '.$DBInfo['DBVersion']:'').'
Module: '.$DBInfo['Name'].' v'.$DBInfo['Version'].' ('.$DBInfo['URL'].')
*/

'.$Backup) or $Error = 1;
		}
	else $Error = 1;
	if (!$Error) $DB->config_set (array ('LastBackup' => time ()), 0);
	e_log (($Error?21:20), !$Error, 'ID: '.$BackupID);
	return ($BackupID);
	}
function e_log ($Log, $Type = 2, $Info = '') {
	if ((int) $Type != 2) $GLOBALS['Information'][] = array ($Log, ($Type?'success':'error'));
	if ($GLOBALS['LogEnabled']) $GLOBALS['DB']->log ($Log, $Info);
	if ($GLOBALS['LogFile']) {
		$Logs = e_data_load ('log-codes');
		$FileName = ESTATS_PATH.$GLOBALS['DataDir'].'estats_'.$GLOBALS['DBID'].'.log';
		if (!is_writable ($FileName)) {
			touch ($FileName);
			chmod ($FileName, 0666);
			}
		$FileHandle = fopen ($FileName, 'a');
		fwrite ($FileHandle, '
'.time ().' ('.date ('Y-m-d H:i:s').')'.': '.(isset ($Logs[$Log])?$Logs[$Log]:$Log).($Info?' ('.$Info.')':''));
		fclose ($FileHandle);
		}
	}
function e_config_get ($Mode, $Refresh = 0) {
	global $DataDir;
	$FileName = 'cache/config-'.($Mode?'gui':'stats');
	if ($Refresh || !is_file (ESTATS_PATH.$DataDir.$FileName.'.dat') || (time () - filemtime (ESTATS_PATH.$DataDir.$FileName.'.dat')) > 86400) {
		$Data = array ();
		$Array = $GLOBALS['DB']->config_get ($Mode);
		if (count ($Array) < 2) e_error_message ('Could not retrieve configuration ('.($Mode?'GUI':'stats').')!', __FILE__, __LINE__, 1);
		foreach ($Array as $Key => $Value) {
			$Key = explode ('|', $Key);
			if (in_array ($Key[0], array ('Keywords', 'BlockedIPs', 'IgnoredIPs', 'Referrers')) || (isset ($Key[1]) && $Key[1] == 'usertables')) {
				if (!$Value) $Value = array ();
				else $Value = explode ('|', $Value);
				}
			$l = count ($Key);
			if ($l > 2) $GLOBALS[$Key[0]][$Key[1]][$Key[2]] = $Data[$Key[0]][$Key[1]][$Key[2]] = $Value;
			else if ($l > 1) $GLOBALS[$Key[0]][$Key[1]] = $Data[$Key[0]][$Key[1]] = $Value;
			else $GLOBALS[$Key[0]] = $Data[$Key[0]] = $Value;
			}
		e_data_save ($FileName, $Data);
		}
	else {
		$Data = e_data_read ($FileName);
		foreach ($Data as $Key => $Value) $GLOBALS[$Key] = $Value;
		}
	}
function e_keywords ($String) {
	$Array = explode (' ', $String);
	$Keywords = array ();
	for ($i = 0, $c = count ($Array); $i < $c; ++$i) {
		if (strlen ($Array[$i]) > 1 && $Array[$i][0] != '-' && !in_array ($Array[$i], $GLOBALS['Keywords'])) $Keywords[] = $Array[$i];
		}
	return ($Keywords);
	}
function e_detect ($String, $Data) {
	foreach ($Data as $Key => $Value) {
		$Version = 0;
		if (isset ($Value['rules'])) {
			if (strstr ($Key, '.')) {
				$Version = explode ('.', $Key);
				$Key = $Version[0];
				}
			for ($i = 0, $c = count ($Value['rules']); $i < $c; ++$i) {
				if (($Version && preg_match ('#'.$Value['rules'][$i].'#i', $String)) || preg_match ('#'.$Value['rules'][$i].'#i', $String, $Version)) return (array ($Key, (isset ($Version[1])?$Version[1]:'')));
				}
			}
		else if (stristr ($String, $Key)) return (array ($Key, ''));
		}
	return (0);
	}
function e_robot ($String) {
	static $Data;
	if (!$String) return ('?');
	if (!$Data && !$Data = e_data_load ('robots')) return (0);
	$Result = e_detect ($String, $Data);
	return (is_array ($Result)?$Result[0]:$Result);
	}
function e_browser ($String) {
	static $Data;
	if (!$String) return (array ('?', ''));
	if (!$Data && !$Data = e_data_load ('browsers')) return (0);
	if ($Browser = e_detect ($String, $Data)) return ($Browser);
	return (array ('?', ''));
	}
function e_os ($String) {
	static $Data;
	if (!$String) return (array ('?', ''));
	if (!$Data && !$Data = e_data_load ('oses')) return (0);
	if ($OS = e_detect ($String, $Data)) return ($OS);
	return (array ('?', ''));
	}
function e_websearcher ($Array, $String) {
	static $Data;
	if (!isset ($Array['query'])) return ('');
	if (!$Data && !$Data = e_data_load ('websearchers')) return (0);
	parse_str ($Array['query'], $Query);
	foreach ($Data as $Key => $Value) {
		if (strstr ($Array['host'], $Key)) {
			if (!isset ($Query[$Value])) continue;
			$String = str_replace (array ('"', '\'', '+', '\\'), ' ', $Query[$Value]);
			return (array ('http://'.$Array['host'], ($String?array ($String):e_keywords ($String))));
			}
		}
	return (0);
	}
function e_summary ($From = 0, $To = 0) {
	global $DB;
	$FileName = 'cache/visits'.($From?'-'.$From.($To?'-'.$To:''):'');
	if (e_cache_status ($FileName, $GLOBALS['DBCache']['others']) || (defined ('ESTATS_USERLEVEL') && ESTATS_USERLEVEL)) {
		$Visits = array (
	'unique' => $DB->visits_amount ('unique', $From, $To),
	'views' => $DB->visits_amount ('views', $From, $To),
	'returns' => $DB->visits_amount ('returns', $From, $To),
	'most' => $DB->visits_most ('unique', $From, $To),
	'excluded' => $DB->visits_excluded ($From, $To),
	'lasthour' => $DB->visits_amount ('unique', (($To?$To:time ()) - 3600), $To),
	'last24hours' => $DB->visits_amount ('unique', (($To?$To:time ()) - 86400), $To),
	'lastweek' => $DB->visits_amount ('unique', (($To?$To:time ()) - 604800), $To),
	'lastmonth' => $DB->visits_amount ('unique', (($To?$To:time ()) - (86400 * date ('t', $To))), $To),
	'lastyear' => $DB->visits_amount ('unique', (($To?$To:time ()) - (86400 * (365 + date ('L', $To)))), $To)
	);
		$HoursAmount = ceil ((time () - $GLOBALS['CollectedFrom']) / 3600);
		$DaysAmount = ceil ($HoursAmount / 24);
		$Visits['averageperday'] = ($Visits['unique'] / $DaysAmount);
		$Visits['averageperhour'] = ($Visits['unique'] / $HoursAmount);
		e_data_save ($FileName, $Visits);
		}
	else $Visits = e_data_read ($FileName);
	$Visits['online'] = $DB->visits_online ();
	return ($Visits);
	}
function e_cookie_get ($Key) {
	static $Array;
	$Name = md5 ($GLOBALS['UniqueID'].$Key);
	if (!isset ($_COOKIE[$Name])) return (FALSE);
	if (isset ($Array[$Key])) return ($Array[$Key]);
	$Array[$Key] = unserialize (stripslashes ($_COOKIE[$Name]));
	return ($Array[$Key]);
	}
function e_cookie_set ($Key, $Value, $Time = 31356000, $Path = 0) {
	setcookie (md5 ($GLOBALS['UniqueID'].$Key), serialize ($Value), (time () + $Time), ($Path?$Path:$GLOBALS['Theme']['datapath']), ((substr ($_SERVER['SERVER_NAME'], 0, 4) == 'www.')?substr ($_SERVER['SERVER_NAME'], 0, 4):$_SERVER['SERVER_NAME']));
	}
if (!function_exists ('file_put_contents')) {
function file_put_contents ($Path, $Data) {
	$File = fopen ($Path, 'w');
	if (!fwrite ($File, $Data)) return (0);
	fclose ($File);
	return (1);
	}
}
?>