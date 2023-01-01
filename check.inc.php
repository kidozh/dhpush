<?php

if (!defined('IN_DISCUZ')) {

    exit('Access Denied');
}

// ensure global is enabled

global $_G;

$config = $_G['cache']['plugin']['dhpush'];

echo $config["checkCode"];