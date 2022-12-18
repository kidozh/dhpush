<?php


if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

if(!$_G['uid']) {
    showmessage('dhpush::not_logined', NULL, array(), array('login' => 1));
}

$config = $_G['cache']['plugin']['dhpush'];

$allowPushGroupIds = (array)unserialize($config['allowPushGroups']);
$userGroupId = $_G['groupid'];

// check with group permission
if($userGroupId && in_array($userGroupId, $allowPushGroupIds)){

}
else{
    showmessage('dhpush:group_not_allowed');
}