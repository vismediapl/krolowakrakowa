<?php
if (!defined ('eStats')) die ();
if (isset ($_POST['SaveConfig']) || isset ($_POST['Defaults'])) {
   if (isset ($_POST['SaveConfig'])) {
      if ($_POST['Path_Mode'] == 1) {
         $_POST['Path|mode'] = 1;
         $_POST['Path|prefix'] = 'index.php/';
         $_POST['Path|suffix'] = '';
         $_POST['Path|separator'] = '?';
         }
      else if ($_POST['Path_Mode'] == 2) {
              $_POST['Path|mode'] = 0;
              $_POST['Path|prefix'] = '';
              $_POST['Path|suffix'] = '/';
              $_POST['Path|separator'] = '&';
              }
      else {
           $_POST['Path|mode'] = 0;
           $_POST['Path|prefix'] = 'index.php?vars=';
           $_POST['Path|suffix'] = '';
           $_POST['Path|separator'] = '&';
           }
      }
   e_config_set (array ('Pass', 'VisitTime', 'LogEnabled', 'CountPhrases', 'Antipixel', 'DefaultTheme', 'Path|mode', 'Path|prefix', 'Path|suffix', 'Path|separator'));
   }
if (isset ($_POST['ChangePass'])) {
   if (md5 ($_POST['CurrentPass']) == $AdminPass && $_POST['NewPass'] == $_POST['RepeatPass']) {
      e_log (12, 1);
      $_SESSION['eStats']['password'] = md5 ($_POST['NewPass']);
      if (e_cookie_get ('pass')) e_cookie_set ('pass', md5 ($_SESSION['eStats']['password'].$UniqueID), 1209600);
      $DB->config_set (array ('AdminPass' => $_SESSION['eStats']['password']), 0);
      }
   else {
        e_log (13);
        if (md5 ($_POST['CurrentPass']) !== $AdminPass) {
           unset ($_SESSION['eStats']['password']);
           e_cookie_set ('pass', 1, 1);
           die (header ('Location: '.$_SERVER['PHP_SELF']));
           }
        else $Information[] = array (e_i18n ('Given passwords are not the same!'), 0);
        }
   }
$Configuration = array (
	'Pass' => '',
	'VisitTime' => $VisitTime,
	'LogEnabled' => $LogEnabled,
	'CountPhrases' => $CountPhrases
	);
$OptionNames = array (
	'Pass' => 'Password for stats (leave empty, if You allow free access)',
	'VisitTime' => 'Time after that visit is count again (s)',
	'LogEnabled' => 'Log errors and important informations',
	'CountPhrases' => 'Count whole phrases instead of keywords'
	);
$Theme['page'] = '<form action="{selfpath}" method="post">
<h3>
'.e_i18n ('Administrator password').'
</h3>
';
$Keys = array ('Current', 'New', 'Repeat');
for ($i = 0; $i < 3; ++$i) $Theme['page'].= e_config_row (e_i18n ($Keys[$i].' password'), $Keys[$i].'Pass', '', '<input type="password" name="'.$Keys[$i].'Pass" id="'.$Keys[$i].'Pass" tabindex="'.(++$TabIndex).'" />');
$Theme['page'].= '<div class="buttons">
<input type="submit" name="ChangePass" value="'.e_i18n ('Change password').'" tabindex="'.(++$TabIndex).'" class="button" />
</div>
</form>
<form action="{selfpath}" method="post">
<h3>
'.e_i18n ('Settings').'
</h3>
';
$i = 0;
foreach ($Configuration as $Key => $Value) $Theme['page'].= e_config_row (e_i18n ($OptionNames[$Key]).(($Key == 'Pass')?' <strong>['.e_i18n ('Currently '.($Pass?'enabled':'disabled')).']</strong>':''), 'F_'.$Key, '', '<input'.((++$i > 2)?' type="checkbox"'.($Value?' checked="checked"':''):'').' name="'.$Key.'" value="'.htmlspecialchars (($i > 2)?1:$Value).'" id="F_'.$Key.'" tabindex="'.(++$TabIndex).'" />');
$Antipixels = '';
$Dirs = glob ('share/antipixels/*');
for ($i = 0, $c = count ($Dirs); $i < $c; ++$i) {
    if (is_dir ($Dirs[$i])) {
       if ($Num = count ($Images = glob ($Dirs[$i].'/*.{png,gif,jpg}', GLOB_BRACE))) $Antipixels.= '<optgroup label="'.ucfirst (basename ($Dirs[$i])).'">
';
       for ($j = 0; $j < $Num; ++$j) $Antipixels.= '<option value="'.($AID = str_replace ('share/antipixels/', '', dirname ($Images[$j])).'/'.basename ($Images[$j])).'"'.(($Antipixel == $AID)?' selected="selected"':'').'>'.ucfirst (str_replace ('_', ' ', basename ($Images[$j]))).'</option>
';
       if ($Num) $Antipixels.= '</optgroup>
';
       }
    }
$SelectPath = '';
$Path_Modes = array ('GET', 'PATH_INFO', 'Rewrite');
for ($i = 0; $i < 3; ++$i) $SelectPath.= '<option value="'.$i.'"'.(($i == $Path['mode'])?' selected="selected"':'').'>'.$Path_Modes[$i].'</option>
';
$Theme['page'].= e_config_row (e_i18n ('Statistics antipixel'), 'F_Antipixel', 0, '<select name="Antipixel" id="F_Antipixel" onchange="document.getElementById (\'apreview\').src = \'{datapath}share/antipixels/\' + this.options[selectedIndex].value">
'.$Antipixels.'</select>
<img src="{datapath}share/antipixels/'.$Antipixel.'" alt="Preview" id="apreview" />');
$Theme['page'].= e_config_row (e_i18n ('Default theme'), 'F_DefaultTheme', '', '<select name="DefaultTheme" id="F_DefaultTheme">
'.e_themes_list ($DefaultTheme).'</select>').e_config_row (e_i18n ('Mode of passing data in the path'), 'F_Path_Mode', '', '<select name="Path_Mode" id="F_Path_Mode">
'.$SelectPath.'</select>').'<div class="buttons">
'.e_buttons ().'<br />
<input type="button" onclick="location.href=\'{path}admin/advanced{suffix}\'" value="'.e_i18n ('Advanced').'" class="button" />
</div>
</form>
';
?>