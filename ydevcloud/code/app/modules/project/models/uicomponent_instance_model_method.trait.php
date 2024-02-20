<?php
namespace app\project;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
/**
 *
 *
 * @version $Id$
 * @package project
 */
trait Uicomponent_Instance_Model_Method{
    // 这里实现model的业务方法

    /**
     * 删除那些在页面上已经删除掉的ui绑定
     * @param $page
     * @return void
     */
    public static function remove_gone_uiid(Page_Model $page) {
        $instances = Uicomponent_Instance_Model::from()->where('page_id=:pid')->select([':pid'=>$page->id]);
        foreach ($instances as $instance){
            if(!$page->find_ui_item($instance->instance_uuid)){
                $instance->remove();
            }
        }
    }
}?>
