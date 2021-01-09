<?php
$Schema = array (
	'configuration' => '("name" TEXT, "value" TEXT, "mode" INTEGER, PRIMARY KEY("name"))',
	'logs' => '("time" TEXT, "log" INTEGER, "info" TEXT)',
	'time' => '("time" TEXT, "views" INTEGER, "unique" INTEGER, "returns" INTEGER, PRIMARY KEY("time"))',
	'visitors' => '("id" INTEGER, "firstvisit" TEXT, "lastvisit" TEXT, "visitsamount" INTEGER, "ip" TEXT, "useragent" TEXT, "host" TEXT, "referrer" TEXT, "language" TEXT, "javascript" INTEGER, "cookies" INTEGER, "flash" TEXT, "java" INTEGER, "screen" TEXT, "info" INTEGER, "robot" TEXT, "proxy" TEXT, "proxyip" TEXT, "previous" INTEGER, PRIMARY KEY("id"))',
	'details' => '("id" INTEGER, "address" TEXT, "time" TEXT)',
	'ignored' => '("lastview" TEXT, "lastvisit" TEXT, "firstvisit" TEXT, "ip" TEXT, "unique" INTEGER, "views" INTEGER, "useragent" TEXT, "type" INTEGER, PRIMARY KEY("ip", "type"))',
	'geoip' => '("time" TEXT, "city" TEXT, "region" TEXT, "country" TEXT, "continent" TEXT, "latitude" DOUBLE, "longitude" DOUBLE, "amount" INTEGER, PRIMARY KEY("city", "country"))',
	'browsers' => '("time" TEXT, "name" TEXT, "amount" INTEGER, "version" TEXT, PRIMARY KEY("time", "name", "version"))',
	'oses' => '("time" TEXT, "name" TEXT, "amount" INTEGER, "version" TEXT, PRIMARY KEY("time", "name", "version"))',
	'sites' => '("time" TEXT, "name" TEXT, "amount" INTEGER, "address" TEXT, PRIMARY KEY("time", "name", "address"))'
	);
$DBTables = array ('cookies', 'flash', 'hosts', 'java', 'javascript', 'keywords', 'languages', 'proxy', 'referrers', 'robots', 'screens', 'websearchers');
for ($i = 0; $i < count ($DBTables); ++$i) $Schema[$DBTables[$i]] = '("time" TEXT, "name" TEXT, "amount" INTEGER, PRIMARY KEY("time", "name"))';
?>