<?php
if (!defined ('eStats') || $Pass) die ();
$Amounts = array (
	'daily' => 20,
	'weekly' => 10,
	'monthly' => 5
	);
$Diagrams = array (
	'daily' => array ('24hours', 'hour'),
	'weekly' => array ('week', 'day'),
	'monthly' => array ('month', 'day'),
	);
$Groups = array (
	'daily' => array (),
	'weekly' => array (
		'sites' => 20,
		'keywords' => 15,
		'osesversions' => 15,
		'browsers' => 15
		),
	'monthly' => array (
		'sites' => 30,
		'keywords' => 20,
		'osesversions' => 20,
		'browsers' => 20
		),
	);
$TimeTypes = array ('unique', 'returns', 'views');
$Types = array ();
if (isset ($Vars[3]) && $Vars[3]) {
   $TmpTypes = explode ('+', $Vars[3]);
   for ($i = 0, $c = count ($TmpTypes); $i < $c; ++$i) {
       if (isset ($Amounts[$TmpTypes[$i]]) && $Amounts[$TmpTypes[$i]] && !in_array ($TmpTypes[$i], $Types)) $Types[] = $TmpTypes[$i];
       }
   }
if (!$Types) $Types = array ('daily', 'weekly', 'monthly');
$Feeds = array (
	'daily' => mktime (0, 0, 0, date ('n'), (date ('j') - 1), date ('Y')),
	'weekly' => (mktime (0, 0, 0, date ('n'), date ('j'), date ('Y')) - (date ('w') * 86400)),
	'monthly' => strtotime (date ('Y-m-t 00:00', strtotime ('last month')))
	);
$Updated = 0;
$FeedArray = array ();
foreach ($Feeds as $Key => $Value) {
        if (!$Amounts[$Key] || !in_array ($Key, $Types)) continue;
        $Modified = 0;
        $FileName = 'cache/feed-'.$Key;
        $Data = e_data_read ($FileName);
        $NewData = array ();
        $TimeStamp = $Feeds[$Key];
        for ($i = 0; $i < $Amounts[$Key]; ++$i) {
            if ($TimeStamp > $Updated) $Updated = $TimeStamp;
            switch ($Key) {
                   case 'daily':
                   $Step = 86400;
                   break;
                   case 'weekly':
                   $Step = 604800;
                   break;
                   case 'monthly':
                   $Step = (date ('t', $TimeStamp) * 86400);
                   }
            if (!isset ($NewData[$TimeStamp])) {
               $Modified = 1;
               $NewData[$TimeStamp]['summary'] = e_summary (($TimeStamp - $Step), $TimeStamp);
               if ($Groups[$Key]) {
                  $NewData[$TimeStamp]['tables'] = array ();
                  foreach ($Groups[$Key] as $Table => $Amount) $NewData[$TimeStamp]['tables'][$Table] = $DB->data ($Table, $Amount, ($TimeStamp - $Step), $TimeStamp);
                  }
               $NewData[$TimeStamp]['time'] = $DB->time ($Diagrams[$Key][1], ($TimeStamp - $Step), $TimeStamp, array ('unique', 'views', 'returns'));
               }
            else $NewData[$TimeStamp] = &$Data[$TimeStamp];
            $Date = date ('Y-m-d\TH:i:s\Z', $TimeStamp);
            $Title = sprintf (e_i18n (ucfirst ($Key).' visits summary for %s.'), date ('Y-m-d', $TimeStamp));
            $Summary = sprintf (e_i18n ($NewData[$TimeStamp]['summary']['views']?'Between %s and %s there were %d unique visits (%d views), which %d were returns.':'Between %s and %s there were no visits.'), date ('Y-m-d H:00', ($TimeStamp - $Step)), date ('Y-m-d H:i', $TimeStamp), $NewData[$TimeStamp]['summary']['unique'], $NewData[$TimeStamp]['summary']['views'], $NewData[$TimeStamp]['summary']['returns']);
            $Content = '<h1>
'.e_i18n ('Summary').'
</h1>
'.$Summary.'
';
            if ($Groups[$Key]) {
               foreach ($NewData[$TimeStamp]['tables'] as $Table => $GroupData) {
                       if (!$GroupData['amount']) continue;
                       $Content.= '<h2>
'.e_i18n ($Titles[$Table]).(($GroupData['amount'] && $GroupData['amount'] != (($Groups[$Key][$Table] > $GroupData['amount'])?$GroupData['amount']:$Groups[$Key][$Table]))?' ('.count ($GroupData['data']).' '.e_i18n ('of').' '.$GroupData['amount'].')':'').'
</h2>
<ol>
';
                       foreach ($GroupData['data'] as $Name => $Row) {
                               $Name = trim ($Name);
                               $Address = '';
                               if ($Table == 'sites') {
                                  $Address = $Name;
                                  $Name = ($Row[1]?$Row[1]:$Name);
                                  $Row = $Row[0];
                                  }
                               if ($Table == 'referrers' && $Name != '?') $Address = $Name;
                               if ($Table == 'cities') {
                                  if ($Name && $Name != '?') $Address = e_link_map ($Row[1], $Row[2]);
                                  $Row = $Row[0];
                                  }
                               if ($Table == 'countries' && $Name != '?') $Address = 'http://{servername}{path}geoip/'.$Name.'/'.implode ('-', $GLOBALS['Date']).'{suffix}';
                               $String = e_item_title ($Table, $Name);
                               $Content.= '<li>
'.($Address?'<a href="'.htmlspecialchars ($Address).'" title="'.$String.'">
':'').str_replace ('{', '{', $String).($Address?'
</a>':'').' - <em>'.$Row.' ('.round ((($Row / $GroupData['sum']) * 100), 2).'%)</em>
</li>
';
                               }
                       if (!$GroupData['amount']) $Content.= '<li>
<strong>'.e_i18n ('None').'</strong>
</li>
';
                       $Content.= '</ol>
<strong>'.e_i18n ('Sum').': <em>'.$GroupData['sum'].'</em></strong>
';
                       }
               }
            if (($CollectFrequency['time'] == 'hourly' || $Key != '24hours') && $NewData[$TimeStamp]['time']) {
               $Content.= '<h2>
'.e_i18n ('Time').' ('.e_i18n ($Titles[$Diagrams[$Key][0]]).')
</h2>
<table cellpadding="2px" cellspacing="0" border="1px" width="100%">
<tr>
<th>
'.e_i18n ('Date').'
</th>
<th>
'.e_i18n ('Unique').'
</th>
<th>
'.e_i18n ('Views').'
</th>
<th>
'.e_i18n ('Returns').'
</th>
</tr>
';
               switch ($Key) {
                      case 'daily':
                      $Amount = 24;
                      $TimeStep = 3600;
                      $DateString = 'Y.m.d H';
                      break;
                      case 'weekly':
                      $Amount = 7;
                      $TimeStep = 86400;
                      $DateString = 'Y.m.d';
                      break;
                      case 'monthly':
                      $Amount = date ('t', $TimeStamp);
                      $TimeStep = 86400;
                      $DateString = 'Y.m.d';
                      }
               $DiagramTimeStamp = ($TimeStamp - $Step);
               for ($j = 0, $l = count ($TimeTypes); $j < $l; ++$j) $TimeSummary['sum'][$TimeTypes[$j]] = $TimeSummary['max'][$TimeTypes[$j]] = $TimeSummary['min'][$TimeTypes[$j]] = 0;
               for ($j = 0; $j < $Amount; ++$j) {
                   $DiagramTimeStamp += $TimeStep;
                   $UnitID = date ($DateString, $DiagramTimeStamp);
                   $Content.= '<tr>
<td>
<em>'.$UnitID.'</em>
</td>
';
                   for ($k = 0; $k < 3; ++$k) {
                       if (!isset ($NewData[$TimeStamp]['time'][$UnitID][$TimeTypes[$k]])) $NewData[$TimeStamp]['time'][$UnitID][$TimeTypes[$k]] = 0;
                       $TimeSummary['sum'][$TimeTypes[$k]] += $NewData[$TimeStamp]['time'][$UnitID][$TimeTypes[$k]];
                       if ($TimeSummary['max'][$TimeTypes[$k]] < $NewData[$TimeStamp]['time'][$UnitID][$TimeTypes[$k]]) $TimeSummary['max'][$TimeTypes[$k]] = $NewData[$TimeStamp]['time'][$UnitID][$TimeTypes[$k]];
                       if ($TimeSummary['min'][$TimeTypes[$k]] > $NewData[$TimeStamp]['time'][$UnitID][$TimeTypes[$k]]) $TimeSummary['min'][$TimeTypes[$k]] = $NewData[$TimeStamp]['time'][$UnitID][$TimeTypes[$k]];
                       $Content.= '<td>
'.$NewData[$TimeStamp]['time'][$UnitID][$TimeTypes[$k]].'
</td>
';
                       }
                       $Content.= '</tr>
';
                   }
               $Content.= '<tr>
<th>
'.e_i18n ('Sum').':
</th>
';
                   for ($k = 0, $l = count ($TimeTypes); $k < $l; ++$k) $Content.= '<td>
'.$TimeSummary['sum'][$TimeTypes[$k]].'
</td>
';
                   $Content.= '</tr>
<tr>
<th>
'.e_i18n ('Most').':
</th>
';
                   for ($k = 0, $l = count ($TimeTypes); $k < $l; ++$k) $Content.= '<td>
'.$TimeSummary['max'][$TimeTypes[$k]].'
</td>
';
                   $Content.= '</tr>
<tr>
<th>
'.e_i18n ('Average').':
</th>
';
                   for ($k = 0, $l = count ($TimeTypes); $k < $l; ++$k) $Content.= '<td>
'.round (($TimeSummary['sum'][$TimeTypes[$k]] / $Amount), 2).'
</td>
';
                   $Content.= '</tr>
<tr>
<th>
'.e_i18n ('Least').':
</th>
';
                   for ($k = 0, $l = count ($TimeTypes); $k < $l; ++$k) $Content.= '<td>
'.$TimeSummary['min'][$TimeTypes[$k]].'
</td>
';
                   $Content.= '</tr>
</table>
';
               }
            $FeedArray[$TimeStamp.'-'.$Key] = '<entry>
<title type="text">
'.$Title.'
</title>
<summary type="text">
'.$Summary.'
</summary>
<content type="xhtml">
<div xmlns="http://www.w3c.org/1999/xhtml">
'.$Content.'</div>
</content>
<id>http://{servername}{path}feed/'.$Key.'/'.$TimeStamp.'{suffix}</id>
<updated>'.$Date.'</updated>
<author>
<name>eStats</name>
</author>
</entry>
';
            $TimeStamp -= $Step;
            }
        if ($Modified) e_data_save ($FileName, $NewData);
        }
krsort ($FeedArray);
header ('Content-type: application/atom+xml; charset=utf-8');
die (e_string_parse ('<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="{lang}">
<title type="text">
eStats :: '.e_i18n ('Feed channel for {servername}').'
</title>
<subtitle type="text">
'.e_i18n ('Short summary of collected data from 24 hours, week or month.').'
</subtitle>
<id>http://{servername}{path}feed/'.implode ('+', $Types).'</id>
<icon>http://{servername}{datapath}share/icons/misc/estats.png</icon>
<generator uri="http://estats.emdek.cba.pl/">eStats</generator>
<updated>'.date ('Y-m-d\TH:i:s\Z', $Updated).'</updated>
<link rel="alternate" type="text/html" href="http://{servername}{path}" />
<link rel="self" type="application/atom+xml" href="http://{servername}{path}feed" />
'.implode ('', $FeedArray).'</feed>', $Theme));
?>