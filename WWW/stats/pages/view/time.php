<?php
if (!defined ('eStats')) die ();
if (is_file ('lib/gd.php')) include ('./lib/gd.php');
$GD = (function_exists ('e_gd_available') && e_gd_available () && !$ThemeConfig['SimpleCharts'] && $ChartsType != 'html');
$CurrentTime = (!$Date[0] || ((int) $Date[0] == date ('Y') && (int) $Date[1] == date ('n') && (int) $Date[2] == date ('j')));
if ($CurrentTime) $Date = array (0, 0, 0, 0);
$Theme['viewselect'] = '';
for ($i = 0; $i < 3; ++$i) $Theme['viewselect'].= '<option value="'.$Available[$i].'"'.(in_array ($Available[$i], $Types)?' selected="selected"':'').'>'.e_i18n (ucfirst ($Available[$i])).'</option>
';
$Theme['selectviewindex'] = ++$TabIndex;
if (!$CurrentTime) {
   $Theme['title'].= ' -';
   if ($Date[2]) $Theme['title'].= ' '.$Date[2];
   if ($Date[1]) $Theme['title'].= ' '.strftime ('%B', mktime (0, 0, 0, $Date[1]));
   $Theme['title'].= ' '.$Date[0];
   }
$TypesAmount = count ($Types);
$LevelsTypes = array ('max', 'average', 'min');
$LevelsNames = array ('maximum', 'average', 'minimum');
$Types = array_reverse ($Types);
$CookieData = e_cookie_get ('visits');
$ChartsAmount = 0;
foreach ($Blocks['time'] as $Key => $Value) {
        $Theme[$Key] = '';
        if (defined ('ESTATS_CRITICAL') || ($CollectFrequency['time'] != 'hourly' && in_array ($Key, array ('24hours', 'hours'))) || ($Var == 4 && $Key !== $Vars[2])) {
           $Theme[$Key] = '';
           continue;
           }
        $Popularity = in_array ($Key, array ('hours', 'weekdays'));
        $Step = 0;
        switch ($Key) {
               case '24hours':
               if ($CurrentTime) $Range = array (strtotime ('last day'), 0);
               else {
                    if (!$Date[2]) continue 2;
                    $Range = e_time_range ($Date[0], $Date[1], $Date[2]);
                    }
               ++$ChartsAmount;
               $Unit = 'hour';
               $Amount = 24;
               $Step = 3600;
               $DateString = 'Y.m.d H';
               break;
               case 'month':
               if ($CurrentTime) {
                  $Range = array (strtotime ('last month'), 0);
                  $Amount = date ('t', ((date ('t') == date ('j'))?time ():strtotime ('last month')));
                  }
               else {
                    if (!$Date[1]) continue 2;
                    $Range = e_time_range ($Date[0], $Date[1]);
                    $Amount = date ('t', ((date ('t', $Range[1]) == date ('j', $Range[1]))?$Range[1]:$Range[0]));
                    }
               ++$ChartsAmount;
               $Unit = 'day';
               $Step = 86400;
               $DateString = 'Y.m.d';
               break;
               case 'year':
               if ($CurrentTime) $Range = array (strtotime ('last year'), 0);
               else $Range = e_time_range ($Date[0]);
               ++$ChartsAmount;
               $Unit = 'month';
               $Amount = 12;
               $DateString = 'Y.m';
               break;
               case 'years':
               ++$ChartsAmount;
               $YearsRange = (date ('Y') - date ('Y', $CollectedFrom));
               $LastYears = $YearsRange;
               if ($LastYears > 15) $LastYears = 15;
               else if ($LastYears < 5) $LastYears = 5;
               if ($CurrentTime) $Range = array (strtotime ('-'.$LastYears.' years'), 0);
               else $Range = array (strtotime (($Date[0] - $LastYears + 1).'-01-01'), strtotime (($Date[0] + 1).'-01-01'));
               ++$ChartsAmount;
               $Unit = 'year';
               $Amount = $LastYears;
               $DateString = 'Y';
               break;
               case 'hours':
               if ($CurrentTime) $Range = array (0, 0);
               else $Range = e_time_range ($Date[0], $Date[1], $Date[2]);
               ++$ChartsAmount;
               $Unit = 'dayhour';
               $Amount = 24;
               break;
               case 'weekdays':
               if ($WeekStartDay) {
                  $WeekDayTransition = range (0, 6);
                  $WeekDayTransition = array_merge (array_slice ($WeekDayTransition, $WeekStartDay), array_slice ($WeekDayTransition, 0, $WeekStartDay));
                  }
               if ($CurrentTime) $Range = array (0, 0);
               else {
                    if ($Date[2]) continue 2;
                    $Range = e_time_range ($Date[0], $Date[1], $Date[2]);
                    }
               ++$ChartsAmount;
               $Unit = 'weekday';
               $Amount = 7;
               $DateString = 'w';
               }
        $FileName = 'cache/time-'.$Key.'-'.implode ('+', $Types).'-'.implode ('_', $Date);
        if (e_cache_status ($FileName, $DBCache['time']) || ESTATS_USERLEVEL == 2) {
           $Data = $DB->time ($Unit, $Range[0], $Range[1], $Types, $Popularity);
           ksort ($Data);
           $Summary = array ();
           for ($i = 0; $i < $TypesAmount; ++$i) {
               $Summary['max'][$Types[$i]] = 0;
               $Summary['min'][$Types[$i]] = 0;
               $Summary['sum'][$Types[$i]] = 0;
               }
           foreach ($Data as $Unit => $Array) {
                   for ($i = 0; $i < $TypesAmount; ++$i) {
                       if ($Array[$Types[$i]] > $Summary['max'][$Types[$i]]) $Summary['max'][$Types[$i]] = $Array[$Types[$i]];
                       if ($Array[$Types[$i]] < $Summary['min'][$Types[$i]] || !$Summary['min'][$Types[$i]]) $Summary['min'][$Types[$i]] = $Array[$Types[$i]];
                       $Summary['sum'][$Types[$i]] += $Array[$Types[$i]];
                       }
                   }
           for ($i = 0; $i < $TypesAmount; ++$i) $Summary['average'][$Types[$i]] = ($Summary['sum'][$Types[$i]] / $Amount);
           krsort ($Summary['sum']);
           $Summary['maxall'] = max ($Summary['max']);
           if (count ($Data) != $Amount) {
              for ($i = 0; $i < $TypesAmount; ++$i) $Summary['min'][$Types[$i]] = 0;
              }
           e_data_save ($FileName, array (
	'data' => $Data,
	'summary' => $Summary
	));
           $CacheInfo = '';
           }
        else {
             $Data = e_data_read ($FileName);
             $Summary = $Data['summary'];
             $Data = $Data['data'];
             $CacheInfo = e_cache_info ($FileName);
             }
        if ($GD) {
           $GDSummary = $Summary;
           $GDSummary['amount'] = $Amount;
           $GDSummary['types'] = $Types;
           $GDSummary['chart'] = $Key;
           $GDSummary['step'] = $Step;
           $GDSummary['timestamp'] = $Range[0];
           $GDSummary['weekstart'] = $WeekStartDay;
           $GDSummary['datestring'] = $DateString;
           $GDSummary['currenttime'] = $CurrentTime;
           $_SESSION['eStats']['imagedata']['time-'.$Key.(($CurrentTime)?'':'-'.$Date[0].'_'.$Date[1].'_'.$Date[2])] = array (
	'type' => 'chart',
	'chart' => $ChartsType,
	'diagram' => $Key,
	'data' => $Data,
	'info' => $GDSummary,
	'mode' => implode ('+', $Types),
	'cache' => $DBCache['time'],
	'join' => $Popularity
	);
           }
        $MaxValues = $MinValues = array ();
        $TimeStamp = $Range[0];
        $ChartArea = '';
        switch ($Key) {
               case '24hours':
               if (!$CurrentTime || date ('G') == 23) break;
               $Yesterday = strtotime ('yesterday');
               $ChartArea = '<tfoot>
<tr>
<th colspan="'.(23 - date ('G')).'" title="'.ucfirst (strftime ('%A', $Yesterday)).'">
'.(in_array (date ('w', $Yesterday), array (0, 6))?'<em>'.strtoupper (strftime ('%a', $Yesterday)).'</em>':strtoupper (strftime ('%a', $Yesterday))).'
</th>
<th colspan="'.(date ('G') + 1).'" title="'.ucfirst (strftime ('%A')).'">
'.(in_array (date ('w'), array (0, 6))?'<em>'.strtoupper (strftime ('%a')).'</em>':strtoupper (strftime ('%a'))).'
</th>
<th> </th>
</tr>
</tfoot>
';
               break;
               case 'month':
               if (!$CurrentTime || date ('t') == date ('j')) break;
               $Month = ((date ('n') - 1)?(date ('n') - 1):12);
               $ChartArea = '<tfoot>
<tr>
<th colspan="'.(date ('t', strtotime ('last month')) - date ('j')).'" title="'.ucfirst (strftime ('%B', mktime (0, 0, 0, $Month))).'">
'.strtoupper (strftime ('%b', mktime (0, 0, 0, $Month))).'
</th>
<th colspan="'. date ('j').'" title="'.ucfirst (strftime ('%B')).'">
'.strtoupper (strftime ('%b')).'
</th>
<th> </th>
</tr>
</tfoot>
';
               break;
               case 'year':
               $TimeStamp = strtotime (date ('Y-m-01', $TimeStamp));
               if (!$CurrentTime || date ('n') == 12) break;
               $ChartArea = '<tfoot>
<tr>
<th colspan="'.(12 - date ('n')).'" title="'.(date ('Y') - 1).'">
'.(date ('Y') - 1).'
</th>
<th colspan="'.date ('n').'" title="'.date ('Y').'">
'.date ('Y').'
</th>
<th> </th>
</tr>
</tfoot>
';
               break;
               case 'years':
               $TimeStamp = strtotime (date ('Y-01-01', $TimeStamp));
               default:
               $ChartArea = '';
               }
        $Descriptions = $Chart = '';
        $BarWidth = round (700 / $Amount);
        for ($i = 0; $i < $Amount; ++$i) {
            if ($Key == 'year') $Step = (date ('t', $TimeStamp) * 86400);
            else if ($Key == 'years') $Step = ((date ('L', $TimeStamp) + 365) * 86400);
            if ($CurrentTime && $Step) $TimeStamp += $Step;
            if ($Key == 'hours') $UnitID = (($i < 10)?'0':'').$i;
            else if ($Key == 'weekdays') {
                    if ($WeekStartDay) $UnitID = $WeekDayTransition[$i];
                    else $UnitID = $i;
                    }
            else $UnitID = date ($DateString, $TimeStamp);
            $Title = $Description = $Bars = $ToolTip = '';
            $NextTimeStamp = $YourVisits = 0;
            switch ($Key) {
                   case '24hours':
                   $Description = date ('H', $TimeStamp);
                   $ToolTipDate = date ('Y.m.d H:00');
                   break;
                   case 'month':
                   $WeekDay = date ('w', $TimeStamp);
                   $Title = strftime ('%A', $TimeStamp);
                   $Description = date ('d', $TimeStamp);
                   if ($WeekDay == 0 || $WeekDay == 6) $Description = '<em>'.$Description.'</em>';
                   $ToolTipDate = $Title.', '.date ('Y.m.d');
                   $Title = ucfirst ($Title);
                   break;
                   case 'year':
                   $DateID = date ('n', $TimeStamp);
                   $Description = strtoupper (strftime ('%b', mktime (0, 0, 0, $DateID)));
                   $Title = strftime ('%B', mktime (0, 0, 0, $DateID));
                   $ToolTipDate = $Title.' '.date ('Y', $TimeStamp);
                   $Title = ucfirst ($Title);
                   break;
                   case 'years':
                   $ToolTipDate = $Description = $UnitID;
                   break;
                   case 'weekdays':
                   $TimeStamp = (94694400 + (($UnitID - 1) * 86400));
                   $ToolTipDate = strftime ('%A', $TimeStamp);
                   $Title = ucfirst ($ToolTipDate);
                   $Description = strtoupper (strftime ('%a', $TimeStamp));
                   if ($UnitID == 0 || $UnitID == 6) $Description = '<em>'.$Description.'</em>';
                   break;
                   case 'hours':
                   $ToolTipDate = $Description = $UnitID;
                   }
            $Descriptions.= '<th'.($Title?' title="'.$Title.'"':'').'>'.$Description.'</th>
';
            for ($j = 0; $j < $TypesAmount; ++$j) {
                if (isset ($Data[$UnitID][$Types[$j]]) && $Data[$UnitID][$Types[$j]] == $Summary['max'][$Types[$j]]) $MaxValues[$Types[$j]][] = $i;
                if (!isset ($Data[$UnitID][$Types[$j]]) || $Data[$UnitID][$Types[$j]] == $Summary['min'][$Types[$j]]) $MinValues[$Types[$j]][] = $i;
                $Size[$Types[$j]] = ((isset ($Data[$UnitID][$Types[$j]]) && $Data[$UnitID][$Types[$j]])?(($Data[$UnitID][$Types[$j]] / $Summary['maxall']) * 150):0);
                if (!$GD) {
                   $Height = $Size[$Types[$j]];
                   if (!$Height) $Bars.= '<div class="empty"></div>
';
                   else $Bars.= e_string_parse ($Theme['chart-bar'], array (
	'height' => round ($Height),
	'margin' => round (150 - $Size[$Types[$j]]),
	'class' => $Types[$j].(($Data[$UnitID][$Types[$j]] == $Summary['max'][$Types[$j]])?' max':''),
	'title' => e_i18n (ucfirst ($Types[$j])).': '.$Data[$UnitID][$Types[$j]],
	'simplebar' => ($ThemeConfig['SimpleCharts']?str_repeat (' <br />
', (int) (($Height / 150) * 10)):'')
	));
                   }
                if (!$ThemeConfig['SimpleCharts']) {
                   $Num = (isset ($Data[$UnitID][$Types[$j]])?$Data[$UnitID][$Types[$j]]:0);
                   $Sum = $Summary['sum'][$Types[$j]];
                   $ToolTip.= e_i18n (ucfirst ($Types[$j])).((isset ($Data[$UnitID][$Types[$j]]) && $Data[$UnitID][$Types[$j]] == $Summary['max'][$Types[$j]] && $Summary['max'][$Types[$j]])?' ('.e_i18n ('maximum').')':((!isset ($Data[$UnitID][$Types[$j]]) || $Data[$UnitID][$Types[$j]] == $Summary['min'][$Types[$j]])?' ('.e_i18n ('minimum').')':'')).': '.e_number ($Num).' ('.($Sum?round ((($Num / $Sum) * 100), 1):0).'%)<br />
';
                   }
                }
            if (!array_sum ($Size)) $ToolTip = '';
            else if ($CookieData) {
                    foreach ($CookieData as $Visit => $VisitTime) {
                            if (!isset ($VisitTime['first'])) continue;
                            switch ($Key) {
                                   case 'hours':
                                   if (date ('G', $VisitTime['first']) == $UnitID) ++$YourVisits;
                                   break;
                                   case 'weekdays':
                                   if (date ('w', $VisitTime['first']) == $UnitID) ++$YourVisits;
                                   break;
                                   default:
                                   if (!$NextTimeStamp) {
                                      if ($Step) $NextTimestamp = ($TimeStamp + $Step);
                                      else if ($Key == 'year') $NextTimeStamp = ($TimeStamp + (date ('t', $TimeStamp) * 86400));
                                      else if ($Key == 'years') $NextTimeStamp = ($TimeStamp + ((date ('L', $TimeStamp) + 365) * 86400));
                                      }
                                   if ($VisitTime['first'] >= $TimeStamp && $VisitTime['first'] < $NextTimestamp) ++$YourVisits;
                                   }
                            }
                    }
            $Chart.= e_string_parse ($Theme['chart-bars-container'], array (
	'class' => ' class="bars_'.$TypesAmount.'"',
	'width' => $BarWidth,
	'id' => 'bar_'.$Key.'_'.$i,
	'bars' => ($GD?'<div class="empty"></div>
':$Bars),
	'tooltip' => ($ToolTip?'<span>
<strong>'.e_i18n ('Visits').' ('.$ToolTipDate.'):</strong><br />
'.$ToolTip.($YourVisits?e_i18n ('Your visits').': '.e_number ($YourVisits).' ('.((isset ($Summary['sum']['unique']) && $Summary['sum']['unique'])?round ((($YourVisits / $Summary['sum']['unique']) * 100), 1):0).'%)<br />
':'').'</span>':' ')
	));
            if (!$CurrentTime && $Step) $TimeStamp += $Step;
            }
        $Levels = $Scale = '';
        if ($Summary['maxall']) {
           if (!$ThemeConfig['SimpleCharts']) {
              $Levels = '';
              for ($i = 0; $i < $TypesAmount; ++$i) {
                  for ($j = 0; $j < 3; ++$j) $Levels.= '<hr style="margin-top:-'.(int) ((($Summary[$LevelsTypes[$j]][$Types[$i]] / $Summary['maxall']) * 150) + 2).'px;" class="'.$Types[$i].'" title="'.e_i18n (ucfirst ($Types[$i])).' - '.e_i18n ($LevelsNames[$j]).': '.round ($Summary[$LevelsTypes[$j]][$Types[$i]], 2).'" id="level_'.$Key.'_'.$Types[$i].'_'.$j.'" />
';
                  }
              }
           for ($i = 10; $i > 0; $i--) $Scale.= e_number (($Summary['maxall'] * $i) / 10).'
';
           $Scale.= '<em>0</em>';
           }
        else $Scale = str_repeat ('
', 12);
        $ChartArea.= '<tbody>
<tr>
'.$Chart.'<td class="scale" style="width:'.(700 - ($Amount * $BarWidth)).'px !important;">
<pre>'.$Scale.'</pre>
</td>
</tr>
<tr>
<td colspan="'.$Amount.'" class="levels">
'.$Levels.'</td>
</tr>
<tr>
'.$Descriptions.'<th>{button}</th>
</tr>
</tbody>
';
        $SummaryTable = '';
        for ($i = 0; $i < $TypesAmount; ++$i) {
            $Array = array (
	'maxjs' => (isset ($MaxValues[$Types[$i]])?implode (', ', $MaxValues[$Types[$i]]):''),
	'avgjs' => '',
	'minjs' => (isset ($MinValues[$Types[$i]])?implode (', ', $MinValues[$Types[$i]]):''),
	'sumjs' => '\'_\'',
	);
            $ThemeArray = array (
	'text' => e_i18n (ucfirst ($Types[$i])),
	'sum' => e_number ($Summary['sum'][$Types[$i]]),
	'max' => e_number ($Summary['max'][$Types[$i]]),
	'avg' => e_number ($Summary['average'][$Types[$i]]),
	'min' => e_number ($Summary['min'][$Types[$i]]),
	);
            $j = 0;
            foreach ($Array as $String => $Number) {
                    $ThemeArray[$String] = '';
                    if ($Summary['max'][$Types[$i]]) {
                       for ($k = 0; $k < 2; ++$k) $ThemeArray[$String].= ' onmouseo'.($k?'ut':'ver').'="highlightColumns (['.$Number.'], \''.$Key.'\', \''.$Types[$i].'\', '.$j.', '.(int) !$k.')"'.($k?' id="switch_'.$Key.'_'.$Types[$i].'_'.($j++).'"':'');
                       }
                    }
            $SummaryTable.= e_string_parse ($Theme['chart-summary-row'], $ThemeArray);
            }
        $Theme[$Key] = e_string_parse ($Theme['chart'], array (
	'id' => $Key,
	'class' => (in_array ($Key, array ('24hours', 'month', 'hours'))?' narrow':'').($GD?'':' nogd'),
	'style' => ($GD?' style="background:url({path}image{suffix}{separator}id=time-'.$Key.(($CurrentTime)?'':'-'.$Date[0].'_'.$Date[1].'_'.$Date[2]).') no-repeat left 23px;"':''),
	'title' => e_i18n ($Titles[$Key]),
	'link' => '{path}time/'.$Key.'/'.implode ('+', $Types).($CurrentTime?'':'/{period}').'{suffix}',
	'tabindex' => ++$TabIndex,
	'colspan' => ($Amount + 1),
	'cacheinfo' => $CacheInfo,
	'chart' => $ChartArea,
	'summary' => str_replace ('{rows}', $SummaryTable, $Theme['chart-summary']),
	'button' => (($Summary['maxall'] && !$ThemeConfig['SimpleCharts'])?'<input type="checkbox" id="levels_switch_'.$Key.'" onclick="levelsShowHide (\''.$Key.'\')"'.((!isset ($_COOKIE['estats_time_levels_chart_'.$Key]) || $_COOKIE['estats_time_levels_chart_'.$Key] != 'true')?' checked="checked"':'').' title="'.e_i18n ('Show / hide levels of maximum, average and minimum').'" tabindex="'.(++$TabIndex).'" />':' '),
	'switch' => ($ThemeConfig['SimpleCharts']?'':'<script type="text/javascript">
levelsShowHide (\''.$Key.'\');
</script>
'),
	'lang_summary' => e_i18n ('Summary')
	));
        }
if (!$ChartsAmount) {
   $Information[] = array (e_i18n ('No data to display!'), 'error');
   $Theme['title'] = e_i18n ('Time statistics');
   }
$Theme['lang_chartsview'] = e_i18n ('View of visits charts');
$Theme['lang_sum'] = e_i18n ('Sum');
$Theme['lang_most'] = e_i18n ('Most');
$Theme['lang_average'] = e_i18n ('Average');
$Theme['lang_least'] = e_i18n ('Least');
?>