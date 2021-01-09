<?php
function e_details_prepare ($Data) {
	$Data['browser'] = implode (' ', e_browser ($Data['useragent']));
	$Data['os'] = implode (' ', e_os ($Data['useragent']));
	$Data['keywords'] = '';
	if (strstr ($Data['host'], '.') && ESTATS_USERLEVEL < 2) $Data['host'] = '*'.substr ($Data['host'], strpos ($Data['host'], '.'));
	if ($Data['referrer'] && !$Data['robot']) {
		$Referrer = parse_url ($Data['referrer']);
		$Data['referrer-host'] = $Referrer['host'];
		if ($Data['websearch'] = e_websearcher ($Referrer, 0)) $Data['keywords'] = implode (', ', $Data['websearch'][1]);
		if (in_array ($Referrer['host'], $GLOBALS['Referrers']) && ESTATS_USERLEVEL < 2) $Data['referrer'] = '';
		}
	$Data['geoinfo'] = (function_exists ('e_geo_info')?e_geo_info ($Data['ip']):0);
	return ($Data);
	}
function e_details ($Data, $Mode) {
	static $Regions;
	if (!$Regions && is_readable ('share/data/regions.ini')) $Regions = e_data_load ('regions');
	global $ThemeConfig;
	if ($Data['robot']) {
		$Class = 'robot';
		$Type = '$';
		}
	else if (isset ($_SESSION['eStats']['visits'][$Data['id']])) {
		$Class = 'user';
		$Type = '!';
		}
	else if ((time () - strtotime ($Data['lastvisit'])) < 300) {
		$Class = 'online';
		$Type = '+';
		}
	else if ($Data['previous']) {
		$Class = 'returns';
		$Type = '^';
		}
	else {
		$Class = '';
		$Type = '&nbsp;';
		}
	if ($Mode) $GLOBALS['ThemeSwitch']['details-'.$Data['id']] = $Data['details'];
	return (e_string_parse ($GLOBALS['Theme'][$Mode?'detailed-row':'details'], array (
	'class' => $Class,
	'simpletype' => $Type,
	'id' => $Data['id'],
	'first' => date ('d.m.Y<b\r />H:i:s', (is_numeric ($Data['firstvisit'])?$Data['firstvisit']:strtotime ($Data['firstvisit']))),
	'last' => date ('d.m.Y<b\r />H:i:s', (is_numeric ($Data['lastvisit'])?$Data['lastvisit']:strtotime ($Data['lastvisit']))),
	'visits' => (int) $Data['visitsamount'],
	'tabindex' => ++$GLOBALS['TabIndex'],
	'referrer' => (($Data['referrer'] && !$Data['robot'])?'<a href="'.htmlspecialchars ($Data['referrer']).'" tabindex="'.(++$GLOBALS['TabIndex']).'"'.($Data['keywords']?' title="'.e_i18n ('Keywords').': '.htmlspecialchars ($Data['keywords']).'" class="tooltip"':'').' rel="nofollow">
'.e_string_cut ($Data['referrer'], $ThemeConfig['DetailedRowValueLength']).'
'.($Data['keywords']?'<span>
<strong>'.e_i18n ('Keywords').':</strong><br />
'.$Data['keywords'].'
</span>
':'').'</a>'.((ESTATS_USERLEVEL == 2)?'
<a href="{selfpath}{separator}referrer='.htmlspecialchars ($Data['referrer-host']).'" class="'.(in_array ($Data['referrer-host'], $GLOBALS['Referrers'])?'green" title="'.e_i18n ('Unblock counting of this referrer').'"':'red" onclick="if (!confirm (\''.e_i18n ('Do You really want to exclude this referrer?').'\')) return false" title="'.e_i18n ('Block counting of this referrer').'"').' tabindex="'.(++$GLOBALS['TabIndex']).'"><strong>&#187;</strong></a>':''):'&nbsp;'),
	'keywords' => e_string_cut ($Data['keywords'], $ThemeConfig['DetailedRowValueLength'], 1),
	'host' => ($Data['host']?e_string_cut ($Data['host'], $ThemeConfig['DetailedRowValueLength'], 1):e_i18n ('Unknown')).((ESTATS_USERLEVEL == 2 && $Data['ip'])?'<br /> '.(($Data['ip'] == '127.0.0.1')?$Data['ip']:e_link_whois ($Data['ip'], $Data['ip'])).'
'.e_ignore_rule ($GLOBALS['IgnoredIPs'], $Data['ip']):''),
	'useragent' => htmlspecialchars ($Data['useragent']),
	'lang_useragent' => e_i18n ('User Agent'),
	'configuration' => ($ThemeConfig['Icons']?($Data['robot']?e_icon ('robots', $Data['robot'], e_i18n ('Network robot').': ').'
':e_icon ('browsersversions', $Data['browser'], e_i18n ('Browser').': ').'
'.e_icon ('osesversions', $Data['os'], e_i18n ('Operating system').': ').'
'.(($Data['language'] != '?')?e_icon ('languages', $Data['language'], e_i18n ('Language').': ').'
':'').($Data['screen']?e_icon ('screens', $Data['screen'], e_i18n ('Screen resolution').': ').'
':'').(($Data['flash'] != 0 || $Data['flash'] == '?')?e_icon_tag (e_icon_path ('misc', 'flash'), e_i18n ('Flash plugin version').': '.e_item_title ('flash', $Data['flash'])).'
':'').($Data['java']?e_icon_tag (e_icon_path ('misc', 'java'), e_i18n ('Java enabled')).'
':'').($Data['javascript']?e_icon_tag (e_icon_path ('misc', 'javascript'), e_i18n ('JavaScript enabled')).'
':'').($Data['cookies']?e_icon_tag (e_icon_path ('misc', 'cookies'), e_i18n ('Cookies enabled')).'
':'').($Data['proxy']?e_link_whois ($Data['proxyip'], '
'.e_icon_tag (e_icon_path ('misc', 'proxy'), e_i18n ('Proxy').': '.htmlspecialchars ($Data['proxy'])).'
').'
':'')):'<small>
'.($Data['robot']?e_i18n ('Network robot').': '.$Data['robot'].'<br />
':e_i18n ('Browser').': <em>'.e_item_title ('browsersversions', $Data['browser']).'</em>.<br />
'.e_i18n ('Operating system').': <em>'.e_item_title ('osesversions', $Data['os']).'</em>.<br />
'.(($Data['language'] != '?')?e_i18n ('Language').': <em>'.e_item_title ('languages', $Data['language']).'</em>.<br />
':'')).e_i18n ('User Agent').': <em>'.e_string_cut ($Data['useragent'], 75).'</em>.<br />
'.($Data['screen']?e_i18n ('Screen resolution').': <em>'.$Data['screen'].'</em>.<br />
':'').(($Data['flash'] != 0 || $Data['flash'] == '?')?e_i18n ('Flash plugin version').': <em>'.e_item_title ('flash', $Data['flash']).'.</em><br />
':'').($Data['java']?e_i18n ('Java enabled').'.<br />
':'').($Data['javascript']?e_i18n ('JavaScript enabled').'.<br />
':'').($Data['cookies']?e_i18n ('Cookies enabled').'.<br />
':'').($Data['proxy']?e_link_whois ($Data['proxyip'], e_i18n ('Proxy')).': '.htmlspecialchars ($Data['proxy']).'<br />
':'').'</small>
').($Data['geoinfo']?'<a href="'.e_link_map ($Data['geoinfo']['latitude'], $Data['geoinfo']['longitude']).'" tabindex="'.(++$GLOBALS['TabIndex']).'" class="tooltip">
'.($ThemeConfig['Icons']?e_icon_tag (e_icon_path ('misc', 'geoip'), e_i18n ('Show location on map')).'
':'').'<span>
<strong>'.e_i18n ('Location').':</strong><br />
'.($Data['geoinfo']['city']?e_i18n ('City').': <em>'.e_item_title ('cities', $Data['geoinfo']['city']).'</em><br />
':'').(($Data['geoinfo']['region'] && isset ($Regions[strtoupper ($Data['geoinfo']['country'])][$Data['geoinfo']['region']]))?e_i18n ('Region').': <em>'.$Regions[strtoupper ($Data['geoinfo']['country'])][$Data['geoinfo']['region']].'</em><br />
':'').($Data['geoinfo']['country']?e_i18n ('Country').': <em>'.e_i18n ($GLOBALS['Countries'][$Data['geoinfo']['country']]).'</em><br />
':'').($Data['geoinfo']['continent']?e_i18n ('Continent').': <em>'.e_i18n ($GLOBALS['Continents'][$Data['geoinfo']['continent']]).'</em><br />
':'').e_i18n ('Co-ordinates').': <em>'.e_coordinates ($Data['geoinfo']['latitude'], $Data['geoinfo']['longitude']).'</em>
</span>
</a>
':'')
	)));
	}
?>