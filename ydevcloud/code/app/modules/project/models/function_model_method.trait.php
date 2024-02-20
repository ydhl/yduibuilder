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
trait Function_Model_Method{
    /**
     * 获取页面（不包含popup，master）
     * @return array
     */
    public function get_pages() {
        return Page_Model::from()->where('function_id=:id and is_deleted=0 and page_type="page"')->select([':id'=>$this->id]);
    }

    public function get_last_modified_page() {
        return Page_Model::from()->where('function_id=:id and is_deleted=0')->order_By('modified_on', 'DESC')->get_Single([':id'=>$this->id]);
    }

    public function remove() {
        YZE_DBAImpl::get_instance()->update('page', 'is_deleted=1', 'function_id=:id', [':id'=>$this->id]);
        $this->set('is_deleted', 1)->save();
    }
}?>
