<?php
if (!defined ('eStats')) die ();
if (!include ('./conf/template.php')) e_error_message ('conf/template.php', __FILE__, __LINE__);
$GoupNames = array (
	'Stats' => 'Settings requeired for correct data collecting.',
	'Backups' => 'Backups creation system configuration.',
	'CollectData' => 'Selection of groups for thats data is collected.',
	'CollectFrequency' => 'Data collecting frequency.',
	'GUI' => 'User interface behaviour settings.',
	'DBCache' => 'Database cache settings (time in seconds).',
	'Detailed' => 'Detailed statistics configuration.',
	'GroupAmount' => 'Displayed element amounts settings.',
	'Path' => 'Settings of passing variables in address.'
	);
$OptionsNames = array (
	'detailed_amount' => 'Amount of entries per page in Detailed',
	'detailed_compactold' => 'Delete old Visits details',
	'detailed_detailsamount' => 'Amount of entries per page in Visit details',
	'detailed_keepalldata' => 'Do not delete old data',
	'detailed_maxpages' => 'Max amount of pages available for user (0 - all available)',
	'detailed_period' => 'Amount of days form which detailed data are displayed (0 - display all)',
	'backups_profile' => 'Backup creating profile',
	'backups_usertables' => 'Tables to archivize (user profile)',
	'backups_tablesstructure' => 'Archivize tables structure (user profile)',
	'backups_time' => 'Create backups after specified time (s)',
	'backups_replacedata' => 'Replace existing data (user profile)',
	'backups_sqlformat' => 'Use SQL format (user profile)',
	'path_mode' => 'Mode of passing data in the path',
	'path_prefix' => 'Address prefix',
	'path_separator' => 'Separator between address and GET query',
	'path_suffix' => 'Address suffix',
	'dbcache_detailed' => 'Detailed statistics',
	'dbcache_others' => 'Others',
	'dbcache_time' => 'Time statistics',
	'countrobots' => 'Add robots visits to vists',
	'pass' => 'Password for stats (leave empty, if You allow free access)',
	'visittime' => 'Time after that visit is count again (s)',
	'blockedips' => 'Disallow stats viewing for selected IP addresses',
	'ignoredips' => 'Ignored IPs',
	'keywords' => 'Ignored keywords',
	'logfile' => 'Save logs also in text file',
	'referrers' => 'Ignored referrers',
	'blacklistmonitor' => 'Save informations about ignored and blocked visits',
	'logenabled' => 'Log errors and important informations',
	'countphrases' => 'Count whole phrases instead of keywords',
	'antipixel' => 'Statistics antipixel',
	'defaulttheme' => 'Default theme',
	'defaultlang' => 'Default language',
	'gdenabled' => 'Use <em>GD</em> extension (generation of maps and charts), when available',
	'chartstype' => 'Chart type in Time stats',
	'header' => 'Page header syntax',
	'checkversiontime' => 'Time interval between checking for new version availability (0 to disable) (s)',
	'timezone' => 'Time zone',
	'maplink' => 'Link for showing locations on map',
	'whoislink' => 'Link to Whois service'
	);
$ArraySelects['DefaultLanguage'] = $Locales;
$ArraySelects['Antipixel'] = $ArraySelects['DefaultTheme'] = array ();
$Dirs = glob ('share/antipixels/*');
for ($i = 0, $c = count ($Dirs); $i < $c; ++$i) {
    if (is_dir ($Dirs[$i])) {
       $Images = glob ($Dirs[$i].'/*.{png,gif,jpg}', GLOB_BRACE);
       for ($j = 0, $l = count ($Images); $j < $l; ++$j) $ArraySelects['Antipixel'][] = str_replace ('share/antipixels/', '', $Images[$j]);
       }
    }
$Themes = glob ('share/themes/*');
for ($i = 0, $c = count ($Themes); $i < $c; ++$i) {
    if (!is_file ($Themes[$i].'/theme.ini')) continue;
    $ArraySelects['DefaultTheme'][] = basename ($Themes[$i]);
    }
if (isset ($_POST['SaveConfig']) || isset ($_POST['Defaults'])) {
   $ConfigArray = array ();
   $Reset = isset ($_POST['Defaults']);
   foreach ($Array as $Group => $Value) {
           foreach ($Value as $SubGroup => $Option) {
                   if (is_array (reset ($Option))) {
                      foreach ($Option as $Field => $SubOption) $ConfigArray[$SubGroup.'|'.$Field] = ($Reset?$Value[$SubGroup][$Field][0]:(isset ($_POST['F_'.$SubGroup.'_'.$Field])?stripslashes ($_POST['F_'.$SubGroup.'_'.$Field]):0));
                      }
                   else $ConfigArray[$SubGroup] = ($Reset?$Value[$SubGroup][0]:(isset ($_POST['F_'.$SubGroup])?stripslashes ($_POST['F_'.$SubGroup]):0));
                   }
           }
   $DB->config_set ($ConfigArray);
   }
$Theme['page'] = '<div id="advanced">
<noscript>
'.e_announce (e_i18n ('Enabled JavaScript is required for correct work of this tool!'), 'error').'</noscript>
<div id="search">
<span>
<label for="AdvancedSearch">'.e_i18n ('Filter').'</label>:&nbsp;
<input value="'.e_i18n ('Search').'" id="AdvancedSearch" onblur="if (!this.value) this.value = \''.e_i18n ('Search').'\'; if (this.value == \''.e_i18n ('Search').'\') this.style.color = \'gray\';" onfocus="this.style.color = \'black\'; if (this.value == \''.e_i18n ('Search').'\') this.value = \'\'; else search (this.value)" onkeyup="search (this.value)" onkeydown="search (this.value)" tabindex="'.(++$TabIndex).'" />
<input type="button" value="'.e_i18n ('Search').'" onclick="document.getElementById(\'AdvancedSearch\').focus (); search (document.getElementById(\'AdvancedSearch\').value);" tabindex="'.(++$TabIndex).'" class="button" /><br />
'.e_i18n ('Meeting conditions').': <em id="ResultsAmount">{resultsamount}</em>.
</span>
<input type="checkbox" id="ShowAll" onclick="showAll ()" tabindex="'.(++$TabIndex).'" />
<label for="ShowAll">'.e_i18n ('Show all').'</label><br />
<input type="checkbox" id="ShowModified" onclick="showModified ()" tabindex="'.(++$TabIndex).'" />
<label for="ShowModified">'.e_i18n ('Show only modified').'</label>
</div>
<form action="{selfpath}" method="post">
';
$Theme['resultsamount'] = 0;
foreach ($Array as $Group => $Value) {
        $Theme['page'].= '<fieldset class="expanded" id="g_'.$Group.'">
<legend class="parent" onclick="changeClassName (\'g_'.$Group.'\')" title="'.e_i18n ($GoupNames[$Group]).'">'.$Group.'</legend>
<div>
<dfn class="groupdesc">'.e_i18n ($GoupNames[$Group]).'</dfn>
';
        foreach ($Value as $SubGroup => $Option) {
                if (is_array (reset ($Option))) {
                   $Theme['page'].= '<fieldset class="expanded" id="g_'.$Group.'.'.$SubGroup.'">
<legend onclick="changeClassName (\'g_'.$Group.'.'.$SubGroup.'\')" title="'.e_i18n ($GoupNames[$SubGroup]).'">'.$SubGroup.'</legend>
<div>
<dfn class="groupdesc">'.e_i18n ($GoupNames[$SubGroup]).'</dfn>
';
                   foreach ($Option as $Field => $SubOption) {
                           $OptionValue = $$SubGroup;
                           $LanguageString = strtolower ($SubGroup.'_'.$Field);
                           if ($SubOption[1] == 4) $SubOption[2] = $ArraySelects[$SubGroup][$Field];
                           $Theme['page'].= e_option_row ($SubOption, $Field, $SubGroup.'|'.$Field, $OptionValue[$Field], ((in_array ($SubGroup, array ('GroupAmount', 'CollectData', 'CollectFrequency')))?e_i18n (($Field != 'details')?$Titles[$Field]:'Details'):(isset ($OptionsNames[$LanguageString])?e_i18n ($OptionsNames[$LanguageString]):'')));
                           ++$Theme['resultsamount'];
                           }
                   $Theme['page'].= '</div>
</fieldset>
';
                   }
                else {
                     if ($Option[1] == 4) $Option[2] = $ArraySelects[$SubGroup];
                     $LanguageString = strtolower ($SubGroup);
                     $Theme['page'].= e_option_row ($Option, $SubGroup, $SubGroup, $$SubGroup, (isset ($OptionsNames[$LanguageString])?e_i18n ($OptionsNames[$LanguageString]):''));
                     ++$Theme['resultsamount'];
                     }
                }
        $Theme['page'].= '</div>
</fieldset>
';
        }
$Theme['page'].= '<div class="buttons">
'.e_buttons (2).'</div>
</form>
<script type="text/javascript">
// <![CDATA[
document.getElementById(\'AdvancedSearch\').style.color = \'gray\';
ResultsAmount = {resultsamount};
ChangedValueString = \''.e_i18n ('Field value is other than default').'\';
SearchString = \''.e_i18n ('Search').'\';
window.onload = hideAll ();
// ]]>
</script>
</div>
';
?>