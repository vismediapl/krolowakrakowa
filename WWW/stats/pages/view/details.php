<?php
if (!defined ('eStats')) die ();
e_theme_load ('detailed');
if (!include ('./lib/details.php')) e_error_message ('lib/details.php', __FILE__, __LINE__);
if (function_exists ('e_geo_init')) e_geo_init ();
$Theme['switch'] = '';
$Theme['title'] = sprintf ($Theme['title'], $Vars[2]);
$Theme['rows'] = '';
if (!isset ($Vars[2])) $Vars[2] = 1;
if (!isset ($Vars[3])) $Vars[3] = 1;
$Vars[2] = (int) $Vars[2];
$Vars[3] = (int) $Vars[3];
$Data = $DB->visit_details ($Vars[2], $Vars[3]);
$PagesAmount = ($Data?ceil ($Data['data']['visitsamount'] / $Detailed['detailsamount']):0);
if ($Data) {
   $c = (count ($Data['sites']) - 1);
   $Theme['page'] = str_replace ('{rowspan}', ($c + (($Data['data']['visitsamount'] > $Detailed['detailsamount'])?3:2)), e_details (e_details_prepare ($Data['data']), 0));
   for ($i = 0; $i <= $c; ++$i) {
       $Data['sites'][$i]['title'] = ($Data['sites'][$i]['title']?$Data['sites'][$i]['title']:$Data['sites'][$i]['address']);
       $Theme['rows'].= e_string_parse ($Theme['details-row'], array (
	'num' => ($Data['data']['visitsamount'] - $i - (($Data['page'] - 1) * $Detailed['detailsamount'])),
	'date' => date ('d.m.Y H:i:s', (is_numeric ($Data['sites'][$i]['time'])?$Data['sites'][$i]['time']:strtotime ($Data['sites'][$i]['time']))),
	'title' => htmlspecialchars ($Data['sites'][$i]['title']),
	'link' => '<a href="'.$Data['sites'][$i]['address'].'" tabindex="'.(++$TabIndex).'">'.e_string_cut ($Data['sites'][$i]['title'], $ThemeConfig['DetailsRowValueLength']).'</a>'
	));
       }
   if ($PagesAmount > 1) $Theme['title'].= ' - '.e_i18n ('page').' '.$Data['page'].'. '.e_i18n ('of').' '.$PagesAmount;
      $Theme['othervisits'] = '';
      $Next = $DB->visits_next ($Data['data']['id']);
      $Previous = $DB->visits_previous ($Data['data']['previous']);
      if ($Next && $Previous) $Array = array_merge (array_reverse ($Next), $Previous);
      else if ($Next) $Array = &$Next;
      else $Array = &$Previous;
      $ThemeSwitch['other-visits'] = (int) $Array;
      if ($ThemeSwitch['other-visits']) {
         for ($i = 0, $c = count ($Array); $i < $c; ++$i) {
             $ThemeSwitch['details-'.$Array[$i]['id']] = $Array[$i]['details'];
             $Theme['othervisits'].= e_string_parse ($Theme['other-visits-row'], array (
	'id' => $Array[$i]['id'],
	'tabindex' => ++$TabIndex,
	'first' => date ('d.m.Y H:i:s', (is_numeric ($Array[$i]['firstvisit'])?$Array[$i]['firstvisit']:strtotime ($Array[$i]['firstvisit']))),
	'last' => date ('d.m.Y H:i:s', (is_numeric ($Array[$i]['lastvisit'])?$Array[$i]['lastvisit']:strtotime ($Array[$i]['lastvisit']))),
	'amount' => (int) $Array[$i]['visitsamount'],
	));
             }
         }
   }
else $Theme['page'] = $Theme['details-none'];
$Theme['links'] = (($PagesAmount > 1)?str_replace ('{links}', e_links ($Data['page'], $PagesAmount, '{path}details/'.$Vars[2].'/{page}{suffix}'), $Theme['details-links']):'');
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
$Theme['lang_keywords'] = e_i18n ('Keywords');
$Theme['lang_visitedpages'] = e_i18n ('Visited pages');
$Theme['lang_othervisits'] = e_i18n ('Other visits');
$Theme['lang_date'] = e_i18n ('Date');
$Theme['lang_site'] = e_i18n ('Site');
$Theme['lang_dataunavailable'] = e_i18n ('Data unavailable');
?>