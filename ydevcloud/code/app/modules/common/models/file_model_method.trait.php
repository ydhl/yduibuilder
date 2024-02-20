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
trait File_Model_Method{
    public static function get_files($file_name, $project_id, $type='', $page=1) {
        $page = intval($page);
        if ($page<1)$page=1;

        $where = 'project_id=:id';
        $params[":id"] = $project_id;
        if ($type){
            $where .= ' and type=:type';
            $params[":type"] = $type;
        }
        if ($file_name){
            $where .= ' and file_name like :like';
            $params[":like"] = '%'.$file_name.'%';
        }

        return File_Model::from()->where($where)
            ->order_By('upload_date', 'DESC')
            ->limit(($page - 1) * 100, 100)
            ->select($params);
    }
}?>
