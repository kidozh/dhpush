<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class DHPushHook{

    public static function get_api_hook($getPlugin = ''){
        global $_G;
        $data = unserialize($_G['setting']['mobileapihook']);
        if ($getPlugin) {
            foreach ($data as $key => $hookNames) {
                foreach ($hookNames as $hookName => $plugins) {
                    foreach ($plugins as $plugin => $value) {
                        if ($getPlugin != $plugin) {
                            unset($data[$key][$hookName][$plugin]);
                        }
                    }
                }
            }
        }
        return $data;
    }

    public static function update_api_hook($datas) {
        $apihook = self::get_api_hook();
        error_log(print_r($apihook, TRUE));
        foreach ($datas as $data) {
            foreach ($data as $key => $value) {
                if (!$value['plugin']) {
                    continue;
                }
                list($module, $hookname) = explode('_', $key);
                if ($value['include'] && $value['class'] && $value['method']) {
                    $v = $value;
                    error_log(print_r($v, TRUE));
                    unset($v['plugin']);
                    $v['allow'] = 1;
                    $apihook[$module][$hookname][$value['plugin']] = $v;
                } else {
                    unset($apihook[$module][$hookname][$value['plugin']]);
                }
            }
        }
        $settings = array('mobileapihook' => serialize($apihook));
        error_log(print_r($apihook, TRUE));
        C::t('common_setting')->update_batch($settings);
        updatecache('setting');
        return $apihook;
    }

    public static function delete_api_hook($getplugin) {
        if (!$getplugin) {
            return;
        }
        $getplugins = (array) $getplugin;
        $apihook = self::get_api_hook();
        foreach ($apihook as $key => $hooknames) {
            foreach ($hooknames as $hookname => $plugins) {
                foreach ($plugins as $plugin => $value) {
                    if (in_array($plugin, $getplugins)) {
                        unset($apihook[$key][$hookname][$plugin]);
                    }
                }
            }
        }
        $settings = array('mobileapihook' => serialize($apihook));
        C::t('common_setting')->update_batch($settings);
        updatecache('setting');
        return $apihook;
    }

    public static function register_all_hooks(){
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
                array('viewthread_variables' =>
                    array('plugin' => 'dhpush',
                        'include' => 'variables.class.php',
                        'class' => 'Variable',
                        'method' => 'viewthread_variables',
                        'variables' => 'useip'
                    )
                ),
            )
        );
    }
}