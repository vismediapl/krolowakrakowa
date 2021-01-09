<?php
error_reporting (0);
if (isset ($_GET['count'])) {
   if ($_GET['count']) {
      define ('ESTATS_COUNT', 1);
      if (isset ($_GET['address'])) {
         $Address = parse_url ($_GET['address']);
         define ('ESTATS_ADDRESS', $Address['path'].(isset ($Address['query'])?'?'.$Address['query']:''));
         }
      if (isset ($_GET['title'])) define ('ESTATS_TITLE', $_GET['title']);
      }
   define ('ESTATS_GETINFO', 1);
   $Stats = array (
	'javascript' => ((isset ($_GET['javascript']) && $_GET['javascript'] == 1)?1:0),
	'cookies' => ((isset ($_GET['cookies']) && is_numeric ($_GET['cookies']))?(int) $_GET['cookies']:'?'),
	'flash' => ((isset ($_GET['flash']) && is_numeric ($_GET['flash']))?(int) $_GET['flash']:'?'),
	'java' => ((isset ($_GET['java']) && is_numeric ($_GET['java']))?(int) $_GET['java']:'?'),
	'screen' => ((isset ($_GET['width']) && (int) $_GET['width'] && isset ($_GET['height']) && (int) $_GET['height'])?((int) $_GET['width']).' x '.((int) $_GET['height']):'?')
	);
   }
require ('./stats.php');
$FileName = 'share/antipixels/'.((isset ($_GET['antipixel']) && $_GET['antipixel'] && is_file ('share/antipixels/'.urldecode ($_GET['antipixel'])))?urldecode ($_GET['antipixel']):$Antipixel);
$TmpArray = explode ('.', basename ($FileName));
header ('Content-type: image/'.end ($TmpArray));
die (file_get_contents ($FileName));
?>