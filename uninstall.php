<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE cdb_dhpush_token;

EOF;

# delete the hook for mobile message
require_once 'dhpush.lib.class.php';

DHPushHook::delete_api_hook("dhpush");



$finish = TRUE;