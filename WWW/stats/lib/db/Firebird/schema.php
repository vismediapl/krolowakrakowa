<?php
$Schema = array (
	'configuration' => '("name" VARCHAR(50), "value" VARCHAR(200), "mode" SMALLINT, PRIMARY KEY("name"))',
	'logs' => '("time" TIMESTAMP, "log" SMALLINT, "info" VARCHAR(100))',
	'time' => '("time" TIMESTAMP, "views" INT, "unique" INT, "returns" INT, PRIMARY KEY("time"))',
	'visitors' => '("id" INT, "firstvisit" TIMESTAMP, "lastvisit" TIMESTAMP, "visitsamount" INT, "ip" VARCHAR(20), "useragent" VARCHAR(250), "host" VARCHAR(200), "referrer" VARCHAR(250), "language" VARCHAR(5), "javascript" VARCHAR(1), "cookies" VARCHAR(1), "flash" VARCHAR(3), "java" VARCHAR(1), "screen" VARCHAR(20), "info" SMALLINT, "robot" VARCHAR(50), "proxy" VARCHAR(50), "proxyip" VARCHAR(50), "previous" INT, PRIMARY KEY("id"))',
	'details' => '("id" INT, "address" VARCHAR(200), "time" TIMESTAMP)',
	'ignored' => '("lastview" TIMESTAMP, "lastvisit" TIMESTAMP, "firstvisit" TIMESTAMP, "ip" VARCHAR(20), "unique" INT, "views" INT, "useragent" VARCHAR(250), "type" SMALLINT, PRIMARY KEY("ip", "type"))',
	'geoip' => '("time" TIMESTAMP, "city" VARCHAR(50), "region" VARCHAR(50), "country" VARCHAR(10), "continent" VARCHAR(10), "latitude" FLOAT, "longitude" FLOAT, "amount" INT, PRIMARY KEY("city", "country"))',
	'browsers' => '("time" TIMESTAMP, "name" VARCHAR(50), "amount" INT, "version" VARCHAR(20), PRIMARY KEY("time", "name", "version"))',
	'oses' => '("time" TIMESTAMP, "name" VARCHAR(50), "amount" INT, "version" VARCHAR(20), PRIMARY KEY("time", "name", "version"))',
	'sites' => '("time" TIMESTAMP, "name" VARCHAR(50), "amount" INT, "address" VARCHAR(200), PRIMARY KEY("time", "name", "address"))'
	);
$DBTables = array ('cookies', 'flash', 'hosts', 'java', 'javascript', 'keywords', 'languages', 'proxy', 'referrers', 'robots', 'screens', 'websearchers');
for ($i = 0; $i < count ($DBTables); ++$i) $Schema[$DBTables[$i]] = '("time" TIMESTAMP, "name" VARCHAR(200), "amount" INT, PRIMARY KEY("time", "name"))';
?>