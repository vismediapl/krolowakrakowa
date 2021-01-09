<?php
if (!defined ('eStats')) die ();
if (is_file ('lib/gd.php')) include ('./lib/gd.php');
if (!include ('./lib/group.php')) e_error_message ('lib/group.php', __FILE__, __LINE__);
$Data = e_group_init ($Blocks['general'], 'general', $Date);
if (isset ($Vars[2]) && isset ($Blocks['general'][$Vars[2]])) {
   $Theme['page'] = $Theme['group-page'];
   $Theme['group'] = e_group ($Vars[2], $Blocks['general'][$Vars[2]], $Data[$Vars[2]], $Date, 1);
   $Theme['title'].= ' - '.e_i18n ($Titles[$Vars[2]]);
   }
else {
     foreach ($Blocks['general'] as $Key => $Value) $Theme[$Key] = e_group ($Key, $Value, $Data[$Key], $Date, 0);
     }
$Array = e_summary ();
foreach ($Array as $Key => $Value) $Theme[$Key] = (is_array ($Value)?e_number ($Value['amount']).' ('.($Value['amount']?date ('d.m.Y', $Value['time']):'-').')':e_number ($Value));
$Theme['lang_visits'] = e_i18n ('Visits');
$Theme['lang_views'] = e_i18n ('Views');
$Theme['lang_unique'] = e_i18n ('Unique');
$Theme['lang_returns'] = e_i18n ('Returns');
$Theme['lang_excluded'] = e_i18n ('Excluded');
$Theme['lang_most'] = e_i18n ('Most');
$Theme['lang_lasthour'] = e_i18n ('Last hour');
$Theme['lang_last24hours'] = e_i18n ('Last twenty - four hours');
$Theme['lang_lastweek'] = e_i18n ('Last week');
$Theme['lang_lastmonth'] = e_i18n ('Last month');
$Theme['lang_lastyear'] = e_i18n ('Last year');
$Theme['lang_online'] = e_i18n ('On-line');
$Theme['lang_averageperday'] = e_i18n ('Average per day');
$Theme['lang_averageperhour'] = e_i18n ('Average per hour');
?>