<?php

# update the hook for mobile message
require_once 'dhpush.lib.class.php';

$hook = DHPushHook::get_api_hook("dhpush");
if($hook){
    DHPushHook::update_api_hook(
        array(
            # plugin array starts here
            array('sendreply_variables' =>
                array('plugin' => 'dhpush',
                    'include' => 'dhpush.class.php',
                    'class' => 'plugin_dhpush_forum',
                    'method' => 'sendreply_variables'
                )
            ),
        )
    );
}

