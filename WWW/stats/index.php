<?php
//=============eStats v4.9.21=============\\
// Author: Emdek                          \\
// URL: http://estats.emdek.cba.pl        \\
// Licence: GPL                           \\
// Last modified: 2008-09-28 15:36:43 CET \\
//========================================\\

define ('ESTATS_VERSION', '4.9.21');
define ('ESTATS_VERSIONSTATUS', 'stable');
define ('ESTATS_VERSIONTIME', 1222609003);

error_reporting (E_ALL);
$Start = array_sum (explode (' ', microtime ()));
$ERRORS = '';
$ECounter = 0;
$ETypes = array (
	2 => 'Warning',
	8 => 'Notice',
	32 => 'Core warning',
	128 => 'Compile warning',
	512 => 'User warning',
	1024 => 'User notice',
	2048 => 'Strict'
	);
function e_error_handler ($ENo, $EString, $EFile, $ELine) {
	if (strstr ($EString, 'Please use the date.timezone setting')) return;
	$GLOBALS['ERRORS'].= '<h5>
<big>#'.($GLOBALS['ECounter'] + 1).'</big>
'.$GLOBALS['ETypes'][$ENo].' (<em>'.$EFile.':'.$ELine.'</em>)
</h5>
'.$EString.'<br />
';
	++$GLOBALS['ECounter'];
	}
function e_error_message ($Error, $File, $Line, $NotFile = 0, $Warning = 0) {
	if (!$Warning && !defined ('ESTATS_CRITICAL')) define ('ESTATS_CRITICAL', 1);
	$GLOBALS['Information'][] = array (($NotFile?$Error:'Could not load file! (<em>'.$Error.'</em>)').'<br />
<strong>'.$File.': <em>'.$Line.'</em></strong>', ($Warning?'warning':'error'));
	}
set_error_handler ('e_error_handler');
set_magic_quotes_runtime (0);
session_start ();
define ('ESTATS_PATH', './');
$Information = $AccessKeys = $Theme = array ();
$Theme['info'] = $Theme['menu'] = $Theme['page'] = $Theme['css'] = $Theme['hourselect'] = $Theme['dayselect'] = $Theme['monthselect'] = $Theme['yearselect'] = $Meta = $SelectLocale = $SelectTheme = $SelectYears = $SelectMonths = $SelectDays = $SelectHours = '';
$TabIndex = $DB = 0;
$DirName = (is_dir ($_SERVER['SCRIPT_NAME'])?$_SERVER['SCRIPT_NAME']:dirname ($_SERVER['SCRIPT_NAME']));
$Theme['datapath'] = (($DirName == '/')?'':$DirName).'/';
if (is_readable ('./conf/config.php')) include ('./conf/config.php');
if (!defined ('eStats') || !defined ('eStatsVersion') || eStatsVersion !== substr (ESTATS_VERSION, 0, 3)) {
   if (!include ('./conf/default.php')) e_error_message ('conf/default.php', __FILE__, __LINE__);
   else {
        define ('ESTATS_INSTALL', 1);
        if (isset ($IVersion) && $IVersion == '4.5') {
           define ('ESTATS_UPGRADEABLE', 1);
           if ($DBType == 'MySQLi') $DBType = 'MySQL';
           else if ($DBType == 'PGSQL') $DBType = 'PostgreSQL';
           }
        }
   }
if (!include ('./conf/menu.php')) e_error_message ('conf/menu.php', __FILE__, __LINE__);
if (!include ('./lib/common.php')) e_error_message ('lib/common.php', __FILE__, __LINE__);
if (!include ('./lib/gui.php')) e_error_message ('lib/gui.php', __FILE__, __LINE__);
if (is_file ('lib/geoip.php')) include ('./lib/geoip.php');
$Logs = e_data_load ('log-codes');
$LanguageToCountry = e_data_load ('language-to-country');
$Languages = e_data_load ('languages');
$Countries = e_data_load ('countries');
$Countries['no'] = $Countries['\no'];
unset ($Countries['\no']);
if (!defined ('ESTATS_CRITICAL') && (!defined ('ESTATS_INSTALL') || defined ('ESTATS_UPGRADEABLE'))) {
   if (!include ('./lib/db/'.$DBType.'/common.php')) e_error_message ('lib/db/'.$DBType.'/common.php', __FILE__, __LINE__);
   if (!defined ('ESTATS_CRITICAL')) if (!include ('./lib/db/'.$DBType.'/gui.php')) e_error_message ('lib/db/'.$DBType.'/gui.php', __FILE__, __LINE__);
   if (!defined ('ESTATS_CRITICAL')) $DB = new estats_db_gui (1);
   if (!defined ('ESTATS_CRITICAL')) {
      e_ip_get ();
      e_config_get (0);
      e_config_get (1);
      $TimeStamp = e_stats_init ($DBType);
      if (!defined ('ESTATS_CRITICAL') && $Version != ESTATS_VERSION && defined ('ESTATS_INSTALL')) {
         e_log (1, 2, 'From: '.$Version.', to: '.ESTATS_VERSION);
         $DB->config_set (array ('Version' => ESTATS_VERSION), 0);
         }
      if (!defined ('ESTATS_INSTALL')) e_clean ();
      }
   }
if (defined ('ESTATS_CRITICAL') && !include ('./conf/default.php')) e_error_message ('conf/default.php', __FILE__, __LINE__);
if ($Path['mode']) $Vars = (isset ($_SERVER['PATH_INFO'])?explode ('/', substr ($_SERVER[((!$_SERVER['PATH_INFO'] && isset ($_SERVER['ORIG_PATH_INFO']))?'ORIG_':'').'PATH_INFO'], 1)):0);
else $Vars = (isset ($_GET['vars'])?explode ('/', $_GET['vars']):0);
if (!$Vars) $Vars = array ('', 'general');
if (!is_file ('pages/view/'.$Vars[1].'.php') && $Vars[1] != 'admin') $Vars[1] = 'general';
$Blocks = array (
	'general' => array (
		'sites' => 0,
		'referrers' => 0,
		'hosts' => 0,
		'keywords' => 0,
		'languages' => 1
		),
	'geoip' => array (
		'cities' => 1,
		'countries' => 1,
		'continents' => 0
		),
	'technical' => array (
		'browsers' => 1,
		'browsersversions' => 1,
		'oses' => 1,
		'osesversions' => 1,
		'websearchers' => 0,
		'robots' => 1,
		'proxy' => 0,
		'screens' => 1,
		'flash' => 0,
		'java' => 0,
		'javascript' => 0,
		'cookies' => 0
		),
	'time' => array (
		'24hours' => 0,
		'month' => 0,
		'year' => 0,
		'years' => 0,
		'hours' => 0,
		'weekdays' => 0
		)
	);
if (isset ($_POST['year'])) {
   $Date = (int) $_POST['year'].'-'.(int) $_POST['month'].'-'.(isset ($_POST['day'])?(int) $_POST['day']:0).'-'.(isset ($_POST['hour'])?(int) $_POST['hour']:0);
   if ($Vars[1] != 'time') e_cookie_set ('date', $Date);
   die (header ('Location: '.$Theme['datapath'].$Path['prefix'].$Vars[0].'/'.$Vars[1].(($Vars[1] == 'geoip')?'/'.$_POST['map']:'').(($Vars[1] != 'time' && isset ($Vars[($Vars[1] == 'geoip')?5:4]))?'/'.$Vars[($Vars[1] == 'geoip')?5:4]:'').(($Vars[1] == 'time')?((isset ($Vars[2]) && isset ($Blocks['time'][$Vars[2]]))?'/'.$Vars[2]:'').((isset ($_POST['TimeView']))?'/'.implode ('+', $_POST['TimeView']):''):'').'/'.$Date.$Path['suffix']));
   }
if (!defined ('ESTATS_INSTALL') && (!isset ($Vars[1]) || !$Vars[1] || $Vars[1] == 'install')) $Vars[1] = 'general';
if (defined ('ESTATS_INSTALL')) $Vars[1] = 'install';
if (e_cookie_get ('theme')) $_SESSION['eStats']['theme'] = e_cookie_get ('theme');
if (isset ($_GET['theme'])) $_SESSION['eStats']['theme'] = $_GET['theme'];
if (isset ($_POST['theme'])) {
   $_SESSION['eStats']['theme'] = $_POST['theme'];
   e_cookie_set ('theme', $_POST['theme']);
   }
if (isset ($_POST['language'])) die (header ('Location: '.$Theme['datapath'].$Path['prefix'].$_POST['language'].'/'.implode ('/', array_slice ($Vars, 1)).$Path['suffix']));
if (isset ($_GET['logout']) && !defined ('ESTATS_INSTALL')) {
   unset ($_SESSION['eStats']['password']);
   e_cookie_set ('pass', 1, 1);
   die (header ('Location: '.$_SERVER['PHP_SELF']));
   }
if ((!isset ($_SESSION['eStats']['password']) || !$_SESSION['eStats']['password']) && e_cookie_get ('pass') && !defined ('ESTATS_INSTALL')) {
   if (e_cookie_get ('pass') == md5 ($Pass.$UniqueID)) $_SESSION['eStats']['password'] = $Pass;
   if (e_cookie_get ('pass') == md5 ($AdminPass.$UniqueID)) {
      if (!isset ($_SESSION['eStats']['password']) || $_SESSION['eStats']['password'] != $AdminPass) e_log (10, 2, 'IP: '.ESTATS_IP);
      $_SESSION['eStats']['password'] = $AdminPass;
      }
   if (isset ($_SESSION['eStats']['password'])) e_cookie_set ('pass', md5 ($_SESSION['eStats']['password'].$UniqueID), 1209600);
   }
if (isset ($_POST['Password']) && !defined ('ESTATS_INSTALL')) {
   $_SESSION['eStats']['password'] = md5 ($_POST['Password']);
   if (isset ($_POST['Remember'])) e_cookie_set ('pass', md5 ($_SESSION['eStats']['password'].$UniqueID), 1209600);
   }
define ('ESTATS_USERLEVEL', (isset ($_SESSION['eStats']['password'])?($_SESSION['eStats']['password'] == $AdminPass && $AdminBoard)?2:(($_SESSION['eStats']['password'] == $Pass)?1:0):0));
define ('ESTATS_LOGIN', (!defined ('ESTATS_INSTALL') && (($Vars[1] == 'admin' && ESTATS_USERLEVEL < 2) || ($Pass && !ESTATS_USERLEVEL))));
$ThemeSwitch = array (
	'loggedin' => (ESTATS_USERLEVEL && !defined ('ESTATS_INSTALL')),
	'user' => (ESTATS_USERLEVEL == 1),
	'admin' => (ESTATS_USERLEVEL == 2),
	'adminpage' => ($Vars[1] == 'admin'),
	'loginpage' => ESTATS_LOGIN
	);
if ($Vars[1] == 'admin' && isset ($Vars[2]) && $Vars[2] == 'phpinfo' && ESTATS_USERLEVEL == 2) die (phpinfo ());
if (!isset ($_SESSION['eStats']['theme']) || !is_file ('share/themes/'.$_SESSION['eStats']['theme'].'/theme.tpl')) {
   $Browser = e_browser ($_SERVER['HTTP_USER_AGENT']);
   if ((in_array ($Browser[0], array ('Dillo', 'OffByOne', 'Links', 'ELinks', 'Lynx', 'W3M')) || ($Browser[0] == 'Internet Explorer' && ((double) $Browser[1]) < 6)) && is_file ('share/themes/Simple/theme.tpl')) $_SESSION['eStats']['theme'] = 'Simple';
   else $_SESSION['eStats']['theme'] = $DefaultTheme;
   }
$ThemeConfig = parse_ini_file ('share/themes/'.$_SESSION['eStats']['theme'].'/theme.ini');
$Locales = glob ('locale/*', GLOB_ONLYDIR);
for ($i = 0, $c = count ($Locales); $i < $c; ++$i) $Locales[$i] = basename ($Locales[$i]);
if (!isset ($_SESSION['eStats']['language'])) {
   $Language = (function_exists ('e_language_detect')?e_language_detect ():$DefaultLanguage);
   $_SESSION['eStats']['userlanguage'] = $_SESSION['eStats']['language'] = (in_array ($Language, $Locales)?$Language:(in_array (substr ($Language, 0, 2), $Locales)?substr ($Language, 0, 2):$DefaultLanguage));
   }
if (!isset ($Vars[0]) || !is_file ('locale/'.$Vars[0].'/locale.ini')) $Vars[0] = $_SESSION['eStats']['language'];
if (!is_readable ('locale/'.$Vars[0].'/locale.ini')) $Vars[0] = $DefaultLanguage;
for ($i = 0; $i < $c; ++$i) {
    $TmpArray = parse_ini_file ('locale/'.$Locales[$i].'/locale.ini');
    $LanguageNames[$Locales[$i]] = $TmpArray['Name'];
    $LocaleInfo[$Locales[$i]] = $TmpArray['Information'];
    $LanguageStatus[$Locales[$i]] = $TmpArray['Status'];
    if ($Locales[$i] == $Vars[0]) {
       $Theme['dir'] = $TmpArray['Dir'];
       $WeekStartDay = $TmpArray['WeekStartDay'];
       $Locale = explode ('|', $TmpArray['Locale']);
       }
    $SelectLocale.= '<option value="'.$Locales[$i].'"'.(($Locales[$i] == $Vars[0])?' selected="selected"':'').'>'.$LanguageNames[$Locales[$i]].'</option>
';
    }
setlocale (LC_ALL, $Locale);
putenv ('LANG='.$Vars[0]);
putenv ('LANGUAGE='.$Vars[0]);
e_locale_load ($Vars[0]);
$_SESSION['eStats']['language'] = $Vars[0];
$_SESSION['eStats']['locale'] = $Locale;
if ($_SESSION['eStats']['userlanguage'] != $Vars[0]) $Information[] = array ('<a href="'.$Theme['datapath'].$Path['prefix'].$_SESSION['eStats']['language'].'/'.implode ('/', array_slice ($Vars, 1)).$Path['suffix'].'" tabindex="'.(++$TabIndex).'">'.$LocaleInfo[$_SESSION['eStats']['language']].'</a>.', 'information');
if (!$LanguageStatus[$Vars[0]]) $Information[] = array (sprintf (e_i18n ('This translation (%s) is not complete!'), $LanguageNames[$Vars[0]]), 'warning');
if (!isset ($_SESSION['eStats']['viewtime'])) $_SESSION['eStats']['viewtime'] = 0;
if (!defined ('ESTATS_INSTALL')) {
   if (!$Pass) {
      $Modes = array ('daily+weekly+monthly', 'daily+monthly', 'daily', 'weekly', 'monthly');
      for ($i = 0, $c = count ($Modes); $i < $c; ++$i) $Meta.= '<link rel="alternate" type="application/atom+xml" href="{path}feed/'.$Modes[$i].'{suffix}" title="'.e_i18n ('Summary').' ('.$Modes[$i].')" />
';
      }
   if (ESTATS_USERLEVEL != 2 && $Maintenance) {
      $Information[] = array (e_i18n ('Site unavailable by the reason of maintenance.'), 'information');
      $Theme['title'] = e_i18n ('Maintenance');
      }
   else if (defined ('ESTATS_IP') && e_ip_check (ESTATS_IP, $BlockedIPs)) {
           $Information[] = array (e_i18n ('This IP address was blocked!'), 'error');
           $DB->update_visits_ignored (ESTATS_IP, 1);
           $Theme['title'] = e_i18n ('Access denied');
           }
   else if ((time () - $_SESSION['eStats']['viewtime']) < 2 && !ESTATS_USERLEVEL && $Vars[1] != 'image') {
           $Meta.= '<meta http-equiv="Refresh" content="2" />
';
           $Information[] = array (e_i18n ('You could not refresh page so quickly!'), 'error');
           $Theme['title'] = e_i18n ('Access denied');
           }
   else if (!ESTATS_LOGIN) define ('ESTATS_LOADPAGE', 1);
   if (is_file ('install/index.php') && ESTATS_USERLEVEL == 2 && !defined ('ESTATS_CRITICAL')) $Information[] = array (e_i18n ('Directory <em>install/</em> should be removed after installation!'), 'warning');
   }
$SelectTheme = e_themes_list ($_SESSION['eStats']['theme']);
$ThemeSwitch['loadpage'] = defined ('ESTATS_LOADPAGE');
$ThemeSwitch['selectform'] = (count ($Locales) > 1 || $SelectTheme);
$ThemeSwitch['antyflood'] = ((time () - $_SESSION['eStats']['viewtime']) < 2 && !ESTATS_USERLEVEL);
$_SESSION['eStats']['viewtime'] = time ();
if (function_exists ('e_theme_load')) e_theme_load ('common');
if (!defined ('ESTATS_INSTALL')) {$Theme['startdate'] = date ('d.m.Y', $CollectedFrom);
   $Theme['header'] = preg_replace ('#(\{tabindex\})#e', '++$TabIndex', $Header);
   $Theme['starttime'] = date ('H:i:s', $CollectedFrom);
   $Theme['servername'] = $_SERVER['SERVER_NAME'];
   $Theme['lang_statsfor'] = e_i18n ('Statistics for');
   $Theme['lang_logout'] = e_i18n ('Log out');
   $Theme['lang_collectedfrom'] = e_i18n ('Data collected from');
   }
$Theme['lang_gototop'] = e_i18n ('Go to top');
$Theme['lang_change'] = e_i18n ('Change');
$Theme['language'] = $Vars[0];
$Theme['meta'] = $Meta;
$Theme['theme'] = $_SESSION['eStats']['theme'];
$Theme['languageselect'] = ((count ($Locales) > 1)?'<select name="lang" title="'.e_i18n ('Choose language').'" tabindex="'.(++$TabIndex).'">
'.$SelectLocale.'</select>
':'');
$Theme['themeselect'] = ((count ($Themes) > 2)?'<select name="theme" title="'.e_i18n ('Choose theme').'" tabindex="'.(++$TabIndex).'">
'.$SelectTheme.'</select>
':'');
$Theme['selectformindex'] = ($ThemeSwitch['selectform']?++$TabIndex:'');
$Theme['logoutlink'] = 'href="{selfpath}{separator}logout" tabindex="'.(++$TabIndex).'"';
$Theme['path'] = $Theme['datapath'].$Path['prefix'].$Vars[0].'/';
$Theme['suffix'] = $Path['suffix'];
if ($Vars[1] == 'admin' && (!isset ($Vars[2]) || !is_file ('pages/admin/'.$Vars[2].'.php'))) $Vars[2] = 'main';
if (ESTATS_VERSIONSTATUS != 'stable' && !defined ('ESTATS_CRITICAL')) $Information[] = array (sprintf (e_i18n ('This is a test version of <em>eStats</em> (status: <em>%s</em>).<br />
Its functionality could be incomplete, could work incorrect and be incompatible with newest versions!<br />
<strong style="text-decoration:underline;">Use at own risk!</strong>'), ESTATS_VERSIONSTATUS), 'warning');
if ((ESTATS_USERLEVEL == 2 || defined ('ESTATS_INSTALL')) && ini_get ('safe_mode') && !defined ('ESTATS_CRITICAL')) $Information[] = array (e_i18n ('<em>PHP safe mode</em> has been activated on this server!<br />
That could cause problems in case of automatic creation of files and directories.<br />
Solution is change of their owner or manual creation.'), 'warning');
if (ESTATS_USERLEVEL == 2 || defined ('ESTATS_INSTALL')) {
   if (!include ('./lib/admin.php')) e_error_message ('lib/admin.php', __FILE__, __LINE__);
   }
if (ESTATS_USERLEVEL == 2) {
   if ($_GET) {
      $Array = array (
	'keyword' => 'Keywords',
	'referrer' => 'Referrers',
	'ignoredIP' => 'IgnoredIPs',
	'blockedIP' => 'IgnoredIPs'
	);
      foreach ($Array as $Key => $Value) {
              if (isset ($_GET[$Key])) {
                 $TmpArray = $$Value;
                 if (in_array ($_GET[$Key], $TmpArray)) unset ($TmpArray[array_search ($_GET[$Key], $TmpArray)]);
                 else {
                      $TmpArray[] = $_GET[$Key];
                      if ($Key == 'keyword' || $Key == 'referrer') $DB->table_row_delete ($Key.'s', urldecode ($_GET[$Key]));
                      }
                 $DB->config_set (array ($Value => implode ('|', $TmpArray)));
                 e_config_get (0);
                 }
              }
      }
   if (isset ($_GET['statsenabled']) || isset ($_POST['statsenabled'])) $DB->config_set (array ('StatsEnabled' => (int) !$StatsEnabled), 0);
   if (isset ($_GET['maintenance']) || isset ($_POST['maintenance'])) $DB->config_set (array ('Maintenance' => (int) !$Maintenance), 0);
   if (isset ($_GET['editmode']) || isset ($_POST['editmode'])) $_SESSION['eStats']['editmode'] = !$_SESSION['eStats']['editmode'];
   if (!isset ($_SESSION['eStats']['editmode'])) $_SESSION['eStats']['editmode'] = $EditMode;
   define ('ESTATS_EDITMODE', $_SESSION['eStats']['editmode']);
   }
else define ('ESTATS_EDITMODE', 0);
if (ESTATS_USERLEVEL == 2 || defined ('ESTATS_INSTALL')) {
   if (($CheckVersionTime || (defined ('ESTATS_INSTALL') && !isset ($_POST))) && (!isset ($_SESSION['eStats']['latestversion']) || (time () - $_SESSION['eStats']['latestversion'][1]) > $CheckVersionTime)) {
      if ($File = fopen ('http://estats.emdek.cba.pl/current.php?'.$_SERVER['SERVER_NAME'].'---'.$_SERVER['SCRIPT_NAME'].'---'.ESTATS_VERSION, 'r')) $_SESSION['eStats']['latestversion'] = array (fread ($File, 6), time ());
      else $Information[] = array (e_i18n ('Could not check for new version availability!'), 'error');
      $NewVersion = ((isset ($_SESSION['eStats']['latestversion']) && str_replace ('.', '', $_SESSION['eStats']['latestversion'][0]) > str_replace ('.', '', ESTATS_VERSION))?$_SESSION['eStats']['latestversion'][0]:0);
      }
   else $NewVersion = '';
   if ($NewVersion) $Information[] = array (sprintf (e_i18n ('New version is available (%s)!'), $_SESSION['eStats']['latestversion'][0]), 'information');
   }
if ($Maintenance && ESTATS_USERLEVEL == 2) $Information[] = array (''.e_i18n ('Maintenance mode is active!').'<br />
<a href="{selfpath}{separator}maintenance" tabindex="'.(++$TabIndex).'"><strong>'.e_i18n ('Disable maintenance mode').'</strong></a>.', 'warning');
if (!defined ('ESTATS_INSTALL') && !defined ('ESTATS_CRITICAL')) {
   $Var = $SubMenuVar = 0;
   switch ($Vars[1]) {
          case 'admin':
          $SubMenuVar = 2;
          break;
          case 'general':
          case 'technical':
          if (isset ($Vars[2]) && isset ($Blocks[$Vars[1]][$Vars[2]])) {
             $Var = 3;
             $SubMenuVar = 2;
             }
          else $Var = 2;
          break;
          case 'geoip':
          $FileName = 'cache/geoip-countries-list';
          if (e_cache_status ($FileName, 3600) || ESTATS_USERLEVEL == 2) {
             $AvailableCountries = $DB->data_countries ();
             e_data_save ($FileName, $AvailableCountries);
             }
          else $AvailableCountries = e_data_read ($FileName);
          if (isset ($Vars[2]) && ($Vars[2] == 'world' || in_array ($Vars[2], $AvailableCountries))) {
             $Var = 3;
             $SubMenuVar = 2;
             if (isset ($Vars[3]) && ((in_array ($Vars[3], array ('cities', 'regions')) && in_array ($Vars[2], $AvailableCountries)) || (in_array ($Vars[3], array ('cities', 'countries', 'continents')) && $Vars[2] == 'world'))) {
                $Var = 4;
                $SubMenuVar = 3;
                }
             }
          else {
               $Var = 3;
               $SubMenuVar = 2;
               if (!isset ($Vars[2]) || !in_array ($Vars[2], array ('countries', 'continents'))) $Vars[2] = 'countries';
               }
          break;
          case 'time':
          $Var = 3;
          $SubMenuVar = 2;
          if (isset ($Vars[2]) && isset ($Blocks['time'][$Vars[2]])) $Var = 4;
          break;
          }
   $Types = array ();
   $Available = array ('views', 'unique', 'returns');
   if (e_cookie_get ('timeview') && in_array ($Vars[1], array ('general', 'technical', 'time'))) $Vars[$Var] = e_cookie_get ('timeview');
   if (isset ($_POST['TimeView'])) {
      $Vars[$Var - 1] = implode ('+', $_POST['TimeView']);
      e_cookie_set ('timeview', $Vars[$Var - 1]);
      }
   if (isset ($Vars[$Var - 1]) && $Vars[$Var - 1]) {
      $TempTypes = explode ('+', $Vars[$Var - 1]);
      for ($i = 0, $c = count ($TempTypes); $i < $c; ++$i) {
          if (in_array ($TempTypes[$i], $Available) && !in_array ($TempTypes[$i], $Types)) $Types[] = $TempTypes[$i];
          }
      }
   if (!$Types) $Types = array ('views', 'unique', 'returns');
   sort ($Types);
   $TimeView = implode ('+', $Types);
   if ($Vars[1] == 'time') $Vars[$Var - 1] = $TimeView;
   if (!isset ($Vars[$Var]) || !$Vars[$Var]) {
      if ($Vars[1] == 'time') $Date = array (0, 0, 0, 0);
      else {
           if (e_cookie_get ('date')) $Date = explode ('-', e_cookie_get ('date'));
           else if (date ('n') > 3) $Date = array (date ('Y'), 0, 0, 0);
           else $Date = array (0, 0, 0, 0);
           }
      }
   else $Date = explode ('-', $Vars[$Var]);
   if ($Vars[1] != 'detailed' && $Vars[1] != 'details' && $Vars[1] != 'admin') {
      $Weights = array (
	'none' => 0,
	'yearly' => 1,
	'monthly' => 2,
	'daily' => 3,
	'hourly' => 4
	);
      if ($Vars[1] != 'time' && $Vars[1] != 'geoip' && $Vars[1] != 'feed' && $Vars[1] != 'image') {
         $Frequency = 4;
         if (isset ($Vars[2]) && isset ($Blocks[$Vars[1]][$Vars[2]])) {
            if (substr ($Vars[2], -8) == 'versions') $Frequency = $Weights[$CollectFrequency[substr ($Vars[2], 0, -8)]];
            else $Frequency = $Weights[$CollectFrequency[$Vars[2]]];
            }
         else {
              foreach ($Blocks[$Vars[1]] as $Key => $Value) {
                      if (substr ($Key, -8) == 'versions') $Key = substr ($Key, 0, -8);
                      if ($Weights[$CollectFrequency[$Key]] < $Frequency) $Frequency = $Weights[$CollectFrequency[$Key]];
                      }
              }
         }
      else if ($Vars[1] == 'geoip') $Frequency = $Weights[$CollectFrequency['geoip']];
      else $Frequency = $Weights[($CollectFrequency['time'] == 'hourly')?'daily':$CollectFrequency['time']];
      if (!$Date[0] || !in_array ($Date[0], range (date ('Y', $CollectedFrom), date ('Y')))) $Date = array_fill (0, 4, 0);
      else if (!$Date[1] || $Date[0].(($Date[1] < 10)?'0':'').$Date[1] < date ('Ym', $CollectedFrom) || $Date[0].(($Date[1] < 10)?'0':'').$Date[1] > date ('Ym')) $Date[1] = $Date[2] = 0;
      else if (!$Date[2] || $Date[2] < 1 || $Date[2] > date ('t', strtotime ($Date[0].'-'.(($Date[1] < 10)?'0':'').$Date[1].'-01'))) $Date[2] = 0;
      else if (!$Date[3] || $Date[3] < 0 || $Date[3] > 23) $Date[3] = 0;
      switch ($Frequency) {
             case 4:
             for ($Hour = 0; $Hour < 24; $Hour++) $SelectHours.= '<option'.(((int) $Date[3] == $Hour && $Date[2])?' selected="selected"':'').'>'.$Hour.'</option>
';
             $Theme['hourselect'] = '<select name="hour" id="hour" title="'.e_i18n ('Hour').'" tabindex="'.(++$TabIndex).'">
<option'.(($Date[3] && $Date[2])?'':' selected="selected"').' value="0">'.e_i18n ('All').'</option>
'.$SelectHours.'</select>
';
             case 3:
             for ($Day = 1; $Day <= 31; $Day++) $SelectDays.= '<option'.(((int) $Date[2] == $Day)?' selected="selected"':'').'>'.$Day.'</option>
';
             $Theme['dayselect'] = '<select name="day" id="day" title="'.e_i18n ('Day').'" tabindex="'.(++$TabIndex).'">
<option'.($Date[2]?'':' selected="selected"').' value="0">'.e_i18n ('All').'</option>
'.$SelectDays.'</select>
';
             case 2:
             for ($Month = 1; $Month <= 12; $Month++) $SelectMonths.= '<option value="'.$Month.'"'.(((int) $Date[1] == $Month)?' selected="selected"':'').'>'.ucfirst (strftime ('%B', (mktime (0, 0, 0, $Month)))).'</option>
';
             $Theme['monthselect'] = '<select name="month" id="month" title="'.e_i18n ('Month').'" tabindex="'.(++$TabIndex).'">
<option'.($Date[1]?'':' selected="selected"').' value="0">'.e_i18n ('All').'</option>
'.$SelectMonths.'</select>
';
             case 1:
             for ($Year = date ('Y', $CollectedFrom); $Year <= date ('Y'); $Year++) $SelectYears.= '<option value="'.$Year.'"'.(($Date[0] == $Year)?' selected="selected"':'').'>'.$Year.'</option>
';
             $Theme['yearselect'] = '<select name="year" id="year" title="'.e_i18n ('Year').'" tabindex="'.(++$TabIndex).'">
<option'.($Date[0]?'':' selected="selected"').' value="0">'.e_i18n ('All').'</option>
'.$SelectYears.'</select>
';
             }
        $ThemeSwitch['dateform'] = $Frequency;
        $Theme['dateformindex'] = ++$TabIndex;
        $Theme['lang_showdatafor'] = e_i18n ('Show data for');
        $Theme['lang_show'] = e_i18n ('Show');
        }
   $Theme['mapselect'] = '';
   $Theme['period'] = implode ('-', $Date);
   if (in_array ($Vars[1], array ('general', 'technical', 'time'))) $Vars[$Var] = $Theme['period'];
   if ((!function_exists ('e_geo_info') || !e_geo_info_available ()) && isset ($Menu[ESTATS_USERLEVEL]['geoip'])) unset ($Menu[ESTATS_USERLEVEL]['geoip']);
   if (!$CollectData['time'] && isset ($Menu[ESTATS_USERLEVEL]['time']) && !$DB->table_rows_amount ('time')) unset ($Menu[ESTATS_USERLEVEL]['time']);
   $Titles = array (
	'general' => 'General',
	'technical' => 'Technical',
	'geoip' => 'Geolocation',
	'time' => 'Time',
	'detailed' => 'Detailed',
	'details' => 'Visit details #%d',
	'admin' => 'Administration',
	'sites' => 'Sites',
	'referrers' => 'Referrers',
	'hosts' => 'Hosts',
	'keywords' => 'Keywords',
	'languages' => 'Languages',
	'cities' => 'Cities',
	'countries' => 'Countries',
	'continents' => 'Continents',
	'regions' => 'Regions',
	'browsers' => 'Browsers',
	'browsersversions' => 'Browser versions',
	'oses' => 'OSes',
	'osesversions' => 'OS versions',
	'websearchers' => 'Web searchers',
	'robots' => 'Bots',
	'proxy' => 'Proxy',
	'screens' => 'Screen resolutions',
	'flash' => 'Flash plugin',
	'java' => 'Java',
	'javascript' => 'JavaScript',
	'cookies' => 'Cookies',
	'24hours' => '24 hours',
	'week' => 'Week',
	'month' => 'Month',
	'year' => 'Year',
	'years' => 'Years',
	'hours' => 'Hours',
	'weekdays' => 'Days of week',
	'main' => 'Main page',
	'configuration' => 'Configuration',
	'advanced' => 'Advanced',
	'blacklist' => 'Blacklist',
	'backups' => 'Backups',
	'reset' => 'Resetting',
	'logs' => 'Logs',
	'plugins' => 'Plugins',
	'documentation' => 'Documentation',
	'forum' => 'Project\'s forum'
	);
   foreach ($Menu[ESTATS_USERLEVEL] as $Key => $Value) {
           $Theme['menu-'.$Key] = e_menu_entry ($Key, $Value, ($Vars[1] == $Key || ($Vars[1] == 'details' && $Key == 'detailed')));
           $Theme['submenu-'.$Key] = '';
           $ThemeSwitch['submenu-'.$Key] = 0;
           if (isset ($Value['submenu']) && count ($Value['submenu'])) {
              $ThemeSwitch['submenu-'.$Key] = 1;
              foreach ($Value['submenu'] as $SubKey => $SubValue) {
                      $ThemeSwitch['submenu-'.$Key.'_'.$SubKey] = 0;
                      $Theme['submenu-'.$Key].= e_menu_entry ($SubKey, $SubValue, (isset ($Vars[$SubMenuVar]) && $Vars[$SubMenuVar] == $SubKey), $Key);
                      }
              }
           if (isset ($Value['dynamicsubmenu']) && isset ($Blocks[$Key])) {
              $ThemeSwitch['submenu-'.$Key] = 1;
              foreach ($Blocks[$Key] as $SubKey => $SubValue) {
                      $ThemeSwitch['submenu-'.$Key.'_'.$SubKey] = 0;
                      if ((isset ($GroupAmount[$SubKey]) && !$GroupAmount[$SubKey]) || ($Key == 'time' && $CollectFrequency['time'] != 'hourly' && in_array ($SubKey, array ('24hours', 'hourspopularity')))) continue;
                      $Array = array (
	'text' => e_i18n ($Titles[$SubKey]),
	'title' => '',
	'link' => '{path}'.$Key.(($Key == 'geoip')?'/world':'').'/'.$SubKey.(($Key == 'time')?'/'.$TimeView:'').((isset ($Vars[$Var]) && $Vars[$Var])?'/'.$Vars[$Var]:'').'{suffix}'
	);
                      $Theme['submenu-'.$Key].= e_menu_entry ($SubKey, $Array, (isset ($Vars[$SubMenuVar]) && $Vars[$SubMenuVar] == $SubKey), $Key);
                      }
              }
           $Theme['menu'].= str_replace ('{submenu}', $Theme['submenu-'.$Key], $Theme['menu-'.$Key]);
           }
   }
$ThemeSwitch['menu'] = (boolean) $Theme['menu'];
if (!defined ('ESTATS_CRITICAL')) {
   if (defined ('ESTATS_LOADPAGE')) {
      if ($Vars[1] != 'image' && $Vars[1] != 'feed') $Theme['title'] = e_i18n ($Titles[$Vars[1]]).((ESTATS_USERLEVEL == 2 && $Vars[1] == 'admin')?' - '.e_i18n ($Titles[$Vars[2]]):'');
      if (isset ($_POST['Password']) && ($Vars[1] == 'admin' || $Pass)) e_log ((($Vars[1] == 'admin')?10:14), 2, 'IP: '.ESTATS_IP);
      if ($Vars[1] != 'admin') {
         e_theme_load ($Vars[1]);
         $Theme['page'] = (isset ($Theme[$Vars[1]])?$Theme[$Vars[1]]:'');
         }
      if (!include ('./pages/'.(($Vars[1] == 'admin')?'admin/'.$Vars[2]:'view/'.$Vars[1]).'.php')) e_error_message ('pages/'.((($Vars[1] == 'admin')?'admin/'.$Vars[2]:'view/'.$Vars[1]).'.php'), __FILE__, __LINE__);
      }
   if (ESTATS_LOGIN) {
      if (isset ($_POST['Password']) && !ESTATS_USERLEVEL) {
         e_log ((($Vars[1] == 'admin')?11:15), 2, 'IP: '.ESTATS_IP);
         $Information[] = array (e_i18n ('Wrong password!'), 'error');
         }
      if ($Vars[1] != 'admin' || $AdminBoard) {
         e_theme_load ('login');
         $Theme['page'] = $Theme['login'];
         $Theme['title'] = e_i18n ('Login');
         $Theme['lang_pass'] = e_i18n ('Password');
         $Theme['lang_remember'] = e_i18n ('Remember password');
         $Theme['lang_loginto'] = e_i18n ('Log into');
         }
      else {
           e_error_message (e_i18n ('Admin board was disabled!'), __FILE__, __LINE__);
           $Theme['title'] = e_i18n ('Access denied');
           }
      }
   if (!$StatsEnabled && !defined ('ESTATS_INSTALL') && !defined ('ESTATS_CRITICAL')) $Information[] = array (e_i18n ('Statistics are disabled.').((ESTATS_USERLEVEL == 2)?'<br />
<a href="{selfpath}{separator}statsenabled" tabindex="'.(++$TabIndex).'"><strong>'.e_i18n ('Enable statistics').'</strong></a>.':''), 'information');
   }
else $Theme['page'] = '';
if (defined ('ESTATS_INSTALL') && !include ('./install/index.php')) e_error_message ('install/index.php', __FILE__, __LINE__);
if ($Theme['css']) $Theme['css'] = '<style type="text/css">
'.$Theme['css'].'</style>
';
$ThemeSwitch['critical'] = defined ('ESTATS_CRITICAL');
if ($c = count ($Information)) {
   for ($i = 0; $i < $c; ++$i) {
       $Message = explode ('|', $Information[$i][0]);
       if (defined ('ESTATS_CRITICAL') && !$Information[$i][1]) {
          $Array = array (
	'Could not load file!',
	'Could not connect to database!',
	'This module does not supported on this server!'
	);
          for ($i = 0, $c = count ($Array); $i < $c; ++$i) {
              if (substr ($Message[0], 0, strlen ($Array[$i])) == $Array[$i]) $Message[0] = e_i18n (substr ($Message[0], 0, strlen ($Array[$i]))).substr ($Message[0], strlen ($Array[$i]));
              }
          }
       if (function_exists ('e_announce') && is_readable ('share/themes/'.$_SESSION['eStats']['theme'].'/theme.tpl')) $Theme['info'].= e_announce ((is_numeric ($Message[0])?e_i18n ($Logs[$Message[0]]).'.':$Message[0]).(isset ($Message[1])?'<br />
<em>'.$Message[1].'</em>.':''), $Information[$i][1]);
       else $Theme['info'].= implode (' - ', $Message).'<br />
';
       }
   }
$Theme['selfpath'] = $Theme['datapath'].$Path['prefix'].implode ('/', $Vars).$Path['suffix'];
$Theme['separator'] = $Path['separator'];
if (defined ('ESTATS_CRITICAL')) {
   $Theme['title'] = $Theme['header'] = e_i18n ('Critical error!');
   $Theme['page'] = '';
   }
$Theme['date'] = date ('d.m.Y H:i:s T');
$Theme['pageid'] = $Vars[($Vars[1] == 'admin')?2:1];
if (is_file ('share/themes/'.$_SESSION['eStats']['theme'].'/theme.php')) include ('./share/themes/'.$_SESSION['eStats']['theme'].'/theme.php');
if (function_exists ('e_string_parse') && ($Page = file_get_contents ('./share/themes/'.$_SESSION['eStats']['theme'].'/theme.tpl'))) {
   $Page = str_replace ('{page}', $Theme['page'], $Page);
   $Page = e_string_parse (e_string_parse (e_string_parse ($Page, $Theme), $Theme), $Theme);
   $ThemeSwitch['announcements'] = (int) $Information;
   header ($ThemeConfig['Header']);
   $Page = e_string_parse ($Page, array ('pagegeneration' => sprintf (e_i18n ('Page generation time: %.3lf (s)'), (array_sum (explode (' ', microtime ())) - $Start))));
   if (defined ('ESTATS_CRITICAL') || $ERRORS) $ThemeSwitch['announcements'] = 1;
   if ($ERRORS && (ESTATS_USERLEVEL == 2 || defined ('ESTATS_INSTALL'))) $Debug = e_announce ('<h4>'.e_i18n ('Debug').' ('.$ECounter.'):</h4><br />
<div id="debug">
'.rtrim ($ERRORS, "\r\n").'
</div>', 'information');
   else $Debug = '';
   foreach ($ThemeSwitch as $Key => $Value) $Page = preg_replace (
	array (
		'#<!--start:'.$Key.'-->(.*?)<!--end:'.$Key.'-->#si',
		'#<!--start:!'.$Key.'-->(.*?)<!--end:!'.$Key.'-->#si'
		),
	array (
		($Value?'\\1':''),
		($Value?'':'\\1'),
		),
	$Page
	);
   $Page = str_replace ('{debug}', $Debug, $Page);
   if ($ThemeConfig['Type'] != 'xhtml') $Page = str_replace (' />', '>', $Page);
   }
else die ($Page = '<h1>Critical error!</h1>
'.$Theme['info'].'<h2>Debug ('.$ECounter.' errors):</h2><br />
'.$ERRORS);
if ($Gzip && function_exists ('ob_gzhandler') && stristr ($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && !(ini_get ('zlib.output_compression') == 'On' || ini_get ('zlib.output_compression_level') > 0) || ini_get ('output_handler') == 'ob_gzhandler') {
   header ('Content-Encoding: gzip');
   ob_start ('ob_gzhandler');
   }
die (preg_replace ('#(\{tabindex\})#e', '++$TabIndex', $Page));
?>