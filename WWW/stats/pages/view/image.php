<?php
if (!defined ('eStats')) die ();
if (!isset ($_GET['id']) || !isset ($_SESSION['eStats']['imagedata'][$_GET['id']])) die ('No data!');
if (!include ('./lib/gd.php')) die ('Could not load file <em>lib/gd.php</em>!');
if (!e_gd_available ()) die ('GD extension unavailable!');
header ('Expires: '.gmdate ('r', 0));
header ('Last-Modified: '.gmdate ('r'));
header ('Cache-Control: no-store, no-cache, must-revalidate');
header ('Pragma: no-cache');
if ($_SESSION['eStats']['imagedata'][$_GET['id']]['type'] == 'map') {
   $MapType = explode ('-', $_SESSION['eStats']['imagedata'][$_GET['id']]['map']);
   $FileName = 'cache/map-'.$MapType[0].(($MapType[0] == 'world')?'-'.$MapType[1]:'').'-'.$_SESSION['eStats']['language'];
   e_image_cache ($FileName, $DBCache['others']);
   e_map ($MapType, $_SESSION['eStats']['imagedata'][$_GET['id']]['data'], $FileName);
   }
if ($_SESSION['eStats']['imagedata'][$_GET['id']]['type'] == 'chart') {
   $FileName = 'cache/chart-'.$_GET['id'].'-'.$_SESSION['eStats']['imagedata'][$_GET['id']]['diagram'].'-'.$_SESSION['eStats']['theme'].(($_SESSION['eStats']['imagedata'][$_GET['id']]['chart'] == 'pie')?'':'-'.$_SESSION['eStats']['imagedata'][$_GET['id']]['mode']).'-'.$_SESSION['eStats']['language'];
   e_image_cache ($FileName, $_SESSION['eStats']['imagedata'][$_GET['id']]['cache']);
   if ($_SESSION['eStats']['imagedata'][$_GET['id']]['chart'] == 'pie') {
      if (strstr ($_GET['id'], 'geoip')) e_geo_init ();
      e_chart_pie ($_SESSION['eStats']['imagedata'][$_GET['id']]['diagram'], $_SESSION['eStats']['imagedata'][$_GET['id']]['data'], $FileName, $_SESSION['eStats']['imagedata'][$_GET['id']]['icons']);
      }
   if (in_array ($_SESSION['eStats']['imagedata'][$_GET['id']]['chart'], array ('areas', 'bars', 'lines'))) e_chart_time ($_SESSION['eStats']['imagedata'][$_GET['id']]['diagram'], $_SESSION['eStats']['imagedata'][$_GET['id']]['data'], $_SESSION['eStats']['imagedata'][$_GET['id']]['info'], $FileName, $_SESSION['eStats']['imagedata'][$_GET['id']]['chart'], $_SESSION['eStats']['imagedata'][$_GET['id']]['join']);
  }
die ('Wrong data!');
?>