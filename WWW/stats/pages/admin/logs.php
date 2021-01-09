<?php
if (!defined ('eStats')) die ();
$LogsSearch = array ();
if (isset ($_GET['search']) && !isset ($_POST['search'])) $Search = $_POST = $_GET;
if (isset ($_POST['search'])) {
   $Search = $_POST;
   foreach ($_POST as $Key => $Value) {
           if (is_array ($Value)) {
              for ($i = 0, $c = count ($Value); $i < $c; ++$i) $LogsSearch[] = $Key.'[]='.urlencode ($Value[$i]);
              }
           else $LogsSearch[] = $Key.'='.urlencode ($Value);
           }
   }
else $Search = 0;
if (!isset ($Vars[3])) $Vars[3] = 0;
$LogsAmount = 50;
if (e_cookie_get ('logsamount')) $LogsAmount = e_cookie_get ('logsamount');
if (isset ($_POST['amount'])) {
   $LogsAmount = (int) $_POST['amount'];
   e_cookie_set ('logsamount', $LogsAmount);
   }
if (isset ($_POST['export'])) {
   $Data = 'eStats v'.ESTATS_VERSION.' logs backup
Creation date: '.date ('m.d.Y H:i:s').'

';
   $Array = e_logs (0, 0, $Search);
   for ($i = 0, $c = count ($Array['data']); $i < $c; ++$i) $Data.= '
'.$Array['data'][$i]['time'].' - '.(isset ($Logs[$Array['data'][$i]['log']])?e_i18n ($Logs[$Array['data'][$i]['log']]):htmlspecialchars ($Array['data'][$i]['log'])).($Array['data'][$i]['info']?' ('.$Array['data'][$i]['info'].')':'');
   e_download ($Data, 'eStats_'.date ('Y-m-d').'.log.bak', 0);
   }
$Array = e_logs ((int) $Vars[3], $LogsAmount, $Search);
$Amount = count ($Array['data']);
$Filter = '';
foreach ($Logs as $Key => $Value) $Filter.= '<option value="'.$Key.'"'.((!isset ($_POST['filter']) || in_array ($Key, $_POST['filter']))?' selected="selected"':'').'>'.e_i18n ($Value).'</option>
';
$Theme['page'] = '<form action="{selfpath}" method="post">
<h3>'.e_i18n ('Search').'</h3>
'.e_config_row (''.e_i18n ('Find entry (search in all fields)').'', 'search', (isset ($_POST['search'])?stripslashes ($_POST['search']):''), 2).e_config_row (e_i18n ('Results per page'), 'amount', $LogsAmount, 2).e_config_row (e_i18n ('Filter'), 'filter', '', '<select name="filter[]" multiple="multiple" size="5" id="filter" tabindex="'.(++$TabIndex).'">
'.$Filter.'</select>').e_config_row (''.e_i18n ('In period').'', 'from', '', ''.e_i18n ('From').' <input name="from" value="'.(isset ($_POST['from'])?$_POST['from']:date ('Y-m-d H:00:00', eStats)).'" id="from" tabindex="'.(++$TabIndex).'" />
'.e_i18n ('To').' <input name="to" value="'.(isset ($_POST['to'])?$_POST['to']:date ('Y-m-d H:00:00', strtotime ('next hour'))).'" tabindex="'.(++$TabIndex).'" />
').'<div class="buttons">
<input type="submit" value="'.e_i18n ('Show').'" tabindex="'.(++$TabIndex).'" class="button" />
<input type="submit" name="export" value="'.e_i18n ('Export').'" tabindex="'.(++$TabIndex).'" class="button" />
<input type="reset" value="'.e_i18n ('Reset').'" tabindex="'.(++$TabIndex).'" class="button" />
</div>
<h3>'.e_i18n ('Browse').'</h3>
<p>
<strong>'.e_i18n ('Entries amount').': '.$Array['all'].'. '.e_i18n ('Meeting conditions').': '.$Array['amount'].'. '.e_i18n ('Showed').': '.$Amount.'.</strong>
</p>
<table cellspacing="0" cellpadding="1">
<tr>
<th>
#
</th>
<th>
'.e_i18n ('Date').'
</th>
<th>
'.e_i18n ('Log').'
</th>
<th>
'.e_i18n ('Information').'
</th>
</tr>
';
for ($i = 0; $i < $Amount; ++$i) {
    $Theme['page'].= '<tr>
<td>
<em>'.($i + 1 + (($Array['page'] - 1) * $LogsAmount)).'</em>.
</td>
<td>
'.date ('d.m.Y H:i:s', (is_numeric ($Array['data'][$i]['time'])?$Array['data'][$i]['time']:strtotime ($Array['data'][$i]['time']))).'
</td>
<td>
'.(isset ($Logs[$Array['data'][$i]['log']])?e_i18n ($Logs[$Array['data'][$i]['log']]):htmlspecialchars ($Array['data'][$i]['log'])).'
</td>
<td>
'.($Array['data'][$i]['info']?$Array['data'][$i]['info']:' ').'
</td>
</tr>
';
    }
if (!$Amount) $Theme['page'].= '<td colspan="4">
<strong>'.e_i18n ('None').'</strong>
</td>
';
$Theme['page'].= '</table>
</form>
'.e_links ($Array['page'], ceil ($Array['amount'] / $LogsAmount), '{path}admin/logs/{page}{suffix}'.($LogsSearch?'{separator}'.implode ('&amp;', $LogsSearch):''));
?>