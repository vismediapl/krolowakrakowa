<?php
function e_backups_info () {
	$BackupsSize = 0;
	$Backups = array_reverse (glob ($GLOBALS['DataDir'].'backups/*.{data,full,user}.{estats,'.strtolower ($GLOBALS['DBType']).'.sql}.bak', GLOB_BRACE));
	for ($i = 0, $c = count ($Backups); $i < $c; ++$i) $BackupsSize += filesize ($Backups[$i]);
	return (array (
	'size' => $BackupsSize,
	'amount' => $c
	));
	}
function e_cache_size () {
	$CacheSize = 0;
	$Files = glob ($GLOBALS['DataDir'].'cache/*');
	for ($i = 0, $c = count ($Files); $i < $c; ++$i) $CacheSize += filesize ($Files[$i]);
	return ($CacheSize);
	}
function e_config_row ($Desc, $ID, $Value, $Type) {
	$EID = str_replace (array ('[', ']'), array ('_', mt_rand (0, 10000000)), $ID);
	switch ($Type) {
		case 0:
		case 3:
		$Page = '<textarea rows="1" cols="25" name="'.$ID.'" id="F_'.$EID.'" tabindex="'.(++$GLOBALS['TabIndex']).'"'.($Type?' title="'.e_i18n ('Array, elements separated by |').'"':'').'>'.htmlspecialchars (($Type == 3)?implode ('|', $Value):$Value).'</textarea>';
		break;
		case 1:
		$Page = '<input type="checkbox" name="'.$ID.'" id="F_'.$EID.'" value="1" tabindex="'.(++$GLOBALS['TabIndex']).'"'.($Value?' checked="checked"':'').' />';
		break;
		case 2:
		$Page = '<input name="'.$ID.'" id="F_'.$EID.'" value="'.htmlspecialchars ($Value).'" tabindex="'.(++$GLOBALS['TabIndex']).'" />';
		break;
		}
	if (!is_int ($Type)) $Page = $Type;
	return (e_string_parse ($GLOBALS['Theme']['config-row'], array (
	'form' => $Page,
	'desc' => $Desc,
	'fid' => (!is_numeric ($Type)?'':'F_').$EID
	)));
	}
function e_option_row ($Array, $Key, $ID, $Value, $Desc) {
	$Name = $ID;
	$ID = str_replace ('|', '_', $ID);
	$Array[0] = str_replace (array ('{', '}'), array ('&#123;', '&#125;'), htmlspecialchars ($Array[0]));
	if (is_array ($Value)) $Value = implode ('|', $Value);
	$Value = str_replace (array ('{', '}'), array ('&#123;', '&#125;'), htmlspecialchars ($Value));
	switch ($Array[1]) {
		case 0:
		case 3:
		$Page = '<textarea rows="1" cols="25" name="F_'.$ID.'" id="F_'.$ID.'" tabindex="'.(++$GLOBALS['TabIndex']).'" title="'.e_i18n ($Array[1]?'Array, elements separated by |':'Text string').'" onkeydown="checkDefault (\''.$ID.'\', \''.str_replace (array ("\r\n", "\n"), array ('\r\n', '\n'), $Array[0]).'\', '.(int) ($Array[1] == 1).')">'.$Value.'</textarea>';
		break;
		case 1:
		$Page = '<input type="checkbox" name="F_'.$ID.'" id="F_'.$ID.'" value="1" tabindex="'.(++$GLOBALS['TabIndex']).'"'.($Value?' checked="checked"':'').' title="'.e_i18n ('Logic value').'" onchange="checkDefault (\''.$ID.'\', \''.$Array[0].'\', '.(int) ($Array[1] == 1).')" />';
		break;
		case 2:
		$Page = '<input name="F_'.$ID.'" id="F_'.$ID.'" value="'.htmlspecialchars ($Value).'" tabindex="'.(++$GLOBALS['TabIndex']).'" title="'.e_i18n ('Number').'" onkeydown="checkDefault (\''.$ID.'\', \''.$Array[0].'\', '.(int) ($Array[1] == 1).')" />';
		break;
		case 4:
		$Page = '<select name="F_'.$ID.'" id="F_'.$ID.'" tabindex="'.(++$GLOBALS['TabIndex']).'" title="'.e_i18n ('Select list').'" onchange="checkDefault (\''.$ID.'\', \''.$Array[0].'\', '.(int) ($Array[1] == 1).')">
';
		for ($i = 0, $c = count ($Array[2]); $i < $c; ++$i) $Page.= '<option'.(($Array[2][$i] == $Value)?' selected="selected"':'').'>'.$Array[2][$i].'</option>
';
		$Page.= '</select>';
		break;
		}
	return (e_string_parse ($GLOBALS['Theme']['option-row'], array (
	'id' => $ID,
	'changed' => (($Array[0] == (is_array ($Value)?implode ('|', $Value):$Value))?'':' class="changed" title="'.e_i18n ('Field value is other than default').'"'),
	'form' => $Page,
	'default' => str_replace (array ("\r\n", "\n"), array ('\r\n', '\n'), $Array[0]),
	'lang_default' => e_i18n ('Default'),
	'mode' => (int) ($Array[1] == 1),
	'defaultvalue' => $Array[0],
	'lang_defaultvalue' => e_i18n ('Default value'),
	'tabindex' => ++$GLOBALS['TabIndex'],
	'option' => $Key,
	'desc' => ($Desc?'<br />
<dfn>'.$Desc.'</dfn>':'')
	)));
	}
function e_buttons ($Mode = 0) {
	$Buttons = '';
	$i = 0;
	$Array = array (
	'Save' => e_i18n ('Do You really want to save?'),
	'Defaults' => e_i18n ('Do You really want to restore defaults?'),
	'Reset' => ''
	);
	foreach ($Array as $Key => $Value) $Buttons.= '<input type="'.($Value?'submit':'reset').'"'.(($Value || ($Mode == 1))?' onclick="'.($Value?'if (!confirm (\''.$Value.'\')) return false':'document.getElementById(\'UserDefinied\').style.display = (show?\'block\':\'none\');').'"':(($Mode == 2)?' onclick="resetAll ()"':'')).' value="'.e_i18n ($Key).'"'.(($i !=2 )?' name="'.($i++?'Defaults':'SaveConfig').'"':'').' tabindex="'.(++$GLOBALS['TabIndex']).'" class="button" />
';
	return ($Buttons);
	}
function e_download ($Data, $FileName, $Compress) {
	$Ext = '';
	switch ($Compress) {
		case 'gzip':
		header ('Content-Encoding: gzip');
		$Size = strlen ($Data);
		$Data = gzcompress ($Data, 9);
		$Data = "\x1f\x8b\x08\x00\x00\x00\x00\x00".substr ($Data, 0, $Size);
		break;
		case 'bzip':
		$Data = bzcompress ($Data);
		$Ext = '.bz2';
		}
	header ('Content-Type: application/force-download');
	header ('Content-Disposition: attachment; filename='.$FileName.$Ext);
	die (trim ($Data));
	}
function e_logs ($Page, $Amount, $Search = 0) {
	global $DB;
	if ($Search) $Clause = $DB->logs_search_clause ($_POST);
	else $Clause = '';
	$Data['all'] = $DB->table_rows_amount ('logs');
	$Data['amount'] = ($Clause?$DB->table_rows_amount ('logs', $Clause):$Data['all']);
	if (!$Page) $Data['page'] = ceil ($Data['amount'] / $Amount);
	else $Data['page'] = $Page;
	$From = ($Amount * ($Data['page'] - 1));
	if ($From > $Data['amount']) {
		$From = 0;
		$Data['page'] = 1;
		}
	if ($Data['amount']) $Data['data'] = $DB->logs ($Amount, $From, $Clause);
	else $Data['data'] = array ();
	return ($Data);
	}
function e_backup_restore ($BackupID, $Notify = 1) {
	$Status = $GLOBALS['DB']->backup_restore ($BackupID);
	if ($Notify) {
		if ($Status) e_log (24, $Status, 'ID: '.$BackupID);
		else $GLOBALS['Information'][] = array (e_i18n ('Unsuccessful backup restore attempt').'!', 'error');
		}
	return ($Status);
	}
function e_config_set ($Options) {
	$Config = array ();
	if (isset ($_POST['Defaults'])) {
		if (!include ('./conf/template.php')) e_error_message ('conf/template.php', __FILE__, __LINE__);
		$Defaults = array_merge ($Array['Stats'], $Array['GUI']);
		}
	for ($i = 0, $c = count ($Options); $i < $c; ++$i) {
		if (strstr ('Pass', $Options[$i]) && !isset ($_POST['Defaults'])) $Config[$Options[$i]] = ($_POST[$Options[$i]]?md5 ($_POST[$Options[$i]]):'');
		else {
			if (strstr ($Options[$i], '|')) $Option = explode ('|', $Options[$i]);
			else $Option = &$Options[$i];
			if (isset ($_POST['Defaults'])) $Config[$Options[$i]] = (is_array ($Option)?$Defaults[$Option[0]][$Option[1]][0]:$Defaults[$Option][0]);
			else $Config[$Options[$i]] = (isset ($_POST[$Options[$i]])?$_POST[$Options[$i]]:0);
			}
		}
	$GLOBALS['DB']->config_set ($Config);
	}
?>