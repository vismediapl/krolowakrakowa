<?php
if (!defined ('eStats')) die ();
if (isset ($_POST['SaveConfig']) || isset ($_POST['Defaults'])) e_config_set (array ('IgnoredIPs', 'BlockedIPs', 'Keywords', 'Referrers', 'BlacklistMonitor'));
if (!isset ($Vars[3])) $Vars[3] = 0;
$Theme['page'] = '<form action="{selfpath}" method="post">
<h3>'.e_i18n ('Settings').'</h3>
'.e_config_row (e_i18n ('Disallow stats viewing for selected IP addresses').' <a href="#desc" tabindex="'.(++$TabIndex).'"><sup>*</sup></a>', 'BlockedIPs', $BlockedIPs, 3).e_config_row (e_i18n ('Ignored IPs').' <a href="#desc" tabindex="'.(++$TabIndex).'"><sup>*</sup></a>', 'IgnoredIPs', $IgnoredIPs, 3).e_config_row (e_i18n ('Ignored keywords'), 'Keywords', $Keywords, 3).e_config_row (e_i18n ('Ignored referrers'), 'Referrers', $Referrers, 3).e_config_row (e_i18n ('Save informations about ignored and blocked visits'), 'BlacklistMonitor', $BlacklistMonitor, 1).'<p>
<small id="desc"><sup>*</sup> '.e_i18n ('Use * for replace end part of address.').'</small>
</p>
<div class="buttons">
'.e_buttons ().'</div>
<h3>'.e_i18n ('Ignored and blocked visits').'</h3>
<table cellpadding="0" cellspacing="0">
<thead>
<tr>
<th>
'.e_i18n ('IP').'
</th>
<th>
'.e_i18n ('First visit').'
</th>
<th>
'.e_i18n ('Last visit').'
</th>
<th>
'.e_i18n ('Amount of visits').'
</th>
<th colspan="2">
'.e_i18n ('User Agent').'
</th>
<th>
'.e_i18n ('Type').'
</th>
</tr>
</thead>
<tbody>
';
$EntriesAmount = $DB->table_rows_amount ('ignored');
$IgnoredAmount = 30;
if (!$Vars[3]) $Vars[3] = ceil ($EntriesAmount / $IgnoredAmount);
$From = ($IgnoredAmount * ($Vars[3] - 1));
if ($From > $EntriesAmount) {
   $From = 0;
   $Vars[3] = 1;
   }
$Entries = $DB->visits_ignored ($IgnoredAmount, $From);
for ($i = 0, $c = count ($Entries); $i < $c; ++$i) {
    if (!$Robot = e_robot ($Entries[$i]['useragent'])) {
       $Browser = implode (' ', e_browser ($Entries[$i]['useragent']));
       $OS = implode (' ', e_os ($Entries[$i]['useragent']));
       }
    else $Robot = 0;
    $Theme['page'].= '<tr>
<td>
'.(($Entries[$i]['ip'] == '127.0.0.1')?$Entries[$i]['ip']:e_link_whois ($Entries[$i]['ip'], $Entries[$i]['ip'])).'
'.e_ignore_rule (($Entries[$i]['type']?$BlockedIPs:$IgnoredIPs), $Entries[$i]['ip'], !$Entries[$i]['type']).'
</td>
<td>
'.date ('d.m.Y H:i:s', (is_numeric ($Entries[$i]['firstvisit'])?$Entries[$i]['firstvisit']:strtotime ($Entries[$i]['firstvisit']))).'
</td>
<td>
'.date ('d.m.Y H:i:s', (is_numeric ($Entries[$i]['lastview'])?$Entries[$i]['lastview']:strtotime ($Entries[$i]['lastview']))).'
</td>
<td>
<span title="'.e_i18n ('Unique').'">'.$Entries[$i]['unique'].'</span>
/
<span title="'.e_i18n ('Views').'">'.($Entries[$i]['unique'] + $Entries[$i]['views']).'</span>
</td>
<td>
'.e_string_cut ($Entries[$i]['useragent'], 40, 1).'
</td>
<td>
'.($Robot?e_icon ('robots', $Robot, e_i18n ('Network bot').': ').'
':e_icon ('browsersversions', $Browser, e_i18n ('Browser').': ').'
'.e_icon ('osesversions', $OS, e_i18n ('Operating system').': ').'
').'</td>
<td>
'.e_i18n ($Entries[$i]['type']?'Blocked':'Ignored').'
</td>
</tr>
';
    }
$Theme['page'].= ($c?'':'<tr>
<td colspan="7">
<strong>'.e_i18n ('None').'</strong>
</td>
</tr>
').'</tbody>
</table>
</form>
'.e_links ($Vars[3], ceil ($EntriesAmount / $IgnoredAmount), '{path}admin/blacklist/{page}{suffix}');
?>