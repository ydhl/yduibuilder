<?php
namespace app\project;
use app\vendor\Expression;
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
trait Page_Bind_Variable_Model_Method{
	private $expressionModel;
	private $fromPageData;
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
		case self::F_FROM_PAGE_ID: return "from_page_id";
		case self::F_FROM_CLASS: return "data_id的来源";
		case self::F_FROM_EXPRESSION: return "from_expression";
		case self::F_TO_PAGE_ID: return "to_page_id";
		case self::F_TO_CLASS: return "data_id的来源";
		case self::F_TO_UUID: return "to_uuid";
		case self::F_TO_DATA_ID: return "to_data_id";
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
		return 'page_bind_variable model';
	}

	/**
	 * 当前model是否允许在graphql中进行查询
	 * @return boolean
	 */
	public function is_enable_graphql(){
		return true;
	}

	/**
	 * 自定义的Field，field的type nane是系统的表名, 该接口提供一个在通过graphql查询page_bind_variable时可以联合查询的其他表的方式
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

	/**
	 * @return Expression
	 */
	public function get_expression(){
		if (!$this->expressionModel){
			$exp = json_decode(html_entity_decode($this->from_expression), true);
			if($exp) $this->expressionModel = new Expression($exp);
		}
		return $this->expressionModel;
	}
    // 这里实现model的业务方法
    public function get_from_data() {
		$model = $this->get_expression();
		if ($model) {
			return [
				'expression'=>json_decode(html_entity_decode($this->from_expression), true),
				'desc'=>$model->get_expression_code()
			];
		}
		return null;
    }
	public function get_from_page_data(){
		if ($this->fromPageData) return $this->fromPageData;
		if ($this->from_class == Page_Bind_Data_Model::CLASS_NAME){
			$this->fromPageData = $this->from_uuid ? Page_Bind_Data_Model::find_by_uuid($this->from_uuid) : null;
		}
		return $this->fromPageData;
	}
    public function get_to_data() {
		return [
			'expression'=>[
				'type'=> 'connect',
				'data'=> [
					'fromUuid'=> $this->to_uuid,
					'id'=> $this->to_data_id,
					'scope'=> $this->to_class==Page_Bind_Data_Model::CLASS_NAME ? 'page' :'local',
					'path'=> $this->to_data_path
				]
			],
			'desc'=> $this->to_data_path
		];
    }

	/**
	 * 返回绑定到指定to_page（左值）页面的指定关联数据(bind_class)上的绑定信息
	 *
	 * 返回的数组格式是
	 *
	 * [
	 * 		左值数据记录uuid=>[
	 * 			左值数据id=> 右值绑定信息
	 * 		]
	 * ]
	 *
	 * 绑定信息有get_data()方法返回
	 *
	 * @param $page
	 * @param $bind_class
	 * @return array
	 * @throws YZE_DBAException
	 */
    public static function get_left_to_right_variable($page, $bind_class){
        $ios = self::from()->where('to_page_id=:pid and to_class=:cls')
            ->select([':pid'=>$page->id,':cls'=>$bind_class]);
        $data = [];
        foreach ($ios as $io){
            $data[$io->to_uuid][$io->to_data_id] = $io->get_from_data();
        }
        return $data;
    }
	/**
	 * 返回从指定from_page（右值）页面的指定关联数据(bind_class)上的绑定信息
	 *
	 * 返回的数组格式是
	 *
	 * [
	 * 		右值数据记录uuid=>[
	 * 			右值数据id=>左值绑定信息
	 * 		]
	 * ]
	 *
	 * 绑定信息有get_to_data()方法返回
	 *
	 * @param $page
	 * @param $bind_class
	 * @return array
	 * @throws YZE_DBAException
	 */
    public static function get_right_to_left_variable($page, $bind_class){
        $ios = self::from()->where('from_page_id=:pid and from_class=:cls')
            ->select([':pid'=>$page->id,':cls'=>$bind_class]);
        $data = [];
        foreach ($ios as $io){
			$expression = $io->get_expression();
			if (!$expression) continue;
            $data[$io->from_uuid][$expression->data->id] = $io->get_to_data();
        }
        return $data;
    }

}?>
