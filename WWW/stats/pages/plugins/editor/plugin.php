<?php
if (!defined ('eStats')) die ();
if (isset ($_POST['edit'])) $_GET['edit'] = $_POST['filename'];
if (isset ($_POST['delete'])) {
   if (unlink ($_POST['filename'])) $Information[] = array (e_i18n ('File deleted successful.'), 1);
   else $Information[] = array (e_i18n ('An error occured during deleting file!'), 0);
   }
if (isset ($_GET['edit']) && !isset ($_POST['delete'])) {
   $_GET['edit'] = str_replace ('../', '', $_GET['edit']);
   if (!is_file ($_GET['edit'])) {
      if (!touch ($_GET['edit']) || !chmod ($_GET['edit'], 0666)) $Information[] = array (e_i18n ('An error occured during creating file!'), 0);
      else $Information[] = array (e_i18n ('File created successful'), 1);
      }
   }
if (isset ($_POST['save']) && is_writeable ($_POST['filename'])) {
   if (file_put_contents ($_POST['filename'], $_POST['contents'])) $Information[] = array (e_i18n ('File saved successful'), 1);
   else $Information[] = array (e_i18n ('An error occured during saving file!'), 1);
   }
$Files = array (
	'conf/config.php',
	'conf/feed.php',
	'conf/menu.php'
	);
$Files = array_merge ($Files, glob ('share/data/*.ini'));
$Themes = glob ('share/themes/*', GLOB_ONLYDIR);
for ($i = 0, $c = count ($Themes); $i < $c; ++$i) {
    if (is_file ($Themes[$i].'/theme.ini')) $Files[] = $Themes[$i].'/theme.ini';
    }
$Maps = glob ('share/maps/*', GLOB_ONLYDIR);
for ($i = 0, $c = count ($Maps); $i < $c; ++$i) {
    if (is_file ($Maps[$i].'/map.ini')) $Files[] = $Maps[$i].'/map.ini';
    if (is_file ($Maps[$i].'/coordinates.ini')) $Files[] = $Maps[$i].'/coordinates.ini';
    if (is_file ($Maps[$i].'/flags.ini')) $Files[] = $Maps[$i].'/flags.ini';
    }
$Plugins = glob ('pages/plugins/*', GLOB_ONLYDIR);
for ($i = 0, $c = count ($Plugins); $i < $c; ++$i) {
    if (is_file ($Plugins[$i].'/plugin.ini')) $Files[] = $Plugins[$i].'/plugin.ini';
    }
$Theme['page'] = '<h3>'.e_i18n ('Configuration files').'</h3>
';
for ($i = 0, $c = count ($Files); $i < $c; ++$i) $Theme['page'].= '<p>
<a href="{selfpath}{separator}edit='.$Files[$i].'" tabindex="'.(++$TabIndex).'" title="'.e_i18n ('Edit').'"><em>'.$Files[$i].'</em></a>'.(is_writeable ($Files[$i])?'':' (<em class="red">'.e_i18n ('Not writeable!').'</em>)').';
</p>
';
$Theme['page'].= '<h3>'.e_i18n ('Editing of file').''.((isset ($_GET['edit']) && is_file ($_GET['edit']))?': <em>'.$_GET['edit'].'</em>'.(is_writeable ($_GET['edit'])?'':' ('.e_i18n ('read only mode').')'):'').'</h3>
<form action="{selfpath}'.((isset ($_GET['edit']) && is_file ($_GET['edit']))?'?edit='.str_replace ('"', '&#034;', $_GET['edit']):'').'" method="post">
'.e_announce (e_i18n ('Creation of backup is prescribed and very cautious editing of each file is recommended!'), 'warning').'
<p>
<span>
<input name="filename" value="'.((isset ($_GET['edit']) && !isset ($_POST['delete']))?htmlspecialchars ($_GET['edit']):'').'" tabindex="'.(++$TabIndex).'" id="filename" />
<input type="submit" name="edit" value="'.e_i18n ('Edit').'" tabindex="'.(++$TabIndex).'" class="button" />
</span>
<label for="filename">'.e_i18n ('Path to file').'</label>:
</p>
<div>
<textarea rows="50" cols="100" style="height:500px;white-space:nowrap;" name="contents">'.((isset ($_GET['edit']) && is_file ($_GET['edit']))?htmlspecialchars (file_get_contents($_GET['edit'])):'').'</textarea>
</div>
<div class="buttons">
<input type="submit" name="save" value="'.e_i18n ('Save').'" onclick="if (!confirm (\''.e_i18n ('Do You really want to save this file?').'\')) return false" tabindex="'.(++$TabIndex).'" class="button" />
<input type="submit" name="delete" value="'.e_i18n ('Delete').'" onclick="if (!confirm (\''.e_i18n ('Dou You really want to delete this file?').'\')) return false" tabindex="'.(++$TabIndex).'" class="button" />
<input type="reset" value="'.e_i18n ('Reset').'" tabindex="'.(++$TabIndex).'" class="button" />
</div>
</form>
';
?>