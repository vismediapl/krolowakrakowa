<?php
if (!defined ('eStats')) die ();
$Regions = e_data_load ('regions');
if (is_file ('lib/gd.php')) include ('./lib/gd.php');
if (!function_exists ('e_geo_info') || !e_geo_info_available ()) e_error_message ('Extension unavailable!', __FILE__, __LINE__, 1);
else {
     e_geo_init ();
     $Array = array ();
     for ($i = 0, $c = count ($AvailableCountries); $i < $c; ++$i) $Array[$AvailableCountries[$i]] = e_i18n ($Countries[$AvailableCountries[$i]]);
     asort ($Array);
     $MapsList = '';
     foreach ($Array as $Key => $Value) $MapsList.= '<option'.(($Vars[$Var - 1] == $Key)?' selected="selected"':'').' value="'.$Key.'">'.$Value.(is_file ('share/maps/'.$Key.'/map.ini')?' ('.e_i18n ('Map').')':'').'</option>
';
     $Theme['mapselect'] = '<select name="map" id="map" title="'.e_i18n ('Map view').'" tabindex="'.(++$TabIndex).'">
<optgroup label="'.e_i18n ('World').'">
<option'.(($Vars[$Var - 1] == 'countries')?' selected="selected"':'').' value="countries">'.e_i18n ('Countries').' ('.e_i18n ('Map').')</option>
<option'.(($Vars[$Var - 1] == 'continents')?' selected="selected"':'').' value="continents">'.e_i18n ('Continents').' ('.e_i18n ('Map').')</option>
</optgroup>
'.($MapsList?'<optgroup label="'.e_i18n ('Countries').'">
'.$MapsList.'</optgroup>
':'').'</select>
';
     $Map = ((!isset ($Vars[$Var - 1]) || !is_file ('share/maps/'.$Vars[$Var - 1].'/map.ini') || in_array ($Vars[$Var - 1], array ('continents', 'countries')))?'world':$Vars[$Var - 1]);
     $SingleCountry = (int) !in_array ($Vars[$Var - 1], array ('continents', 'countries'));
     $ThemeSwitch['singlecountry'] = $SingleCountry;
     $ThemeSwitch['map'] = (int) is_file ('share/maps/'.(in_array ($Vars[$Var - 1], array ('continents', 'countries', 'cities'))?'world':$Vars[$Var - 1]).'/map.ini');
     if ($SingleCountry) {
        $Blocks['geoip'] = array (
	'cities' => 0,
	'regions' => 0,
	);
        }
     $GeoIPMode = (isset ($Vars[3])?$Vars[3]:0);
     $GeoIPCountry = $Vars[2];
     if (!include ('./lib/group.php')) e_error_message ('lib/group.php', __FILE__, __LINE__);
     $Data = e_group_init ($Blocks['geoip'], 'geoip', $Date);
     $Theme['maptype'] = (((isset ($Vars[$Var - 1]) && in_array ($Vars[$Var - 1], array ('continents', 'countries', 'cities')) && $Vars[2] == 'world') || in_array ($Vars[2], array ('countries', 'continents')))?e_i18n ('World').(($Vars[2] != 'world')?': '.e_i18n (ucfirst ($Vars[2])):''):e_i18n ($Countries[$Vars[2]]));
     $Theme['maphrefs'] = $Theme['maptooltips'] = '';
     $Theme['title'].= ' - '.$Theme['maptype'];
     if ($Var == 4 && isset ($Vars[$Var - 1]) && isset ($Blocks['geoip'][$Vars[$Var - 1]])) {
        $Theme['page'] = $Theme['group-page'];
        $Theme['group'] = e_group ($Vars[$Var - 1], ($Vars[2] == 'world' && in_array ($Vars[$Var - 1], array ('cities', 'countries'))), $Data[$Vars[$Var - 1]], $Date, 1);
        $Theme['title'].= ': '.e_i18n (ucfirst ($Vars[$Var - 1]));
        }
     else {
          foreach ($Blocks['geoip'] as $Key => $Value) $Theme[$Key] = e_group ($Key, $Value, $Data[$Key], $Date, 0);
          $Data = $Data[(($Map == 'world' && isset ($Data['countries']))?'countries':'regions')];
          $Data['max'] = 0;
          if ($Map == 'world') {
             $Array = array (
	'gb' => 'uk',
	'fr' => 'fx',
	'yu' => 'rs'
	);
             foreach ($Array as $Key => $Value) {
                     if (isset ($Data['data'][$Key])) {
                        $Data['data'][$Value] = ($Data['data'][$Key] + (isset ($Data['data'][$Value])?$Data['data'][$Value]:0));
                        if (isset ($Data['data'][$Value])) unset ($Data['data'][$Value]);
                        }
                     }
             }
          if ($Vars[$Var - 1] == 'continents') $Data['continents'] = array_fill (0, 7, 0);
          foreach ($Data['data'] as $Key => $Value) {
                  $Data['data'][$Key] = (int) $Value;
                  if ($Vars[$Var - 1] == 'continents') {
                     $Continent = $CountryToContinent[$Key];
                     $Data['continents'][$Continent] += (int) $Value;
                     if ($Data['continents'][$Continent] > $Data['max']) $Data['max'] = $Data['continents'][$Continent];
                     }
                  else if ((int) $Value > $Data['max']) $Data['max'] = (int) $Value;
                  }
          $MapInfo = parse_ini_file ('./share/maps/'.$Map.'/map.ini', 1);
          $Theme['mapid'] = $Map.(($Map == 'world')?'-'.$Vars[$Var - 1]:'');
          $Theme['maptabindex'] = ++$TabIndex;
          $Theme['mapauthor'] = $MapInfo['Information']['Author'];
          $Theme['maplink'] = $MapInfo['Information']['URL'];
          $Theme['maptime'] = date ('Y.m.d H:i:s', $MapInfo['Information']['Time']);
          $_SESSION['eStats']['imagedata']['geoip-'.$Theme['mapid']] = array (
	'type' => 'map',
	'data' => $Data,
	'map' => $Map.(($Map == 'world')?'-'.$Vars[$Var - 1]:'')
	);
          if (is_file ('share/maps/'.$Map.(($Map == 'world')?'/'.$Vars[$Var - 1]:'').'/coordinates.ini')) {
             $Hrefs = parse_ini_file ('./share/maps/'.$Map.(($Map == 'world')?'/'.$Vars[$Var - 1]:'').'/coordinates.ini');
             if (isset ($Hrefs['\no'])) {
                $Hrefs['no'] = $Hrefs['\no'];
                unset ($Hrefs['\no']);
                }
             foreach ($Hrefs as $Key => $Value) {
                     $Amount = (isset ($Data[($Vars[$Var - 1] == 'continents')?'continents':'data'][(($Map != 'world')?$Vars[$Var - 1].'-':'').$Key])?(int) $Data[($Vars[$Var - 1] == 'continents')?'continents':'data'][(($Map != 'world')?$Vars[$Var - 1].'-':'').$Key]:0);
                     $Entry = (($Map == 'world')?e_i18n (($Vars[$Var - 1] == 'continents')?$Continents[$Key]:$Countries[$Key]):$Regions[$Vars[$Var - 1]][$Key]);
                     $Theme['maphrefs'].= '<area shape="poly" alt="'.$Entry.'" title="'.$Entry.(($Amount && $Data['sum'])?' - '.$Amount.(($Amount && $Data['sum'])?' ('.round ((($Amount / $Data['sum']) * 100), 2).'%)" onmouseover="document.getElementById(\'geoip_tooltip_'.$Key.'\').style.display=\'block\'" onmouseout="document.getElementById(\'geoip_tooltip_'.$Key.'\').style.display=\'none\''.(($Map == 'world')?'" href="{path}geoip/'.$Key.'/'.implode ('-', $Date).'{suffix}':''):''):'').'" coords="'.$Value.'" tabindex="'.(++$TabIndex).'" />
';
                    if ($Amount) $Theme['maptooltips'].= '<div id="geoip_tooltip_'.$Key.'" class="geoipinfo">
'.(($Vars[$Var - 1] == 'countries')?e_icon ('countries', $Key).'
':'').$Entry.' - '.$Amount.'
'.($Data['sum']?'('.round ((($Amount / $Data['sum']) * 100), 2).'%)
':'').'</div>
';
                     }
             }
          }
     }
$Theme['lang_map'] = e_i18n ('Map');
$Theme['lang_author'] = e_i18n ('Author');
?>