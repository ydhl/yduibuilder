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
trait Module_Model_Method{
    public function function_count(){
        return Function_Model::from()->where('module_id=:id and is_deleted=0')->count('id', [':id'=>$this->id]);
    }
    public function get_functions() {
        return Function_Model::from()->where('module_id=:id and is_deleted=0')->select( [':id'=>$this->id]);
    }

    /**
     * 只返回type是page的页面
     * @return array
     */
    public function get_pages($type='page') {
        $where = 'module_id=:id and is_deleted=0';
        $param = [':id'=>$this->id];
        if ($type){
            $where .= ' and page_type=:page';
            $param[':page'] = $type;
        }
        return Page_Model::from()
            ->where($where)->select($param);
    }
    public function page_count() {
        return Page_Model::from()
            ->where('module_id=:id and is_deleted=0')->count('id', [':id'=>$this->id]);
    }
    public function remove(){
        YZE_DBAImpl::get_instance()->update(Page_Model::TABLE, 'is_deleted=1', 'module_id=:id',[':id'=>$this->id]);
        YZE_DBAImpl::get_instance()->update('`'.Function_Model::TABLE.'`', 'is_deleted=1', 'module_id=:id',[':id'=>$this->id]);
        $this->set('is_deleted', 1)->save();
    }
}?>
