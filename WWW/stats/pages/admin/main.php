<?php
if (!defined ('eStats')) die ();
$DBSize = $DB->db_size ();
$CacheSize = e_cache_size ();
$BackupsInfo = e_backups_info ();
$PHPExtensions = get_loaded_extensions ();
sort ($PHPExtensions);
if (function_exists ('apache_get_modules')) {
   $ApacheModules = apache_get_modules ();
   sort ($ApacheModules);
   }
else $ApacheModules = 0;
$Theme['page'] = '<h3>'.e_i18n ('Actions').'</h3>
<form action="{selfpath}" method="post">
<div class="buttons">
<input type="submit" name="statsenabled" value="'.e_i18n (($StatsEnabled?'Dis':'En').'able statistics').'" tabindex="'.(++$TabIndex).'" class="button" />
<input type="submit" name="maintenance" value="'.e_i18n (($Maintenance?'Dis':'En').'able maintenance mode').'" tabindex="'.(++$TabIndex).'" class="button" />
<input type="submit" name="editmode" value="'.e_i18n ((ESTATS_EDITMODE?'Dis':'En').'able edit mode').'" tabindex="'.(++$TabIndex).'" class="button" />
</div>
</form>
<h3>'.e_i18n ('Information').'</h3>
<p>
'.e_i18n ('<em>eStats</em> version').':
<em><a href="http://estats.emdek.cba.pl/index.php/'.$Vars[0].'/changelog/#'.ESTATS_VERSION.'" tabindex="'.(++$TabIndex).'">'.ESTATS_VERSION.' - '.ESTATS_VERSIONSTATUS.'</a> ('.date ('d.m.Y H:i:s', ESTATS_VERSIONTIME).')</em>'.($NewVersion?' (<strong>'.e_i18n ('New version is available!').' - <a href="http://estats.emdek.cba.pl/index.php/'.$Vars[0].'/changelog/#'.$_SESSION['eStats']['eVERSION'][0].'" tabindex="'.(++$TabIndex).'">'.$_SESSION['eStats']['eVERSION'][0].'</a></strong>)':'').';
</p>
<p>
'.e_i18n ('Database module').':
<em><a href="'.htmlspecialchars ($DBInfo['URL']).'" tabindex="'.(++$TabIndex).'" title="'.e_i18n ('Author').': '.$DBInfo['Author'].'">'.$DBInfo['Name'].' v'.$DBInfo['Version'].' - '.$DBInfo['Status'].'</a> ('.date ('d.m.Y H:i:s', $DBInfo['Time']).')</em>;
</p>
<p>
'.e_i18n ('Database').':
<em>'.$DBInfo['DB'].(($DBInfo['DBVersion'] != '?')?' - '.$DBInfo['DBVersion']:'').'</em>;
</p>
<p>
'.e_i18n ('PHP version').':
<em>'.PHP_VERSION.(function_exists ('phpinfo')?' (<a href="{path}admin/phpinfo" tabindex="'.(++$TabIndex).'">phpinfo</a>)':'').'</em>;
</p>
<p>
'.e_i18n ('PHP loaded extensions').':
<em>'.implode (', ', $PHPExtensions).'</em>;
</p>
<p>
'.e_i18n ('PHP safe mode').':
<em>'.((ini_get ('safe_mode') != '')?ini_get ('safe_mode'):''.e_i18n ('N/A').'').'</em>;
</p>
<p>
'.e_i18n ('Server software').':
<em>'.($_SERVER['SERVER_SOFTWARE']?htmlspecialchars ($_SERVER['SERVER_SOFTWARE']):''.e_i18n ('N/A').'').'</em>;
</p>
'.($ApacheModules?'<p>
'.e_i18n ('Apache modules').':
<em>'.implode (', ', $ApacheModules).'</em>;
</p>
':'').'<p>
'.e_i18n ('Operating system').':
<em>'.PHP_OS.'</em>;
</p>
<p>
'.e_i18n ('Server load').':
<em>'.(function_exists ('sys_getloadavg')?implode (', ', sys_getloadavg ()):''.e_i18n ('N/A').'').'</em>;
</p>
<p>
'.e_i18n ('Data collected from').':
<em>'.date ('d.m.Y H:i:s', $CollectedFrom).'</em>;
</p>
<p>
'.e_i18n ('Data size').':
<em>'.(($DBSize == '?')?'<strong>>=</strong> ':'').e_size ($DBSize + $CacheSize + $BackupsInfo['size']).' (<em title="'.e_i18n ('Data').'">'.e_size ($DBSize).'</em> / <em title="'.e_i18n ('Cache').'">'.e_size ($CacheSize).'</em> / <em title="'.e_i18n ('Backups').'">'.e_size ($BackupsInfo['size']).'</em>)</em>;
</p>
<p>
'.e_i18n ('Date of last backup creation').':
<em>'.(($BackupsInfo['amount'] && $LastBackup)?date ('d.m.Y H:i:s', $LastBackup):' - ').'</em>;
</p>
<p>
'.e_i18n ('Amount of available backups').':
<em>'.$BackupsInfo['amount'].'</em>;
</p>
<p>
'.e_i18n ('Default language').':
<em>'.$LanguageNames[$DefaultLanguage].'</em>;
</p>
<p>
'.e_i18n ('Default theme').':
<em>'.$DefaultTheme.'</em>.
</p>
';
?>