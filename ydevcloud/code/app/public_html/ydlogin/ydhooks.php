<?php

/**
 * 该文件为系统提供hook机制
 * @author liizii
 * @since 2009-9-1
 */
final class YDHook {

    const HOOK_LOGIN_FAIL = "HOOK_LOGIN_FAIL";
    const HOOK_LOGIN_SUCCESS = "HOOK_LOGIN_SUCCESS";

    private static $listeners = array();

    /**
     * 增加hook
     */
    public static function add_hook($event, $func_name, $object = null) {
        self::$listeners [$event] [] = array(
            "function" => $func_name,
            "object" => $object
        );
    }

    public static function do_hook($filter_name, $data = array()) {
        if (!self::has_hook($filter_name))
            return $data;
        foreach (self::$listeners [$filter_name] as $listeners) {
            if (is_object($listeners ['object'])) {
                $data = call_user_func(array($listeners ['object'], $listeners ['function']), $data);
            } else {
                $data = call_user_func($listeners ['function'], $data);
            }
        }
        return $data;
    }

    public static function has_hook($filter_name) {
        return @self::$listeners [$filter_name];
    }

    public static function allhooks() {
        return self::$listeners;
    }

    public static function include_files($dir) {
        if (!file_exists($dir))
            return;
        foreach (glob($dir . "/*") as $file) {
            if (is_dir($file)) {
                self::include_hooks($file);
            } else {
                require_once $file;
            }
        }
    }

}

?>