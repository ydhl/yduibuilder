<?php
namespace app\common;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
/**
 *
 *
 * @version $Id$
 * @package common
 */
trait Option_Model_Method{
    public static function get_option($option_name) {
        return YZE_DBAImpl::get_instance ()->lookup ( "option_value", "option", "option_name=:name", array (
            ":name" => $option_name
        ) );
    }

    public static function save_option($option_name, $option_value) {
        $dba = YZE_DBAImpl::get_instance ();
        $dba->check_Insert( "option", array (
            "created_on" => date ( "Y-m-d H:i:s" ),
            "option_name" => $option_name,
            "uuid" => self::uuid(),
            "option_value" => $option_value
        ), "select id from `option` where option_name=:oname", array (
            ":oname" => $option_name
        ), false, true, "id" );
    }

}?>
