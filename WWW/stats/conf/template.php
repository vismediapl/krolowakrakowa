<?php
$Array = array (
	'Stats' => array (
		'Antipixel' => array ('default/silver.png', 4),
		'BlacklistMonitor' => array (1, 1),
		'CountPhrases' => array (0, 1),
		'CountRobots' => array (1, 1),
		'IgnoredIPs' => array ('127.0.0.1', 3),
		'Keywords' => array ('', 3),
		'LogEnabled' => array (1, 1),
		'LogFile' => array (0, 1),
		'Referrers' => array ('localhost', 3),
		'TimeZone' => array ('UTC', 0),
		'VisitTime' => array (86400, 2),
		'Backups' => array (
			'profile' => array ('full', 4),
			'replacedata' => array (1, 1),
			'tablesstructure' => array (0, 1),
			'time' => array (0, 0),
			'usertables' => array ('', 3)
			),
		'CollectData' => array (
			'browsers' => array (1, 1),
			'cookies' => array (1, 1),
			'details' => array (1, 1),
			'flash' => array (1, 1),
			'geoip' => array (1, 1),
			'hosts' => array (1, 1),
			'java' => array (1, 1),
			'javascript' => array (1, 1),
			'keywords' => array (1, 1),
			'languages' => array (1, 1),
			'oses' => array (1, 1),
			'proxy' => array (1, 1),
			'referrers' => array (1, 1),
			'robots' => array (1, 1),
			'screens' => array (1, 1),
			'sites' => array (1, 1),
			'time' => array (1, 1),
			'websearchers' => array (1, 1)
			),
		'CollectFrequency' => array (
			'browsers' => array ('monthly', 4),
			'cookies' => array ('monthly', 4),
			'flash' => array ('monthly', 4),
			'geoip' => array ('monthly', 4),
			'hosts' => array ('monthly', 4),
			'java' => array ('monthly', 4),
			'javascript' => array ('monthly', 4),
			'keywords' => array ('monthly', 4),
			'languages' => array ('monthly', 4),
			'oses' => array ('monthly', 4),
			'proxy' => array ('monthly', 4),
			'referrers' => array ('monthly', 4),
			'robots' => array ('monthly', 4),
			'screens' => array ('monthly', 4),
			'sites' => array ('monthly', 4),
			'time' => array ('hourly', 4),
			'websearchers' => array ('monthly', 4)
			)
		),
	'GUI' => array (
		'BlockedIPs' => array ('', 3),
		'ChartsType' => array ('html', 4),
		'CheckVersionTime' => array (600, 2),
		'DefaultLanguage' => array ('en', 4),
		'DefaultTheme' => array ('Silver', 4),
		'GDEnabled' => array (1, 1),
		'Header' => array ('{lang_statsfor} <a href="http://{servername}" tabindex="{tabindex}">{servername}</a><br />
<small>{lang_collectedfrom} {startdate}</small>', 0),
		'MapLink' => array ('http://maps.google.com/maps?q={latitude},+{longitude}&iwloc=A&hl={language}', 0),
		'Pass' => array ('', 0),
		'WhoisLink' => array ('http://www.ripe.net/fcgi-bin/whois?searchtext={data}', 0),
		'DBCache' => array (
			'detailed' => array (3, 2),
			'others' => array (15, 2),
			'time' => array (15, 2)
			),
		'Detailed' => array (
			'amount' => array (30, 2),
			'compactold' => array (1, 1),
			'detailsamount' => array (30, 2),
			'keepalldata' => array (1, 1),
			'maxpages' => array (10, 2),
			'period' => array (30, 2)
			),
		'GroupAmount' => array (
			'browsers' => array (15, 2),
			'cities' => array (15, 2),
			'continents' => array (10, 2),
			'cookies' => array (3, 2),
			'countries' => array (15, 2),
			'flash' => array (10, 2),
			'hosts' => array (15, 2),
			'java' => array (3, 2),
			'javascript' => array (3, 2),
			'keywords' => array (15, 2),
			'languages' => array (15, 2),
			'oses' => array (15, 2),
			'proxy' => array (15, 2),
			'referrers' => array (15, 2),
			'regions' => array (15, 2),
			'robots' => array (15, 2),
			'screens' => array (15, 2),
			'sites' => array (30, 2),
			'browsersversions' => array (15, 2),
			'osesversions' => array (15, 2),
			'websearchers' => array (15, 2)
			),
		'Path' => array (
			'mode' => array (0, 4),
			'prefix' => array ('index.php?vars=', 0),
			'separator' => array ('&amp;', 0),
			'suffix' => array ('', 0)
			)
		)
	);
$ArraySelects['Backups']['profile'] = array ('data', 'full', 'user');
$ArraySelects['ChartsType'] = array ('areas', 'bars', 'html', 'lines');
$ArraySelects['Path']['mode'] = range (0, 2);
foreach ($Array['Stats']['CollectFrequency'] as $Key => $Value) $ArraySelects['CollectFrequency'][$Key] = array ('yearly', 'monthly', 'daily', 'hourly', 'none');
$ArraySelects['CollectFrequency']['time'] = array ('daily', 'hourly');
?>