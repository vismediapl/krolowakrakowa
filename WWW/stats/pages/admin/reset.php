<?php
if (!defined ('eStats')) die ();
if (isset ($_POST['CreateBackup'])) $BackupID = e_backup_create ('data');
else $BackupID = 0;
if (isset ($_POST['ResetData'])) {
   $DB->table_reset ($DBTables);
   $DB->config_set (array ('CollectedFrom' => time ()), 0);
   e_log (30, 1);
   }
if (isset ($_POST['ResetTables']) && !array_diff ($_POST['Tables'], $DBTables) && !in_array ('configuration', $_POST['Tables']) && !in_array ('logs', $_POST['Tables'])) {
   $DB->table_reset ($_POST['Tables']);
   e_log (31, 1, implode (', ', $_POST['Tables']));
   }
if (isset ($_POST['ResetBackups'])) {
   $Files = glob ($DataDir.'backups/*');
   for ($i = 0, $c = count ($Files); $i < $c; ++$i) {
       if (!$BackupID || basename ($Files[$i], '.bak') != $BackupID) unlink ($Files[$i]);
       }
   e_log (32, 1);
   }
if (isset ($_POST['ResetCache'])) {
   $Files = glob ($DataDir.'cache/*');
   for ($i = 0, $c = count ($Files); $i < $c; ++$i) unlink ($Files[$i]);
   }
$DBSize = $DB->db_size ();
$CacheSize = e_cache_size ();
$BackupsInfo = e_backups_info ();
$ResetOptions = array (
	'Data' => $DBSize,
	'Backups' => $BackupsInfo['size'],
	'Cache' => $CacheSize
	);
$i = 0;
$Theme['page'] = '<form action="{selfpath}" method="post">
';
$OptionNames = array (
	'Data' => 'Delete all statistics data',
	'Backups' => 'Delete backups',
	'Cache' => 'Reset cache'
	);
foreach ($ResetOptions as $Key => $Value) $Theme['page'].= e_config_row (e_i18n ($OptionNames[$Key]).' (<strong>'.(($Key == 'All' && $DBSize == '?')?'>= ':'').e_size ($Value).'</strong>)', 'Reset'.$Key, '', '<input type="checkbox" name="Reset'.$Key.'" id="Reset'.$Key.'" tabindex="'.(++$TabIndex).'" />');
$Tables = '';
for ($i = 0, $c = count ($DBTables); $i < $c; ++$i) {
    if (!in_array ($DBTables[$i], array ('configuration', 'logs'))) $Tables.= '<option>'.$DBTables[$i].'</option>
';
    }
$Theme['page'].= e_config_row (e_i18n ('Reset selected tables'), 'Tables', '', '<select name="Tables" id="Tables" tabindex="'.(++$TabIndex).'" multiple="multiple" size="3">
'.$Tables.'</select>').e_config_row (e_i18n ('Create backup'), 'CreateBackup', 1, 1).'<div class="buttons">
<input type="submit" value="'.e_i18n ('Execute').'" onclick="if (!confirm (\''.e_i18n ('Do You really want to delete data?').'\')) return false" class="button" tabindex="'.(++$TabIndex).'" />
<input type="reset" value="'.e_i18n ('Reset').'" tabindex="'.(++$TabIndex).'" class="button" />
</div>
</form>
';
?>