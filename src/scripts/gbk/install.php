<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$sql = <<<EOF
CREATE TABLE IF NOT EXISTS `pre_custom_emoji` (
  `uid` bigint(20) NOT NULL,
  `url` varchar(255) NOT NULL,
  `createtime` bigint(20) NOT NULL,
  KEY `url` (`url`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=gbk COLLATE=gbk_chinese_ci;
EOF;

runquery($sql);

$finish = true;
