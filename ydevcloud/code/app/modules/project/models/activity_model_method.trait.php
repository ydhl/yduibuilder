<?php
namespace app\project;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use function yangzie\__;

/**
 *
 *
 * @version $Id$
 * @package project
 */
trait Activity_Model_Method{
    public static function get_types(){
        return [
          'ui'=>__('UI'),
          'dev'=>__('Dev'),
          'member'=>__('Member')
        ];
    }
}?>
