<?php
$Schema = array (
	'configuration' => '(`name` VARCHAR(50) NOT NULL, `value` VARCHAR(200) NOT NULL, `mode` TINYINT(1) NOT NULL, PRIMARY KEY(`name`))',
	'logs' => '(`time` DATETIME NOT NULL, `log` SMALLINT(6) NOT NULL, `info` VARCHAR(100))',
	'time' => '(`time` DATETIME NOT NULL, `views` INT(11) NOT NULL, `unique` INT(11) NOT NULL, `returns` INT(11) NOT NULL, PRIMARY KEY (`time`))',
	'visitors' => '(`id` INT(11) NOT NULL, `firstvisit` DATETIME NOT NULL, `lastvisit` DATETIME NOT NULL, `visitsamount` INT(5) NOT NULL, `ip` VARCHAR(20) NOT NULL, `useragent` VARCHAR(250), `host` VARCHAR(200), `referrer` VARCHAR(250), `language` VARCHAR(5), `javascript` TINYINT(1), `cookies` TINYINT(1), `flash` VARCHAR(5), `java` TINYINT(1), `screen` VARCHAR(12), `info` TINYINT(1), `robot` VARCHAR(20), `proxy` VARCHAR(100), `proxyip` VARCHAR(20), `previous` INTEGER, PRIMARY KEY(`id`))',
	'details' => '(`id` INT(11) NOT NULL, `address` VARCHAR(200) NOT NULL, `time` DATETIME NOT NULL)',
	'ignored' => '(`lastview` DATETIME NOT NULL, `lastvisit` DATETIME NOT NULL, `firstvisit` DATETIME NOT NULL, `ip` VARCHAR(20) NOT NULL, `unique` INT(11) NOT NULL, `views` INT(11) NOT NULL, `useragent` VARCHAR(250) NOT NULL, `type` TINYINT(1) NOT NULL, PRIMARY KEY(`ip`, `type`))',
	'geoip' => '(`time` DATETIME NOT NULL, `city` VARCHAR(50) NOT NULL, `region` VARCHAR(50) NOT NULL, `country` VARCHAR(10) NOT NULL, `continent` VARCHAR(10) NOT NULL, `latitude` DOUBLE, `longitude` DOUBLE, `amount` INT(11), PRIMARY KEY(`city`, `country`))',
	'browsers' => '(`time` DATETIME NOT NULL, `name` VARCHAR(50) NOT NULL, `amount` INT(11) NOT NULL, `version` VARCHAR(20) NOT NULL, PRIMARY KEY(`time`, `name`, `version`))',
	'oses' => '(`time` DATETIME NOT NULL, `name` VARCHAR(50) NOT NULL, `amount` INT(11) NOT NULL, `version` VARCHAR(20) NOT NULL, PRIMARY KEY(`time`, `name`, `version`))',
	'sites' => '(`time` DATETIME NOT NULL, `name` VARCHAR(50) NOT NULL, `amount` INT(11) NOT NULL, `address` VARCHAR(200) NOT NULL, PRIMARY KEY(`time`, `name`, `address`))'
	);
$DBTables = array ('cookies', 'flash', 'hosts', 'java', 'javascript', 'keywords', 'languages', 'proxy', 'referrers', 'robots', 'screens', 'websearchers');
for ($i = 0; $i < count ($DBTables); ++$i) $Schema[$DBTables[$i]] = '(`time` DATETIME NOT NULL, `name` VARCHAR(200) NOT NULL, `amount` INT(11) NOT NULL, PRIMARY KEY(`time`, `name`))';
?>