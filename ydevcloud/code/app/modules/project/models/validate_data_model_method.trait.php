<?php
namespace app\project;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use yangzie\GraphqlSearchNode;
/**
 *
 *
 * @version $Id$
 * @package project
 */
trait Validate_Data_Model_Method{
    private $from;
    public function get_from_object(){
        if (!$this->from) {
            $this->from = find_by_uuid($this->from_class, $this->from_uuid);
        }
        return $this->from;
    }
    public function set_from_object($from){
        $this->from = $from;
		return $this;
    }
	/**
	 * 返回每个字段的描述文本
	 * @param $column
	 * @return mixed
	 */
    public function get_column_mean($column){
    	switch ($column){
    	case self::F_ID: return "id";
		case self::F_CREATED_ON: return "created_on";
		case self::F_MODIFIED_ON: return "modified_on";
		case self::F_IS_DELETED: return "is_deleted";
		case self::F_UUID: return "uuid";
		case self::F_ACTION_ID: return "action_id";
		case self::F_FROM_CLASS: return "from_class";
		case self::F_FROM_UUID: return "from_uuid";
		case self::F_DATA_UUID: return "data_uuid";
    	default: return $column;
    	}
		return $column;
	}
    /**
	 * 返回表的描述
	 * @param $column
	 * @return mixed
	 */
    public function get_description(){
		return 'validate_data model';
	}

	/**
	 * 当前model是否允许在graphql中进行查询
	 * @return boolean
	 */
	public function is_enable_graphql(){
		return true;
	}

	/**
	 * 自定义的Field，field的type nane是系统的表名, 该接口提供一个在通过graphql查询validate_data时可以联合查询的其他表的方式
	 *
	 * 如果要返回的不是表名而是自定义的类型，那么该类型必须通过YZE_GRAPHQL_TYPES进行定义并返回
	 *
	 * 具体如何查询，需要在query_graphql_fields中实现
	 * @return array<GraphqlField>
	 */
	public function custom_graphql_fields(){
		return [];
	}

	/**
	 * 根据传入的fieldName名返回对应的subFields值
	 * @param $graphqlSearchNode 查询的内容结构体
	 * @return array<GraphqlField>
	 */
	public function query_graphql_fields(GraphqlSearchNode $searchNode){
		return [];
	}
    // 这里实现model的业务方法
    public function get_validate_data(){
        $from = $this->get_from_object();
        if (!$from) return null;
        if ($this->from_class == Page_Bind_Data_Model::CLASS_NAME){
            $data = $from->get_data_model();
			$allParents=[];$path=[];
			$data = $from->find_data($this->data_uuid, $data, $allParents, $path);
			$path[] = 'page';
			$path = array_reverse($path);
			$path[] = $data['name'];
			$data['from_uuid'] = $from->uuid;
			$data['fullName'] = join('.', $path);
			$data['from_type'] = 'page_bind_data';
			return $data;
        }else{
            return null;
        }
    }

	public static function save_validate(Action_Model $action, $validate){
		Validate_Data_Model::from()->where('action_id=:aid')->delete([':aid'=>$action->id]);
		$saved = [];

		foreach ((array)$validate['datas'] as $validateData){
			if (in_array($validateData['from_uuid'].$validateData['uuid'], $saved)) continue;
			$model = new Validate_Data_Model();
			$model->set('from_class', Page_Bind_Data_Model::CLASS_NAME) // 目前先只考虑page bind data
				->set('from_uuid', $validateData['from_uuid'])
				->set('data_uuid', $validateData['uuid'])
				->set('uuid', Validate_Data_Model::uuid())
				->set('action_id', $action->id)
				->save();
			$saved[] = $validateData['from_uuid'].$validateData['uuid'];
		}
	}
}?>
