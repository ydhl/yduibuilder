<?php
namespace app\project;
use yangzie\YZE_FatalException;
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
trait Page_Bind_Data_Model_Method{
    // 这里实现model的业务方法
    public function get_data_model() {
        $record = $this->get_records();

        unset($record['created_on'],$record['id'],$record['modified_on'],$record['is_deleted'],$record['page_id'],$record['data_from'],$record['content']);
        if ($this->type == 'array'){
            $record['item'] = json_decode(html_entity_decode($this->content), true);
        }else if ($this->type == 'object'){
            $record['props'] = json_decode(html_entity_decode($this->content), true);
        }
        $record['defaultValue'] = html_entity_decode($this->defaultValue);
        $record['isRoot'] = true;
        $record['enumValue'] = json_decode(html_entity_decode($this->enumValue), true)?:null;
        return $record;
    }

    /**
     * 查找指定的数据，并返回对应的上一级数据和该数据的路径信息
     *
     * @param $data_id string 要查找的数据id
     * @param $dataSource array 查找数据员源
     * @param $allParents array 找到data_id的所有上级路径，从下往上
     * @param $path array 从找到的parent到自己的访问路径，从下往上
     * @return mixed|null
     */
    public function find_data($data_id, $dataSource, &$allParents=[], &$path=[]) {
        if ($dataSource['uuid'] == $data_id) {
            return $dataSource;
        }

        if ($dataSource['type'] == 'array'){
            $rst = $this->find_data($data_id, $dataSource['item'],$allParents, $path);
            if ($rst) {
                $allParents[] = $dataSource;
                $path[] = $dataSource['name'];// 注意如果数组没有name也需要加上
                return $rst;
            }
        }else if ($dataSource['type'] == 'object') {
            foreach ($dataSource['props'] as $prop) {
                $rst = $this->find_data($data_id, $prop, $allParents, $path);
                if ($rst) {
                    $allParents[] = $dataSource;
                    $path[] = $dataSource['name'];
                    return $rst;
                }
            }
        }
        return null;
    }
    /**
     * 返回data id的上级parent
     *
     * @param $data_id
     * @param $check_type array 传入则返回指定类型的直接上级, 不传入则返回最顶级节点
     * @param $path array 从找到的上级到自己的访问路径
     * @param $foundData array 找到的数据节点
     * @return void
     */
    public function get_parent_of_data_id($data_id, $check_type=null, &$path=[], &$foundData=null){
        $dataModel = $this->get_data_model();
        $allParents = [];
        $foundData = $this->find_data($data_id, $dataModel, $allParents, $path);
        if (!$check_type) {
            return array_pop($allParents);
        }
        foreach ($allParents as $index => $parent){
            if (in_array($parent['type'], (array)$check_type)) {
                $path = array_slice($path, 0, $index+1);
                return $parent;
            }
        }
        return null;
    }
    public static function get_page_bind_data($page_id, $data_from){
        $where = 'page_id=:pid';
        $params = [':pid'=>$page_id];
        $dba = YZE_DBAImpl::get_instance();
        if ($data_from) {
            $_ = [];
            foreach (array_filter(explode(',', $data_from)) as $item){
                $_[] = $dba->quote($item);
            }
            $where .= " and data_from in (".join(",", $_).")";
        }
        return Page_Bind_Data_Model::from()
            ->where($where)
            ->order_By('id','asc')
            ->select($params);
    }

    /**
     * 从给定的bind中获取给定的data id的数据
     * @param $data_uuid string page_bind_data 的uuid
     * @param $data_id string 数据uuid
     * @param $path array 访问data id的访问路径，从上往下
     * @return array dataconfig
     */
    public static function find_data_from_bind($data_uuid, $data_id, &$path = []){
        $from_data = Page_Bind_Data_Model::find_by_uuid($data_uuid);
        if (!$from_data) return null;
        $dataConfig = $from_data->get_data_model();
        $allParents =[];
        $data = $from_data->find_data($data_id, $dataConfig, $allParents, $path);
        if (!$data) return null;
        $path[] = $data['name'];
        $path = array_reverse($path);
        return $data;
    }
}?>
