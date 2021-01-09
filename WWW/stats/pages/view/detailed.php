<?php
if (!defined ('eStats')) die ();
if (!include ('./lib/details.php')) e_error_message ('lib/details.php', __FILE__, __LINE__);
if (function_exists ('e_geo_init')) e_geo_init ();
$Theme['rows'] = '';
if (!isset ($Vars[2])) $Vars[2] = 0;
if (e_cookie_get ('detailedview') !== FALSE) $Vars[2] = e_cookie_get ('detailedview');
if (isset ($_POST['Robots'])) {
   $Vars[2] = $_POST['Robots'];
   e_cookie_set ('detailedview', $Vars[2]);
   }
if (!isset ($Vars[3])) $Vars[3] = 1;
$Vars[2] = (int) $Vars[2];
$Vars[3] = (int) $Vars[3];
$Theme['robotsselect'] = '';
for ($i = 0; $i < 2; ++$i) $Theme['robotsselect'].= '<option value="'.$i.'"'.(($Vars[2] == $i)?' selected="selected"':'').'>'.e_i18n ($i?'Show':'Hide').'</option>
';
$Theme['selectrobotsindex'] = ++$TabIndex;
$Theme['robotsformindex'] = ++$TabIndex;
$FileName = 'cache/detailed'.($Vars[2]?'':'-norobots');
if ($Vars[3] != 1 || e_cache_status ($FileName, $GLOBALS['DBCache']['detailed']) || ESTATS_USERLEVEL == 2) {
   $Data = $DB->visits ($Vars[2], $Vars[3]);
   for ($i = 0, $c = count ($Data['data']); $i < $c; ++$i) $Data['data'][$i] = e_details_prepare ($Data['data'][$i]);
   e_data_save ($FileName, $Data);
   $Theme['cacheinfo'] = '';
   }
else {
     $Data = e_data_read ($FileName);
     $Theme['cacheinfo'] = e_cache_info ($FileName);
     }
$PagesAmount = ceil ($Data['amount'] / $Detailed['amount']);
for ($i = 0, $c = count ($Data['data']); $i < $c; ++$i) $Theme['rows'].= e_details ($Data['data'][$i], 1);
if ($PagesAmount > 1 && $Detailed['maxpages'] > 1) $Theme['title'].= ' - '.e_i18n ('page').' '.$Data['page'].'. '.e_i18n ('of').' '.$PagesAmount;
$Theme['links'] = (($PagesAmount > 1)?str_replace ('{links}', e_links ($Data['page'], $PagesAmount, '{path}detailed/'.$Vars[2].'/{page}{suffix}'), $Theme['detailed-links']):'');
if (!$Theme['rows']) $Theme['rows'] = $Theme['detailed-none'];
$Theme['lang_details'] = e_i18n ('Details');
$Theme['lang_legend'] = e_i18n ('Legend');
$Theme['lang_yourvisits'] = e_i18n ('Your visits');
$Theme['lang_onlinevisitors'] = e_i18n ('On-line visitors (last five minutes)');
$Theme['lang_returnsvisitors'] = e_i18n ('Returns visitors');
$Theme['lang_robots'] = e_i18n ('Bots');
$Theme['lang_firstvisit'] = e_i18n ('First visit');
$Theme['lang_lastvisit'] = e_i18n ('Last visit');
$Theme['lang_visitsamount'] = e_i18n ('Amount of visits');
$Theme['lang_referrer'] = e_i18n ('Referrer website');
$Theme['lang_host'] = e_i18n ('Host');
$Theme['lang_configuration'] = e_i18n ('Configuration');
$Theme['lang_none'] = e_i18n ('None');
?>