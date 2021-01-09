<?php
function firebird_install_prepare ($DB, $Upgrade, $FileHandle) {
	if (!$DB->connect (0, $_POST['DBPrefix'], 0, $_POST['DBAddress'], $_POST['DBUser'], $_POST['DBPass'], 'UTF8')) return (0);
	$GLOBALS['DB'] = &$DB;
	for ($i = 0, $c = count ($GLOBALS['DBTables']); $i < $c; ++$i) fwrite ($FileHandle, '
/*Table: '.$GLOBALS['DBTables'][$i].'*/

');
	return (1);
	}
function firebird_install_finish ($DB, $Upgrade) {
	return (1);
	}
function firebird_connection_test () {
	return ($GLOBALS['DB']->connect (1, $_POST['DBPrefix'], 0, $_POST['DBAddress'], $_POST['DBUser'], $_POST['DBPass'], 'UTF8'));
	}
$Available = function_exists ('ibase_query');
$OptionsInstall = array (
	array (
		'DBAddress' => array ('', 1),
		'DBUser' => array ('', 1),
		'DBPass' => array ('', 2),
		'DBPrefix' => array ('estats_', 1),
		),
	array (
		'PConnect' => array (0, 0)
		)
	);
$OptionsUpgrade = array ();
$OptionsUpgradeUnchanged = array ();
?>