<?php
function sqlite_install_prepare ($DB, $Upgrade, $FileHandle) {
	$DBFile = $GLOBALS['DataDir'].'estats_'.$GLOBALS['DBID'].'.sqlite';
	if ($Upgrade) copy ($DBFile, $DBFile.'.bak');
	touch ($DBFile);
	chmod ($DBFile, 0666);
	if (!$DB->connect (0, '', 0)) return (0);
	array_unshift ($GLOBALS['DBTables'], 'logs');
	for ($i = 0, $c = count ($GLOBALS['DBTables']); $i < $c; ++$i) {
		if ($GLOBALS['DBTables'][$i] != 'logs') fwrite ($FileHandle, '
/*Table: '.$GLOBALS['DBTables'][$i].'*/

');
		if ($Upgrade && !in_array ($GLOBALS['DBTables'][$i], array ('geoip', 'proxy'))) {
			if ($GLOBALS['DBTables'][$i] == 'languages') $GLOBALS['DBTables'][$i] = 'langs';
			if ($GLOBALS['DBTables'][$i] == 'time') $GLOBALS['DBTables'][$i] = 'archive';
			$Array = sqlite_array_query ($DB->Link, 'SELECT * FROM "'.$GLOBALS['DBTables'][$i].'"', SQLITE_NUM);
			if (!count ($Array)) continue;
			$Fields = count ($Array[0]);
			for ($j = 0, $Rows = count ($Array); $j < $Rows; ++$j) {
				$Values = array ();
				for ($k = 0; $k < $Fields; $k++) $Values[] = strtr ($Array[$j][$k], array (
	"\r" => '\r',
	"\n" => '\n',
	chr (30) => ''
	));
				switch ($GLOBALS['DBTables'][$i]) {
					case 'details':
					$Values[2] = date ('Y-m-d H:i:s', (int) $Values[2]);
					break;
					case 'visitors':
					if (!$Amount = sqlite_single_query ($DB->Link, 'SELECT COUNT(*) FROM "details" WHERE "uid" = '.$Values[0], 1)) continue;
					$Values = array (
	$Values[0],
	date ('Y-m-d H:i:s', sqlite_single_query ($DB->Link, 'SELECT MIN("time") FROM "details" WHERE "uid" = '.$Values[0], 1)),
	date ('Y-m-d H:i:s', sqlite_single_query ($DB->Link, 'SELECT MAX("time") FROM "details" WHERE "uid" = '.$Values[0], 1)),
	$Amount,
	$Values[1],
	$Values[2],
	$Values[3],
	$Values[4],
	$Values[5],
	$Values[6],
	$Values[7],
	$Values[8],
	$Values[9],
	$Values[10],
	$Values[11],
	$Values[12],
	$Values[13],
	$Values[14],
	''
	);
					break;
					case 'ignored':
					$Values[0] = date ('Y-m-d H:i:s', (int) $Values[0]);
					$Values[1] = date ('Y-m-d H:i:s', (int) $Values[1]);
					$Values[2] = date ('Y-m-d H:i:s', (int) $Values[2]);
					break;
					case 'logs':
					if (isset ($GLOBALS['LogsChanges'][$Values[1]])) {
						if ($GLOBALS['LogsChanges'][$Values[1]] == NULL) continue;
						else $Values[1] = $GLOBALS['LogsChanges'][$Values[1]];
						}
					$Values[0] = date ('Y-m-d H:i:s', (int) $Values[0]);
					break;
					case 'archive':
					$Values = array ($Values[1].'-'.(($Values[2] < 10)?'0':'').$Values[2].'-'.(($Values[3] < 10)?'0':'').$Values[3].' 00:00:00', $Values[4], $Values[3], 0);
					break;
					case 'oses':
					if ($Values[1] == 'MSIE') $Values[1] = 'Internet Explorer';
					case 'browsers':
					if ($Values[1] == 'Sun') $Values[1] = 'Solaris';
					default:
					$Values[0] = date ('Y-m-H 00:00:00', (int) $Values[0]);
					}
				fwrite ($FileHandle, implode (chr (30), $Values).'
');
				}
			}
		}
	return (1);
	}
function sqlite_install_finish ($DB, $Upgrade) {
	if ($Upgrade) {
		$OldTables = array ('archive', 'daysofweekpopularity', 'hours', 'hourspopularity', 'langs');
		for ($i = 0, $c = count ($OldTables); $i < $c; ++$i) sqlite_query ($DB->Link, 'DROP TABLE "'.$OldTables[$i].'"');
		sqlite_query ($DB->Link, 'VACUUM');
		}
	return (1);
	}
$Available = function_exists ('sqlite_query');
$OptionsInstall = array (
	array (),
	array (
		'PConnect' => array (0, 0)
		));
$OptionsUpgrade = &$OptionsInstall;
$OptionsUpgradeUnchanged = array ();
?>