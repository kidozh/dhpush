<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function renderPhpFile($filename, $vars = null) {
    if (is_array($vars) && !empty($vars)) {
      extract($vars);
    }
    ob_start();
    include $filename;
    return ob_get_clean();
  }

class plugin_dhpush{

    function __construct(){
        global $_G;
		if(!$_G['uid']) {
			return;
		}
    }

    

    // embledding footer
    function global_footer(){
        // need to declear it before use!!
        global $_G;
        //return "GLOBAL HEADER";
        if(!$_G['uid']) {
            // not login so nothing happened
            //include  template("dhpush:firebase_push");
            return "";
        }
        else{
            //require './template/firebase_push.htm';
            // start to embled page into main
            $args = [
                "formhash" => formhash()
            ];
            // directly render from console
            return renderPhpFile(__DIR__."/template/firebase_push.php", $args);
        }
        
    }
}

