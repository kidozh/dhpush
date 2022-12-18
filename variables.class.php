<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once libfile('function/misc');

class Variable{
    function viewthread_variables(&$variables){
        global $_G;
        // add ip config
        $variables['postlist'] = array_values($variables['postlist']);
        foreach($variables['postlist'] as $key => $post) {
            // get user ip
            $pid = $post["pid"];
            $userResult = DB::fetch_first("SELECT useip FROM " . DB::table("forum_post") . " WHERE pid=" . $pid);
            $ipAddr = $userResult["useip"];
            $ipLocation = convertip($ipAddr);
            $variables['postlist'][$key]["ipLocation"] = $ipLocation;
            error_log(print_r($ipLocation, TRUE));
        }


    }
}