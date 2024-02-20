<?php

namespace yangzie;

/**
 * 会话封装
 *
 * @liizii
 */
class YZE_Session_Context extends YZE_Object {
    private static $instance;
    private function __construct() {
    }

    /**
     *
     * @return YZE_Session_Context
     */
    public static function get_instance() {
        if (! isset ( self::$instance )) {
            $c = __CLASS__;
            self::$instance = new $c ();
        }
        return self::$instance;
    }

    /**
     * 在会话中设置数据
     *
     * @param $key
     * @return unknown_type
     * @author liizii
     * @since 2009-12-10
     */
    public function get($key) {
        return @$_SESSION ['yze'][$key];
    }

    /**
     * 获取设置在会话中的数据
     *
     * @param $key
     * @return unknown_type
     * @author liizii
     * @since 2009-12-10
     */
    public function set($key, $value) {
        $_SESSION ['yze'][$key] = $value;
    }
    public function has($key) {
        return array_key_exists ( $key, @$_SESSION ['yze']);
    }
    /**
     * 删除指定的key，如果不指定key则全部session都将被删除(注意只删除set设置的内容)
     *
     * @author leeboo
     * @param string $key
     *
     */
    public function destory($key = null) {
        if ($key) {
            unset ( $_SESSION ['yze'][$key] );
        } else {
            unset ( $_SESSION ['yze']);
        }
        return $this;
    }
}
?>
