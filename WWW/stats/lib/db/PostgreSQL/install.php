<?php
function postgresql_install_prepare ($DB, $Upgrade, $FileHandle) {
	$_POST['DBString'] = postgresql_connection_string ();
	$GLOBALS['Options'] = array (
	0 => array (
		'DBString' => array ($_POST['DBString'], 1),
		'DBPrefix' => array ($_POST['DBPrefix'], 1)
		),
	1 => array (
		'PConnect' => array (isset ($_POST['PConnect']), 0)
		)
	);
	if (!$DB->connect (0, $_POST['DBPrefix'], 0, $_POST['DBString'])) return (0);
	$GLOBALS['DB'] = &$DB;
	array_unshift ($GLOBALS['DBTables'], 'logs');
	for ($i = 0, $c = count ($GLOBALS['DBTables']); $i < $c; ++$i) {
		if ($GLOBALS['DBTables'][$i] != 'logs') fwrite ($FileHandle, '
/*Table: '.$GLOBALS['DBTables'][$i].'*/

');
		if ($Upgrade && !in_array ($GLOBALS['DBTables'][$i], array ('geoip', 'proxy'))) {
			if ($GLOBALS['DBTables'][$i] == 'languages') $GLOBALS['DBTables'][$i] = 'langs';
			if ($GLOBALS['DBTables'][$i] == 'time') $GLOBALS['DBTables'][$i] = 'archive';
			$Result = pg_query ($DB->Link, 'SELECT * FROM '.$DB->Prefix.$Tables[$i]);
			$Fields = pg_num_fields ($Result);
			for ($j = 0, $Rows = pg_num_rows ($Result); $j < $Rows; ++$j) {
				$Row = pg_fetch_row ($Result, $i);
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
					if (!$Values[17]) continue;
					$Values = array (
			$Values[0],
			$Values[15],
			$Values[16],
			$Values[17],
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
			pg_free_result ($Result);
			}
		}
	return (1);
	}
function postgresql_install_finish ($DB, $Upgrade) {
	if ($Upgrade) {
		$OldTables = array ('archive', 'daysofweekpopularity', 'hours', 'hourspopularity', 'langs');
		for ($i = 0, $c = count ($OldTables); $i < $c; ++$i) pg_query ($DB->Link, 'DROP TABLE "'.$DB->Prefix.$OldTables[$i].'"');
		}
	return (1);
	}
function postgresql_connection_string () {
	$String = array ();
	if ($_POST['DBHost'] && $_POST['DBPort']) $String[] = 'host='.$_POST['DBHost'].' port='.$_POST['DBPort'];
	else if ($_POST['DBHost'] && $_POST['DBHost'] != 'localhost') $String[] = 'host='.$_POST['DBHost'];
	if ($_POST['DBName']) $String[] = 'dbname='.$_POST['DBName'];
	if ($_POST['DBUser']) $String[] = 'user='.$_POST['DBUser'];
	if ($_POST['DBPass']) $String[] = 'password='.$_POST['DBPass'];
	return (implode (' ', $String));
	}
function postgresql_connection_test () {
	return ($GLOBALS['DB']->connect (1, $_POST['DBPrefix'], 0, postgresql_connection_string ()));
	}
$Available = function_exists ('pg_query');
$OptionsInstall = array (
	array (
		'DBHost' => array ('localhost', 1),
		'DBUser' => array ('', 1),
		'DBPass' => array ('', 2),
		'DBName' => array ('', 1),
		'DBPrefix' => array ('estats_', 1),
		),
	array (
		'DBPort' => array (5432, 1),
		'PConnect' => array (0, 0)
		)
	);
$OptionsUpgrade = array (
	array (
		'DBString' => array ((isset ($DBString)?$DBString:''), 1)
		),
	array (
		'PConnect' => array ((isset ($PConnect)?$PConnect:0), 0)
		)
	);
$OptionsUpgradeUnchanged = array ('DBString');
?>