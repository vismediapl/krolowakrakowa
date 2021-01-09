<?php
if (!defined ('eStats')) die ();
if (isset ($_POST['SaveConfig']) || isset ($_POST['Defaults'])) e_config_set (array ('Backups|profile', 'Backups|time', 'Backups|usertables', 'Backups|tablesstructure', 'Backups|replacedata', 'Backups|sqlformat'));
if (isset ($_POST['DownloadBackup'])) {
   $BackupInfo = explode ('.', $_POST['BackupID']);
   e_download (file_get_contents ($DataDir.'backups/'.$_POST['BackupID'].'.bak'), 'eStats_'.date ('Y-m-d', (int) $BackupInfo[0]).'_'.date ('Y-m-d').'.'.$BackupInfo[1].'.bak', $_POST['Compress']);
   }
if (isset ($_POST['DeleteBackup'])) {
   $Error = 0;
   unlink ($DataDir.'backups/'.$_POST['BackupID'].'.bak') or $Error = 1;;
   e_log (($Error?23:22), !$Error, 'ID: '.$_POST['BackupID']);
   }
if (isset ($_POST['RestoreBackup'])) {
   e_backup_restore ($_POST['BackupID']);
   e_config_get (0, 1);
   e_config_get (1, 1);
   }
if (isset ($_POST['CreateBackup'])) {
   e_backup_create ((($_POST['Backups|profile'] == 'user')?'manual':$_POST['Backups|profile']), (isset ($_POST['Backups|usertables'])?$_POST['Backups|usertables']:array ()), isset ($_POST['Backups|tablesstructure']), isset ($_POST['Backups|replacedata']), isset ($_POST['Backups|sqlformat']));
   clearstatcache ();
   }
if (isset ($_FILES['UploadBackup']) && is_uploaded_file ($_FILES['UploadBackup']['tmp_name'])) {
   $BackupID = 'Upload-'.time ().'.user';
   move_uploaded_file ($_FILES['UploadBackup']['tmp_name'], $DataDir.'backups/'.$BackupID.'.estats.bak');
   e_backup_restore ($BackupID);
   e_config_get (0, 1);
   e_config_get (1, 1);
   }
$SelectBackups = $SelectProfile = '';
$BackupTypes = array ('full', 'data', 'user');
$BackupTypesNames = array ('Full', 'Only collected data', 'User definied');
for ($i = 0; $i < 3; ++$i) {
    $SelectProfile.= '<option value="'.$BackupTypes[$i].'"'.(($BackupTypes[$i] == $Backups['profile'])?' selected="selected"':'').'>'.e_i18n ($BackupTypesNames[$i]).'</option>
';
    $AvailableBackups = array_reverse (glob ($DataDir.'backups/*.'.$BackupTypes[$i].'.{estats,'.strtolower ($DBType).'.sql}.bak', GLOB_BRACE));
    if ($c = count ($AvailableBackups)) $SelectBackups.= '<optgroup label="'.e_i18n ($BackupTypesNames[$i]).'">
';
    for ($j = 0; $j < $c; ++$j) {
        $BackupTime = explode ('-', basename ($AvailableBackups[$j]));
        $SelectBackups.= '<option value="'.basename ($AvailableBackups[$j], '.bak').'">'.(is_numeric ($BackupTime[0])?date ('d.m.Y H:i:s', (int) $BackupTime[0]):$BackupTime[0]).' - '.date ('d.m.Y H:i:s', (int) $BackupTime[1]).(strstr ($AvailableBackups[$j], 'sql')?' - SQL':'').' ('.e_size (filesize ($AvailableBackups[$j])).')</option>
';
        }
    if ($c) $SelectBackups.= '</optgroup>
';
   }
$SelectUserTables = '';
$DBTables = array_merge ($DBTables, array ('logs', 'configuration'));
sort ($DBTables);
for ($i = 0, $c = count ($DBTables); $i < $c; ++$i) $SelectUserTables.= '<option'.(in_array ($DBTables[$i], $Backups['usertables'])?' selected="selected"':'').'>'.$DBTables[$i].'</option>
';
$Theme['page'] = '<h3>'.e_i18n ('Backups management').'</h3>
<form action="{selfpath}" method="post" enctype="multipart/form-data">
'.e_config_row (e_i18n ('Select backup'), 'BackupID', '', ($SelectBackups?'<select name="BackupID" id="BackupID" tabindex="'.(++$TabIndex).'">
'.$SelectBackups.'</select><br />
<label for="Compress">'.e_i18n ('Compression').'</label>:
<select name="Compress" id="Compress" title="'.e_i18n ('Type of compression of file for download').'">
<option value="">'.e_i18n ('None').'</option>
<option selected="selected">gzip</option>
'.(extension_loaded ('bz2')?'<option>bzip</option>
':'').'</select>
<input type="submit" name="DownloadBackup" value="'.e_i18n ('Download').'" class="button" tabindex="'.(++$TabIndex).'" />
<input type="submit" onclick="if (!confirm (\''.e_i18n ('Do You really want to restore data?').'\')) return false" name="RestoreBackup" value="'.e_i18n ('Restore').'" class="button" tabindex="'.(++$TabIndex).'" />
<input type="submit" onclick="if (!confirm (\''.e_i18n ('Do You really want to delete data?').'\')) return false" name="DeleteBackup" value="'.e_i18n ('Delete').'" class="button" tabindex="'.(++$TabIndex).'" />':'<strong>'.e_i18n ('No backups').'.</strong>')).e_config_row (e_i18n ('Restore backup saved on hard disc'), 'UploadBackup', '', '<input type="file" name="UploadBackup" id="UploadBackup" tabindex="'.(++$TabIndex).'" />
<input type="submit" value="'.e_i18n ('Send').'" tabindex="'.(++$TabIndex).'" class="button" />').'</form>
<h3>'.e_i18n ('Settings').'</h3>
<form action="{selfpath}" method="post">
'.e_config_row (e_i18n ('Backup creating profile'), 'F_profile', '', '<select name="Backups|profile" id="F_profile" tabindex="'.(++$TabIndex).'" onchange="document.getElementById(\'UserDefinied\').style.display = ((this.options[selectedIndex].value == \'user\')?\'block\':\'none\');">
'.$SelectProfile.'</select>').e_config_row (e_i18n ('Create backups after specified time (s)'), 'Backups|time', $Backups['time'], 2).'<div id="UserDefinied">
'.e_config_row (e_i18n ('Tables to archivize (user profile)'), 'F_usertables', '', '<select name="Backups|usertables[]" multiple="multiple" size="3" id="F_usertables" tabindex="'.(++$TabIndex).'">
'.$SelectUserTables.'</select>').e_config_row (e_i18n ('Archivize tables structure (user profile)'), 'Backups|tablesstructure', $Backups['tablesstructure'], 1).e_config_row (e_i18n ('Replace existing data (user profile)'), 'Backups|replacedata', $Backups['replacedata'], 1).e_config_row (e_i18n ('Use SQL format (user profile)'), 'Backups|sqlformat', $Backups['sqlformat'], 1).'</div>
<div class="buttons">
'.e_buttons (1).'<br />
<input type="submit" name="CreateBackup" value="'.e_i18n ('Create backup').'" tabindex="'.(++$TabIndex).'" class="button" />
</div>
</form>
<script type="text/javascript">
var show = '.(int) ($Backups['profile'] == 'user').';
document.getElementById(\'UserDefinied\').style.display = (show?\'block\':\'none\');
</script>
';
?>