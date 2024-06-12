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
    public static function add_instance($page_id, $uicomponent_id, $instance_uuid) {
        $sql = new YZE_SQL();
        $sql->select('a',['id'])->from(Uicomponent_Instance_Model::CLASS_NAME, 'a')
            ->where('a','page_id','=', $page_id)
            ->where('a','uicomponent_page_id','=', $uicomponent_id)
            ->where('a','instance_uuid','=', $instance_uuid);
        
        $instance = new Uicomponent_Instance_Model();
        $instance->set('uuid', Uicomponent_Instance_Model::uuid())
            ->set('page_id', $page_id)
            ->set('uicomponent_page_id', $uicomponent_id)
            ->set('instance_uuid', $instance_uuid)
            ->save(YZE_SQL::INSERT_NOT_EXIST, $sql);
    }
}?>
