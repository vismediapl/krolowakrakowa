<?php
$Schema = array (
	'configuration' => '("name" TEXT NOT NULL, "value" TEXT, "mode" SMALLINT NOT NULL, PRIMARY KEY("name"))',
	'logs' => '("time" TIMESTAMP NOT NULL, "log" SMALLINT NOT NULL, "info" TEXT NULL)',
	'time' => '("time" TIMESTAMP NOT NULL, "views" INT NOT NULL, "unique" INT NOT NULL, "returns" INT NOT NULL, PRIMARY KEY("time"))',
	'visitors' => '("id" INT NOT NULL, "firstvisit" TIMESTAMP NOT NULL, "lastvisit" TIMESTAMP NOT NULL, "visitsamount" INT NOT NULL, "ip" TEXT NOT NULL, "useragent" TEXT NULL, "host" TEXT NULL, "referrer" TEXT NULL, "language" TEXT NULL, "javascript" SMALLINT NULL, "cookies" SMALLINT NULL, "flash" TEXT NULL, "java" SMALLINT NULL, "screen" TEXT NULL, "info" SMALLINT NULL, "robot" TEXT NULL, "proxy" TEXT NULL, "proxyip" TEXT NULL, "previous" INT NULL, PRIMARY KEY("id"))',
	'details' => '("id" INT NOT NULL, "address" TEXT NOT NULL, "time" TIMESTAMP NOT NULL)',
	'ignored' => '("lastview" TIMESTAMP NOT NULL, "lastvisit" TIMESTAMP NOT NULL, "firstvisit" TIMESTAMP NOT NULL, "ip" TEXT NOT NULL, "unique" INT NOT NULL, "views" INT NOT NULL, "useragent" TEXT NOT NULL, "type" SMALLINT NOT NULL, PRIMARY KEY("ip", "type"))',
	'geoip' => '("time" TIMESTAMP NOT NULL, "city" TEXT NOT NULL, "region" TEXT NOT NULL, "country" TEXT NOT NULL, "continent" TEXT NOT NULL, "latitude" FLOAT NOT NULL, "longitude" FLOAT NOT NULL, "amount" INT NOT NULL, PRIMARY KEY("city", "country"))',
	'browsers' => '("time" TIMESTAMP NOT NULL, "name" TEXT NOT NULL, "amount" INT NOT NULL, "version" TEXT NOT NULL, PRIMARY KEY("time", "name", "version"))',
	'oses' => '("time" TIMESTAMP NOT NULL, "name" TEXT NOT NULL, "amount" INT NOT NULL, "version" TEXT NOT NULL, PRIMARY KEY("time", "name", "version"))',
	'sites' => '("time" TIMESTAMP NOT NULL, "name" TEXT NOT NULL, "amount" INT NOT NULL, "address" TEXT NOT NULL, PRIMARY KEY("time", "name", "address"))'
	);
$DBTables = array ('cookies', 'flash', 'hosts', 'java', 'javascript', 'keywords', 'languages', 'proxy', 'referrers', 'robots', 'screens', 'websearchers');
for ($i = 0; $i < count ($DBTables); ++$i) $Schema[$DBTables[$i]] = '("time" TIMESTAMP NOT NULL, "name" TEXT NOT NULL, "amount" INT NOT NULL, PRIMARY KEY("time", "name"))';
?>