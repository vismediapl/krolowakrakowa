<?php
function e_group_init ($Array, $Mode, $Date) {
	e_theme_load ('group');
	$GLOBALS['Theme']['lang_fulllist'] = e_i18n ('Full list');
	$GLOBALS['Theme']['lang_chart'] = e_i18n ('Chart');
	$ThisYear = date ('Y');
	if ($Date[1] == date ('n') && $Date[0] == $ThisYear) $Suffix = 'current-month';
	else if ($Date[0] == $ThisYear && !$Date[1]) $Suffix = 'current-year';
	else if ($Date[0] || $Date[1]) $Suffix = ($Date[0]?$Date[0]:'').($Date[1]?'-'.$Date[1]:'');
	else $Suffix = '';
	$FileName = 'cache/'.$Mode.(($Mode == 'geoip')?'-'.$GLOBALS['GeoIPCountry'].'-'.$GLOBALS['GeoIPMode']:'').($Suffix?'-'.$Suffix:'');
	$ConfigArray = array ();
	if (ESTATS_EDITMODE) {
		$GLOBALS['Theme']['adminbuttons'] = '<div class="buttons">
<input type="submit" value="'.e_i18n ('Preview').'" name="Preview" tabindex="'.(++$GLOBALS['TabIndex']).'" class="button" />
'.e_buttons ().'</div>
';
		if (isset ($_POST['SaveConfig']) || isset ($_POST['Defaults']) || isset ($_POST['Preview'])) {
			$Options = array ();
			foreach ($Array as $Key => $Value) {
				if (isset ($_POST['SaveConfig']) || isset ($_POST['Defaults'])) {
					$Options[] = 'GroupAmount|'.$Key;
					$Options[] = 'CollectData|'.$Key;
					}
				else $GLOBALS['GroupAmountPreview'][$Key] = &$_POST['GroupAmount|'.$Key];
				}
			if (!isset ($_POST['Preview'])) {
				e_config_set ($Options);
				e_config_get (1);
				}
			}
		}
	else $GLOBALS['Theme']['adminbuttons'] = '';
	if ((($Suffix == 'current-month' || $Suffix == 'current-year') && is_file ($GLOBALS['DataDir'].$FileName.'.dat') && date ('Ym', filemtime ($GLOBALS['DataDir'].$FileName.'.dat')) != date ('Ym')) || e_cache_status ($FileName, $GLOBALS['DBCache']['others']) || (ESTATS_EDITMODE && isset ($_POST['GroupAmount'])) || ESTATS_USERLEVEL == 2) {
		$Range = e_time_range ($Date[0], $Date[1], $Date[2], $Date[3]);
		foreach ($Array as $Key => $Value) $Data[$Key] = $GLOBALS['DB']->data ($Key.(($Key == 'regions' || ($Key == 'cities' && strlen ($GLOBALS['GeoIPCountry']) == 2))?'_'.$GLOBALS['GeoIPCountry']:''), (isset ($GLOBALS['GroupAmountPreview'][$Key])?$GLOBALS['GroupAmountPreview'][$Key]:$GLOBALS['GroupAmount'][$Key]), $Range[0], $Range[1]);
		$GLOBALS['Theme']['cacheinfo'] = '';
		if (!ESTATS_EDITMODE) e_data_save ($FileName, $Data);
		}
	else {
		$Data = e_data_read ($FileName);
		$GLOBALS['Theme']['cacheinfo'] = e_cache_info ($FileName);
		}
	return ($Data);
	}
function e_group ($ID, $Icons, $Data, $Date, $Single = 0) {
	static $Unique;
	if (!$Unique) {
		$Range = e_time_range ($Date[0], $Date[1], $Date[2], $Date[3]);
		$Unique = $GLOBALS['DB']->visits_amount ('unique', $Range[0], $Range[1]);
		}
	global $GroupAmount;
	if (in_array ($ID, array ('cookies', 'flash', 'java', 'javascript', 'screens')) && $Data['sum'] < $Unique) {
		if (!isset ($Data['data']['?'])) $Data['data']['?'] = 0;
		$Data['data']['?'] += ($Unique - $Data['sum']);
		$Data['sum'] = $Unique;
		}
	arsort ($Data['data']);
	$GLOBALS['ThemeSwitch']['group_'.$ID.'_header'] = !((int) $Single);
	$GLOBALS['ThemeSwitch']['group_chart'] = ($Single && function_exists ('e_gd_available') && e_gd_available () && isset ($Data['amount']) && $Data['amount']);
	$GLOBALS['ThemeSwitch']['group_'.$ID.'_info'] = ($GroupAmount[$ID] && $Data['amount'] > $GroupAmount[$ID]);
	$GLOBALS['Theme']['chartid'] = $GLOBALS['Vars'][1].'-'.$ID;
	$_SESSION['eStats']['imagedata'][$GLOBALS['Vars'][1].'-'.$ID] = array (
	'type' => 'chart',
	'chart' => 'pie',
	'diagram' => $ID,
	'cache' => $GLOBALS['DBCache']['others'],
	'data' => $Data,
	'icons' => $Icons
	);
	$Page = '';
	$Information = '';
	if (!isset ($Data['amount']) || !$GroupAmount[$ID]) $Information.= e_announce (e_i18n ('No data to display!'), 'error');
	if (ESTATS_EDITMODE) $Information.= ($GroupAmount[$ID]?'':e_announce (e_i18n ('This group is disabled!'), 'warning')).((!in_array ($ID, array ('browsersversions', 'osesversions', 'cities', 'countries', 'regions', 'continents')) && !$GLOBALS['CollectData'][$ID])?e_announce (e_i18n ('Data collecting for this group was disabled!'), 'warning'):'');
	foreach ($Data['data'] as $Key => $Value) $Page.= e_group_row ($Key, $Value, $ID, $Icons, $Data['sum'], $Single);
	return (e_string_parse ($GLOBALS['Theme']['group'], array (
	'id' => $ID,
	'amount' => (int) $Data['amount'],
	'displayed' => (int) (($GroupAmount[$ID] > $Data['amount'])?$Data['amount']:$GroupAmount[$ID]),
	'limit' => (int) $GroupAmount[$ID],
	'title' => e_i18n ($GLOBALS['Titles'][$ID]),
	'link' => '{path}{pageid}/'.(($GLOBALS['Vars'][1] == 'geoip')?(in_array ($GLOBALS['Vars'][2], array ('countries', 'continents'))?'world':$GLOBALS['Vars'][2]).'/':'').$ID.($GLOBALS['Date'][0]?'/{period}':'').'{suffix}',
	'tabindex' => ++$GLOBALS['TabIndex'],
	'informations' => ($Information?str_replace ('{informations}', $Information, $GLOBALS['Theme']['group-informations']):''),
	'rows' => $Page,
	'sum' => (($Data['sum'] && $GroupAmount[$ID])?str_replace ('{amount}', e_number ($Data['sum']), $GLOBALS['Theme']['group-amount']):$GLOBALS['Theme']['group-none']),
	'lang_sum' => e_i18n ('Sum'),
	'lang_of' => e_i18n ('of'),
	'lang_none' => e_i18n ('None'),
	'admin' => (ESTATS_EDITMODE?str_replace ('{admin}', e_group_admin ($ID), $GLOBALS['Theme']['group-admin']):'')
	)));
	}
function e_group_row ($Key, $Value, $ID, $Icons, $Sum, $Single) {
	static $Num;
	static $LastID;
	if (!$LastID || $LastID != $ID) {
		$LastID = $ID;
		$Num = 0;
		}
	global $Theme;
	$Key = trim ($Key);
	$Address = '';
	if ($ID == 'sites') {
		$Address = $Key;
		$Key = ($Value[1]?$Value[1]:$Key);
		$Value = $Value[0];
		}
	if ($ID == 'websearchers') $Address = &$Key;
	if ($ID == 'referrers' && $Key != '?') $Address = &$Key;
	if ($ID == 'cities') {
		if ($Key && $Key != '?') $Address = e_link_map ($Value[1], $Value[2]);
		$Value = $Value[0];
		}
	if ($ID == 'countries' && $Key != '?') $Address = '{path}geoip/'.$Key.'/'.implode ('-', $GLOBALS['Date']).'{suffix}';
	$Title = e_item_title ($ID, $Key);
	if (ESTATS_EDITMODE && ($ID == 'referrers' || $ID == 'keywords') && $Key != '?') {
		if ($ID == 'referrers') $Referrer = parse_url ($Key);
		$AdminOptions = '
<a href="{selfpath}{separator}'.(($ID == 'referrers')?'referrer='.$Referrer['host']:'keyword='.urlencode ($Key)).'" class="red" tabindex="'.(++$GLOBALS['TabIndex']).'" title="'.e_i18n ('Block counting of this '.(($ID == 'referrers')?'referrer':'keyword / phrase')).'" onclick="if (!confirm (\''.e_i18n ('Do You really want to exclude this '.(($ID == 'referrers')?'referrer':'keyword / phrase').'?').'\')) return false">
<strong>&#187;</strong>
</a>';
		}
	else $AdminOptions = '';
	$String = e_string_cut ($Title, $GLOBALS['ThemeConfig'][($Single?'Single':'').'GroupRowValueLength']);
	return (e_string_parse ($Theme['group-row'], array (
	'title' => str_replace ('{', '&#123;', htmlspecialchars ($Title)),
	'num' => ++$Num,
	'icon' => ($Icons?e_icon_tag (e_icon_path ($ID, $Key), $String):''),
	'value' => ($Address?'<a href="'.htmlspecialchars ($Address).'" tabindex="'.(++$GLOBALS['TabIndex']).'" title="'.$String.'" rel="nofollow">
':'').str_replace ('{', '&#123;', $String).($Address?'
</a>':'').$AdminOptions,
	'amount' => e_number ($Value),
	'percent' => round ((($Value / $Sum) * 100), 2)
	)));
	}
function e_group_admin ($ID) {
	global $GroupAmount, $CollectData, $Data;
	$Page = '<h3>
'.e_i18n ('Settings').'
</h3>
<p>
<span>
<select name="GroupAmount|'.$ID.'" tabindex="'.(++$GLOBALS['TabIndex']).'" id="GroupAmount_'.$ID.'">
';
	$Max = (($GroupAmount[$ID] < $Data[$ID]['amount'])?$Data[$ID]['amount']:$GroupAmount[$ID]);
	$Selected = (isset ($GLOBALS['GroupAmountPreview'][$ID])?$GLOBALS['GroupAmountPreview'][$ID]:$GroupAmount[$ID]);
	if ($Max < 50) $Max = 50;
	for ($i = 0; $i <= $Max; ++$i) {
		if ($i > 100) $i += 49;
		else if ($i > 50) $i += 9;
		else if ($i > 30) $i += 4;
		$Page.= '<option'.(($i == $Selected)?' selected="selected"':'').'>'.$i.'</option>
';
		}
	$Page.= '</select>
</span>
<label for="GroupAmount_'.$ID.'">'.sprintf (e_i18n ('Amount of items (current: %d)'), $GroupAmount[$ID]).'</label>:
</p>
'.(in_array ($ID, array ('browsersversions', 'osesversions', 'cities', 'countries', 'regions', 'continents'))?'':'<p>
<span>
<input type="checkbox" value="1" name="CollectData|'.$ID.'"'.($CollectData[$ID]?' checked="checked"':'').' tabindex="'.(++$GLOBALS['TabIndex']).'" id="CollectData_'.$ID.'" />
</span>
<label for="CollectData_'.$ID.'">'.e_i18n ('Data collecting enabled for this group').'</label>:
</p>
');
	return ($Page);
	}
?>