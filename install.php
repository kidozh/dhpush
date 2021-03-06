<?php

/* Licensed with MIT */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF
DROP TABLE IF EXISTS cdb_dhpush_token;
CREATE TABLE cdb_dhpush_token (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) unsigned NOT NULL,
  `username` varchar(15) NOT NULL DEFAULT '',
  `token` TEXT NOT NULL DEFAULT '',
  `allowPush` BOOLEAN NOT NULL DEFAULT true ,
  `deviceName` TEXT NOT NULL DEFAULT '',
  `packageId` TEXT NOT NULL DEFAULT '',
  `channel` varchar(255) NOT NULL DEFAULT '',
  `updateAt` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) TYPE=MyISAM;
EOF;

runquery($sql);

$finish = TRUE;

?>