<?php
error_reporting (0);
ignore_user_abort (1);
if (!session_id ()) session_start ();
define ('ESTATS_PATH', dirname (__FILE__).'/');
function e_error_message ($Error, $File, $Line, $NotFile = 0, $Warning = 0) {
	if (!$Warning && !defined ('ESTATS_CRITICAL')) define ('ESTATS_CRITICAL', 1);
	echo '<b>eStats '.($Warning?'warning':'error').':</b> <i>'.($NotFile?$Error:'Could not load file: <b>'.$Error.'</b>!').'</i> (<b>'.$File.': '.$Line.'</b>)<br />
';
	}
$DB = 0;
if (defined ('ESTATS_COUNT') || defined ('ESTATS_MINISTATS') || defined ('ESTATS_GETINFO')) {
   if (!include (ESTATS_PATH.'conf/config.php')) e_error_message ('conf/config.php', __FILE__, __LINE__);
   if (!include (ESTATS_PATH.'lib/common.php')) e_error_message ('lib/common.php', __FILE__, __LINE__);
   if (isset ($DBType) && !include (ESTATS_PATH.'lib/db/'.$DBType.'/common.php')) e_error_message ('lib/db/'.$DBType.'/common.php', __FILE__, __LINE__);
   if (defined ('ESTATS_MINISTATS')) {
      $GUILoaded = 1;
      if (!include (ESTATS_PATH.'lib/db/'.$DBType.'/gui.php')) {
         e_error_message ('lib/db/'.$DBType.'/gui.php', __FILE__, __LINE__, 0, 1);
         $GUILoaded = 0;
         }
      if (!include (ESTATS_PATH.'lib/gui.php')) {
         e_error_message ('lib/gui.php', __FILE__, __LINE__, 0, 1);
         $GUILoaded = 0;
         }
      }
   if (!defined ('ESTATS_CRITICAL')) {
      if (defined ('ESTATS_MINISTATS') && $GUILoaded) $DB = new estats_db_gui (1);
      else $DB = new estats_db (1);
      }
   if (!defined ('ESTATS_CRITICAL')) {
      e_config_get (0);
      e_stats_init ($DBType);
      if (defined ('ESTATS_MINISTATS')) e_config_get (1);
      if ($StatsEnabled) {
         header ('Expires: '.gmdate ('r', 0));
         header ('Last-Modified: '.gmdate ('r'));
         header ('Cache-Control: no-store, no-cache, must-revalidate');
         header ('Pragma: no-cache');
         if ($Backups['time'] && ((time () - $LastBackup > $Backups['time']))) e_backup_create ();
         if (defined ('ESTATS_COUNT') || defined ('ESTATS_GETINFO')) {
            e_ip_get ();
            if (e_ip_check (ESTATS_IP, $IgnoredIPs)) {
               if (!defined ('ESTATS_GETINFO') && $BlacklistMonitor) $DB->update_visits_ignored (ESTATS_IP);
               }
            else e_visitor ();
            }
         }
      }
   define ('ESTATS_ROBOT', e_robot ($_SERVER['HTTP_USER_AGENT']));
   }
if (defined ('ESTATS_GETINFO') && defined ('ESTATS_NOINFO') && defined ('ESTATS_VISITORID')) {
   foreach ($Stats as $Key => $Value) $DB->update (($Key.(($Key == 'screen')?'s':'')), $Value);
   $Stats['info'] = 1;
   }
else $Stats = array ('info' => 0, 'javascript' => 0, 'cookies' => 0, 'flash' => 0, 'java' => 0, 'screen' => 0);
if (!defined ('ESTATS_ADDRESS')) define ('ESTATS_ADDRESS', $_SERVER['REQUEST_URI']);
if (defined ('ESTATS_COUNT') && defined ('ESTATS_NEWVISIT')) {
   if (defined ('ESTATS_PROXY')) {
      $Stats['proxy'] = gethostbyaddr ($_SERVER['REMOTE_ADDR']);
      $Stats['proxyip'] = ESTATS_PROXYIP;
      }
   else $Stats['proxy'] = $Stats['proxyip'] = '';
   $Stats['ip'] = ESTATS_IP;
   $Stats['returned'] = (int) defined ('ESTATS_RETURNED');
   $Stats['robot'] = ESTATS_ROBOT;
   $Stats['host'] = gethostbyaddr (ESTATS_IP);
   $Stats['language'] = strtoupper (e_language_detect ());
   $Stats['useragent'] = $_SERVER['HTTP_USER_AGENT'];
   $Stats['referrer'] = (isset ($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'');
   if (!ESTATS_ROBOT) {
      if ($CollectData['languages']) $DB->update ('languages', $Stats['language']);
      if ($CollectData['browsers']) $DB->update_software ('browsers', e_browser ($_SERVER['HTTP_USER_AGENT']));
      if ($CollectData['oses']) $DB->update_software ('oses', e_os ($_SERVER['HTTP_USER_AGENT']));
      if ($Stats['proxy'] && $CollectData['proxy']) $DB->update ('proxy', $Stats['proxy']);
      if ($CollectData['hosts']) {
         $Host = explode ('.', $Stats['host']);
         $Host = (!is_numeric (((count ($Host) > 1)?$Host[count ($Host) - 2].'.':'').end ($Host))?((count ($Host) > 1)?$Host[count ($Host) - 2].'.':'').end ($Host):'?');
         $DB->update ('hosts', ($Host?$Host:'?'));
         }
      if ($CollectData['geoip']) {
         include (ESTATS_PATH.'lib/geoip.php');
         if (function_exists ('e_geo_info') && e_geo_info_available ()) {
            e_geo_init ();
            $DB->update_geoip (e_geo_info (ESTATS_IP));
            }
         }
      if ($Stats['referrer'] && ($CollectData['websearchers'] || $CollectData['keywords'])) {
         $Referrer = parse_url ($Stats['referrer']);
         if ($CollectData['referrers']) $DB->update ('referrers', (in_array ($Referrer['host'], $Referrers))?'?':'http://'.strtolower ($Referrer['host']));
         $WebSearch = e_websearcher ($Referrer, $CountPhrases);
         if ($WebSearch) {
            if ($CollectData['websearchers']) $DB->update ('websearchers', $WebSearch[0]);
            if ($CollectData['keywords']) {
               for ($i = 0, $c = count ($WebSearch[1]); $i < $c; ++$i) {
                   if ($WebSearch[1][$i]) $DB->update ('keywords', $WebSearch[1][$i]);
                   }
               }
            }
         }
      }
   else if ($CollectData['robots']) $DB->update ('robots', ESTATS_ROBOT);
   }
if (defined ('ESTATS_VISITORID')) {
   e_visit ($Stats);
   if (defined ('ESTATS_COUNT') && $CollectData['sites']) $DB->update_sites (array ((defined ('ESTATS_TITLE')?ESTATS_TITLE:0), ESTATS_ADDRESS));
   }
if ($DB && !defined ('ESTATS_MINISTATS')) $DB->disconnect ();
?>