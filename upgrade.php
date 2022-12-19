<?php

error_log(print_r("Upgrading DHPush plugin...", TRUE));

# update the hook for mobile message
require_once 'dhpush.lib.class.php';

// delete and then update
DHPushHook::delete_api_hook("dhpush");

$hook = DHPushHook::get_api_hook("dhpush");
if($hook){
    DHPushHook::register_all_hooks();
}

