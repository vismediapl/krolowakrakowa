<?php
function e_theme_load ($File) {
	if (!is_file ('share/themes/'.$_SESSION['eStats']['theme'].'/'.$File.'.tpl')) $File = './share/themes/common/'.$File.'.tpl';
	else $File = './share/themes/'.$_SESSION['eStats']['theme'].'/'.$File.'.tpl';
	if (!is_file ($File)) {
		e_error_message ($File, __FILE__, __LINE__);
		return (0);
		}
	$Theme = file_get_contents ($File);
	preg_match_all ('#\[start:(.*?)\](.*?)\[/end\]#si', $Theme, $Blocks);
	for ($i = 0, $c = count ($Blocks[0]); $i < $c; ++$i) $GLOBALS['Theme'][$Blocks[1][$i]] = $Blocks[2][$i];
	}
function e_menu_entry ($Key, $Value, $Active, $Parent = '') {
	$Text = e_i18n (isset ($Value['text'])?$Value['text']:$GLOBALS['Titles'][$Key]);
	return (e_string_parse ($GLOBALS['Theme']['menuentry'], array (
	'link' => (isset ($Value['link'])?$Value['link']:$GLOBALS['Theme']['path'].($Parent?$Parent.'/':'').$Key).$GLOBALS['Path']['suffix'],
	'text' => $Text,
	'class' => ($Active?'active':''),
	'id' => $Parent.($Parent?'_':'').$Key,
	'accesskey' => ($Parent?'':e_accesskey ($Text)),
	'tabindex' => ++$GLOBALS['TabIndex']
	)));
	}
function e_accesskey ($String) {
	$Range = range ('A', 'Z');
	for ($i = 0, $c = strlen ($String); $i < $c; ++$i) {
		$Key = strtoupper ($String[$i]);
		if (!in_array ($Key, $GLOBALS['AccessKeys']) && in_array ($Key, $Range)) {
			$GLOBALS['AccessKeys'][] = $Key;
			return ($Key);
			}
		}
	return ('');
	}
function e_themes_list ($Selected = '') {
	$GLOBALS['Themes'] = $Dirs = glob ('share/themes/*', GLOB_ONLYDIR);
	$Page = '';
	for ($i = 0, $c = count ($Dirs); $i < $c; ++$i) {
		$Name = basename ($Dirs[$i]);
		if (is_file ($Dirs[$i].'/theme.ini') && $Name != 'common') {
			$TmpArray = parse_ini_file ($GLOBALS['Themes'][$i].'/theme.ini');
			$Page.= '<option value="'.urlencode ($Name).'"'.(($Name == $Selected)?' selected="selected"':'').'>'.htmlspecialchars ($TmpArray['Name']).'</option>
';
			}
		}
	return ($Page);
	}
function e_links ($Page, $Amount, $Path) {
	$Links = array ();
	if ($Amount < 2) return ('');
	$Array = array (
	'first' => '&#060;&#060;',
	'previous' => '&#060;',
	'next' => '&#062;',
	'last' => '&#062;&#062;'
	);
	$TmpArray = array_merge (array (
		'first' => 1,
		'previous' => ($Page - 1)
		),
	range (($Page - (($Page == 5)?4:3)), ($Page + (($Page == ($Amount - 4))?4:3))),
	array (
		'next' => ($Page + 1),
		'last' => $Amount
		));
	foreach ($TmpArray as $Key => $Value) {
			if (is_numeric ($Key)) $Key = $Value;
			if (!is_numeric ($Key) || ($Key > 0 && $Key <= $Amount)) {
				if ($Value > 0 && $Value <= $Amount && $Value != $Page) $Links[] = '<a href="'.str_replace ('{page}', $Value, $Path).'" tabindex="'.(++$GLOBALS['TabIndex']).'" title="'.e_i18n ('Go to'.(is_numeric ($Key)?'':' '.$Key).' page').' '.(is_numeric ($Key)?$Value.'.':'('.$Value.'.)').'">'.(is_numeric ($Key)?$Key:$Array[$Key]).'</a>';
				else $Links[] = '<strong>'.(is_numeric ($Key)?$Key:$Array[$Key]).'</strong>';
				if (($Page > 4 && $Key == 'previous' && $Page != 5) || ($Page < ($Amount - 3) && $Key == ($Page + 3) && $Page != ($Amount - 4) )) $Links[] = '...';
				}
			}
	return ('<div class="links">
'.implode ('
|
', $Links).'
</div>
');
	}
function e_announce ($Content, $Type) {
	return (e_string_parse ($GLOBALS['Theme']['announcement'], array (
	'class' => $Type,
	'type' => e_i18n (ucfirst ($Type)),
	'content' => $Content
	)));
	}
function e_icon ($Type, $Name, $Prefix = '') {
	return (e_icon_tag (e_icon_path ($Type, $Name), $Prefix.e_item_title ($Type, $Name)));
	}
function e_icon_tag ($FileName, $Title) {
	if (!is_dir ('share/icons/') || !$GLOBALS['ThemeConfig']['Icons']) return ('');
	return ('<img src="'.$GLOBALS['Theme']['datapath'].$FileName.'" alt="'.$Title.'" title="'.$Title.'" />');
	}
function e_icon_path ($Directory, $Name) {
	$Icon = trim ($Name);
	switch ($Directory) {
		case 'cities':
		$Directory = 'countries';
		$Icon = substr ($Name, -2);
		break;
		case 'countries':
		$Icon = $Name;
		break;
		case 'languages':
		$Directory = 'countries';
		$Lang = explode ('-', strtolower ($Name));
		if (isset ($Lang[1]) && isset ($GLOBALS['Countries'][$Lang[1]])) $Icon = $Lang[1];
		else if (isset ($GLOBALS['LanguageToCountry'][$Lang[0]])) $Icon = $GLOBALS['LanguageToCountry'][$Lang[0]];
		else $Icon = '?';
		break;
		case 'screens':
		$Directory = 'misc';
		$Array = array (0, 800, 1024, 1280, 1600, 5000);
		$Screens = array ('smallest', 'small', 'medium', 'big', 'biggest');
		if ((int) $Name) {
			for ($i = 0; $i < 5; ++$i) {
				if ((int) $Name >= $Array[$i] && (int) $Name < $Array[$i + 1]) {
					$Icon = 'screen_'.$Screens[$i];
					break;
					}
				}
			}
		else $Icon = '?';
		break;
		case 'browsersversions':
		$Icon = $Name = preg_replace ('#\s[\d\.]+\w*$#', '', $Name);
		case 'browsers':
		$Directory = 'browsers';
		$Array = e_icon_info_cache ('browsers');
		if (isset ($Array[$Name])) $Icon = $Array[$Name];
		break;
		case 'oses':
		case 'osesversions':
		$Directory = 'oses';
		$Array = e_icon_info_cache ('oses');
		if (isset ($Array[$Name])) $Icon = $Array[$Name];
		else if (strstr (trim ($Name), ' ')) {
			$Array = explode (' ', $Name);
			$Icon = $Array[1];
			}
		break;
		case 'robots':
		$Array = e_icon_info_cache ('robots');
		if (isset ($Array[$Name])) $Icon = $Array[$Name];
		}
	$Icon = str_replace (array (' ', '/'), '', strtolower ($Icon));
	if ($Icon == '?') {
		$Icon = 'unknown';
		$Directory = 'misc';
		}
	else if (!is_file ('share/icons/'.$Directory.'/'.$Icon.'.png')) {
		$Icon = 'na';
		$Directory = 'misc';
		}
	if ($Directory == 'misc' && is_file ('share/themes/'.$_SESSION['eStats']['theme'].'/icons/'.$Icon.'.png')) return ('share/themes/'.$_SESSION['eStats']['theme'].'/icons/'.$Icon.'.png');
	return ('share/icons/'.$Directory.'/'.$Icon.'.png');
	}
function e_icon_info_cache ($Type) {
	$FileName = 'cache/icons-info-'.$Type;
	if (!is_file ($GLOBALS['DataDir'].$FileName.'_'.$GLOBALS['DBID'].'.dat') || filemtime ('share/data/'.$Type.'.ini') - filemtime ($GLOBALS['DataDir'].$FileName.'_'.$GLOBALS['DBID'].'.dat') > 0) {
		$Data = array ();
		$Array = e_data_load ($Type);
		foreach ($Array as $Key => $Value) {
			if (isset ($Value['icon'])) $Data[str_replace ('.', ' ', $Key)] = $Value['icon'];
			}
		e_data_save ($FileName, $Data);
		}
	else $Data = e_data_read ($FileName);
	return ($Data);
	}
function e_item_title ($Type, $String) {
	if (trim ($String) == '?') return (e_i18n (($Type == 'referrers')?'Direct entries':'Unknown'));
	else if (in_array ($Type, array ('java', 'javascript', 'cookies'))) return (e_i18n ($String?'Yes':'No'));
	else if ($Type == 'flash' && !$String) return (e_i18n ('No'));
	switch ($Type) {
		case 'cities':
		$Country = (int) ($String[strlen ($String) - 3] == '-');
		$City = ($Country?substr ($String, 0, -3):$String);
		$String = (function_exists ('utf8_encode')?utf8_encode ($City):$City).($Country?', '.e_i18n ($GLOBALS['Countries'][substr ($String, -2)]):'');
		break;
		case 'continents':
		$String = e_i18n ($String?$GLOBALS['Continents'][$String]:'Unknown');
		break;
		case 'countries':
		$String = e_i18n (isset ($GLOBALS['Countries'][$String])?$GLOBALS['Countries'][$String]:'Unknown');
		break;
		case 'languages':
		$Lang = explode ('-', strtolower ($String));
		if (isset ($GLOBALS['Languages'][$Lang[0]])) {
			$String = e_i18n ($GLOBALS['Languages'][$Lang[0]]);
			if (isset ($Lang[1]) && isset ($GLOBALS['Countries'][$Lang[1]])) $String.= ' ('.e_i18n ($GLOBALS['Countries'][$Lang[1]]).')';
			}
		else $String = e_i18n ('Unknown');
		break;
		case 'oses':
		if ($String == 'mobile') $String = e_i18n ('Mobile devices');
		break;
		case 'regions':
		$Region = explode ('-', $String);
		$String = (isset ($GLOBALS['Regions'][$Region[0]][$Region[1]])?$GLOBALS['Regions'][$Region[0]][$Region[1]]:e_i18n ('Unknown'));
		break;
		case 'osesversions':
		if (substr ($String, 0, 6) == 'mobile') $String = substr ($String, 7);
		break;
		}
	return ($String);
	}
function e_number ($Num, $Tag = 1) {
	$Value = (($Num < 1000)?round ($Num, 2):(($Num < 1000000)?(round ($Num / 1000, 1)).'K':(round ($Num / 1000000, 1)).'M'));
	if ($Tag) return ('<em'.(($Num >= 1000 || is_float ($Num))?' title="'.round ($Num, 5).'"':'').'>'.$Value.'</em>');
	return ($Value);
	}
function e_size ($Size) {
	if ($Size === '?') return ('N/A');
	return (($Size > 1024)?(($Size > 1048576)?round ($Size / 1048576, 2).' MB':round ($Size / 1024, 2).' KB'):((int) $Size).' B');
	}
function e_string_cut ($String, $Length, $Title = 0, $Dots = 1) {
	if (!$Length) return (htmlspecialchars ($String));
	if (!function_exists ('mb_substr')) return ((strlen ($String) > ($Length + 3) || !$Dots)?($Title?'<span title="'.htmlspecialchars ($String).'">'.htmlspecialchars (substr_replace ($String, ($Dots?'...':''), $Length)).'</span>':htmlspecialchars (substr_replace ($String, ($Dots?'...':''), $Length))):htmlspecialchars ($String));
	else return ((mb_strwidth ($String, 'UTF-8') > ($Length + 3) || !$Dots)?($Title?'<span title="'.htmlspecialchars ($String).'">'.htmlspecialchars (mb_substr ($String, 0, $Length, 'UTF-8')).($Dots?'...':'').'</span>':htmlspecialchars (mb_substr ($String, 0, $Length, 'UTF-8')).($Dots?'...':'')):htmlspecialchars ($String));
	}
function e_string_parse ($String, $Array, $Start = 0, $End = 0) {
	if (!$Start) {
		$Start = '{';
		$End = '}';
		}
	if (!$End) $End = $Start;
	foreach ($Array as $Key => $Value) $String = str_replace ($Start.$Key.$End, $Value, $String);
	return ($String);
	}
function e_ignore_rule ($IPs, $IP, $Ignored = 1) {
	for ($i = 0, $c = count ($IPs); $i < $c; ++$i) {
		if ($IP == $IPs[$i] || (strstr ($IPs[$i], '*') && substr ($IP, 0, (strlen ($IPs[$i]) - 1)) == substr ($IPs[$i], 0, -1))) return ('<a href="{selfpath}{separator}'.($Ignored?'ignored':'blocked').'IP='.$IPs[$i].'" class="green" title="'.e_i18n ('Unblock IP'.(($IP == $IPs[$i])?'':'s range')).(($IP == $IPs[$i])?'':' ('.$IPs[$i].')').'" tabindex="'.(++$GLOBALS['TabIndex']).'"><strong>&#187;</strong></a>');
		}
	return ('<a href="{selfpath}{separator}'.($Ignored?'ignored':'blocked').'IP='.$IP.'" class="red" title="'.e_i18n ('Block this IP').'" onclick="if (!confirm (\''.e_i18n ('Do You really want to ban this IP address?').'\')) return false" tabindex="'.(++$GLOBALS['TabIndex']).'"><strong>&#187;</strong></a>');
	}
function e_link_whois ($Data, $String = 0) {
	return ('<a href="'.str_replace ('{data}', htmlspecialchars ($Data), $GLOBALS['WhoisLink']).'" title="'.e_i18n ('Whois').'" tabindex="'.(++$GLOBALS['TabIndex']).'">'.($String?$String:e_i18n ('Whois')).'</a>');
	}
function e_link_map ($Latitude, $Longitude) {
	return (str_replace (array ('{latitude}', '{longitude}'), array ($Latitude, $Longitude), $GLOBALS['MapLink']));
	}
function e_cache_status ($FileName, $Time, $Ext = '.dat') {
	$FileName = ESTATS_PATH.$GLOBALS['DataDir'].$FileName.'_'.$GLOBALS['DBID'].$Ext;
	return ($Time && (!is_file ($FileName) || (time () - filemtime ($FileName)) > $Time));
	}
function e_cache_info ($FileName) {
	return (e_announce (sprintf (e_i18n ('Data from <em>cache</em>, refreshed: %s.'), date ('d.m.Y H:i:s', filemtime ($GLOBALS['DataDir'].$FileName.'_'.$GLOBALS['DBID'].'.dat'))), 'information'));
	}
function e_time_range ($Year = 0, $Month = 0, $Day = 0, $Hour = 0) {
	if (!$Year) return (0);
	if ($Month) {
		if ($Day) {
			if ($Hour) {
				$From = strtotime ($Year.'-'.(($Month < 10)?'0':'').$Month.'-'.(($Day < 10)?'0':'').$Day.' '.(($Hour < 10)?'0':'').$Hour.':00');
				return (array ($From, ($From + 3600)));
				}
			else {
				$From = strtotime ($Year.'-'.(($Month < 10)?'0':'').$Month.'-'.(($Day < 10)?'0':'').$Day);
				return (array ($From, ($From + 86400)));
				}
			}
		else {
			$From = strtotime ($Year.'-'.(($Month < 10)?'0':'').$Month.'-01');
			return (array ($From, ($From + (date ('t', $From) * 86400))));
			}
		}
	else return (array (strtotime ($Year.'-01-01'), strtotime (($Year + 1).'-01-01')));
	}
function e_date_format ($Format, $String) {
	$Array = array (
	'Y' => substr ($String, 0, 4),
	'm' => substr ($String, 5, 2),
	'd' => substr ($String, 8, 2),
	'H' => substr ($String, 11, 2),
	'i' => substr ($String, 14, 2),
	's' => substr ($String, 17, 2)
	);
	if (strstr ($Format, 'w')) $Array['w'] = date ('w', strtotime ($String));
	return (strtr ($Format, $Array));
	}
function e_locale_load ($Locale, $Directory = '') {
	if (!defined ('ESTATS_GETTEXT')) define ('ESTATS_GETTEXT', extension_loaded ('gettext'));
	if (!$Directory && ESTATS_GETTEXT && is_file ('./locale/'.$Locale.'/LC_MESSAGES/estats.mo')) {
		bindtextdomain ('estats', './locale/');
		textdomain ('estats');
		bind_textdomain_codeset ('estats', 'UTF-8');
		}
	else {
		if ($Locale == 'en') return;
		$Path = $Directory.($Directory?'':'./locale/').$Locale.($Directory?'':'/locale').'.php';
		if (!is_file ($Path)) return;
		include ($Path);
		if (isset ($GLOBALS['Translation'])) $GLOBALS['Translation'] = array_merge ($L, $GLOBALS['Translation']);
		else $GLOBALS['Translation'] = $L;
		}
	}
function e_i18n ($String) {
	if (ESTATS_GETTEXT && !isset ($GLOBALS['Translation'][$String])) return (gettext ($String));
	else return (isset ($GLOBALS['Translation'][$String])?$GLOBALS['Translation'][$String]:$String);
	}
function e_verify_configuration () {
	if (!include ('./conf/template.php')) e_error_message ('conf/template.php', __FILE__, __LINE__);
	foreach ($Array as $Group => $Value) {
		foreach ($Value as $SubGroup => $Option) {
			if (is_array (reset ($Option))) {
				foreach ($Option as $Field => $SubOption) {
					if (!$GLOBALS['DB']->verify_configuration_option ($SubGroup.'|'.$Field, (int) ($Group != 'Stats'))) return (0);
					}
				}
			else {
				if (!$GLOBALS['DB']->verify_configuration_option ($SubGroup, (int) ($Group != 'Stats'))) return (0);
				}
			}
		}
	return (1);
	}
function e_clean () {
	if (defined ('ESTATS_CRITICAL') || date ('Ymd', $GLOBALS['LastClean']) == date ('Ymd')) return (0);
	$Files = glob (ESTATS_PATH.$GLOBALS['DataDir'].'cache/*');
	for ($i = 0, $c = count ($Files); $i < $c; ++$i) unlink ($Files[$i]);
	$GLOBALS['DB']->clean ();
	$GLOBALS['DB']->config_set (array ('LastClean' => time ()), 0);
	}
?>