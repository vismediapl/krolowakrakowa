<?php
function mysql_install_prepare ($DB, $Upgrade, $FileHandle) {
	if (!$DB->connect (0, $_POST['DBPrefix'], 0, $_POST['DBHost'], $_POST['DBUser'], $_POST['DBPass'], $_POST['DBName'])) return (0);
	$GLOBALS['DB'] = &$DB;
	array_unshift ($GLOBALS['DBTables'], 'logs');
	for ($i = 0, $c = count ($GLOBALS['DBTables']); $i < $c; ++$i) {
		if ($GLOBALS['DBTables'][$i] != 'logs') fwrite ($FileHandle, '
/*Table: '.$GLOBALS['DBTables'][$i].'*/

');
		if ($Upgrade && !in_array ($GLOBALS['DBTables'][$i], array ('geoip', 'proxy'))) {
			if ($GLOBALS['DBTables'][$i] == 'languages') $GLOBALS['DBTables'][$i] = 'langs';
			if ($GLOBALS['DBTables'][$i] == 'time') $GLOBALS['DBTables'][$i] = 'archive';
			$Result = $DB->query ('SELECT * FROM '.$DB->Prefix.$GLOBALS['DBTables'][$i]);
			$Fields = (MySQLi?mysqli_num_fields ($Result):mysql_num_fields ($Result));
			while ($Row = $DB->fetch ($Result)) {
				$Values = array ();
				for ($j = 0; $j < $Fields; $j++) $Values[] = strtr ($Row[$j], array (
		"\r" => '\r',
		"\n" => '\n',
		chr (30) => ''
		));
				switch ($GLOBALS['DBTables'][$i]) {
					case 'details':
					$Values[2] = date ('Y-m-d H:i:s', (int) $Values[2]);
					break;
					case 'visitors':
					if (!$Amount = $DB->query ('SELECT COUNT(*) FROM `'.$DB->Prefix.'details` WHERE `uid` = '.$Values[0], 1)) continue;
					$Values = array (
		$Values[0],
		date ('Y-m-d H:i:s', $DB->query ('SELECT MIN(`time`) FROM `'.$DB->Prefix.'details` WHERE `uid` = '.$Values[0], 1)),
		date ('Y-m-d H:i:s', $DB->query ('SELECT MAX(`time`) FROM `'.$DB->Prefix.'details" WHERE `uid` = '.$Values[0], 1)),
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
					$Values = array ($Values[0], $Values[2], $Values[1], 0);
					break;
					case 'oses':
					if ($Values[1] == 'MSIE') $Values[1] = 'Internet Explorer';
					break;
					case 'browsers':
					if ($Values[1] == 'Sun') $Values[1] = 'Solaris';
					}
				fwrite ($FileHandle, implode (chr (30), $Values).'
	');
				}
			$DB->free ($Result);
			}
		}
	return (1);
	}
function mysql_install_finish ($DB, $Upgrade) {
	if ($Upgrade) {
		$OldTables = array ('archive', 'daysofweekpopularity', 'hours', 'hourspopularity', 'langs');
		for ($i = 0, $c = count ($OldTables); $i < $c; ++$i) $DB->query ('DROP TABLE `'.$DB->Prefix.$OldTables[$i].'`');
		}
	return (1);
	}
function mysql_connection_test () {
	return ($GLOBALS['DB']->connect (1, $_POST['DBPrefix'], 0, $_POST['DBHost'], $_POST['DBUser'], $_POST['DBPass'], $_POST['DBName']));
	}
$Available = (function_exists ('mysql_query') || function_exists ('mysqli_query'));
$OptionsInstall = array (
	array (
		'DBHost' => array ('localhost', 1),
		'DBUser' => array ('', 1),
		'DBPass' => array ('', 2),
		'DBName' => array ('', 1),
		'DBPrefix' => array ('estats_', 1)
		),
	array (
		'PConnect' => array (0, 0)
		)
	);
$OptionsUpgrade = array (
	array (
		'DBHost' => array ((isset ($DBHost)?$DBHost:''), 1),
		'DBUser' => array ((isset ($DBUser)?$DBUser:''), 1),
		'DBPass' => array ((isset ($DBPass)?$DBPass:''), 2),
		'DBName' => array ((isset ($DBName)?$DBName:''), 1),
		'DBPrefix' => array ((isset ($DBPrefix)?$DBPrefix:''), 1)
		),
	array (
		'PConnect' => array ((isset ($PConnect)?$PConnect:0), 0)
		)
	);
$OptionsUpgradeUnchanged = array ('DBHost', 'DBUser', 'DBPass' ,'DBName', 'DBPrefix');
?>