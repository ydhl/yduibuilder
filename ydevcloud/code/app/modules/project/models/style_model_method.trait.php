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
trait Style_Model_Method{
    // 这里实现model的业务方法
    public static function get_by_name($class_name) {
        return self::from()->where('class_name=:class_name and is_deleted=0')->get_Single([':class_name'=>$class_name]);
    }

    public function get_used_count($page_id=null){
        $where = 'style_id=:sid';
        $param = [':sid'=>$this->id];
        if ($page_id) {
            $where .= " and page_id=:pid";
            $param[':pid'] = $page_id;
        }
        return Page_Bind_Style_Model::from()
            ->where($where)
            ->count('id', $param);
    }
}?>
