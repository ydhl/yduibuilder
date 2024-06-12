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
trait Mutation_Model_Method{
    private $bindData;
    private $expressionModel;
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
		case self::F_MUTATION_FROM_UUID: return "mutation_from_uuid";
		case self::F_MUTATION_DATA_ID: return "mutation_data_id";
		case self::F_EXPRESSION: return "expression";
		case self::F_ACTION_ID: return "action_id";
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
		return 'mutation model';
	}

	/**
	 * 当前model是否允许在graphql中进行查询
	 * @return boolean
	 */
	public function is_enable_graphql(){
		return true;
	}

	/**
	 * 自定义的Field，field的type nane是系统的表名, 该接口提供一个在通过graphql查询mutation时可以联合查询的其他表的方式
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
    public function set_from_data(Page_Bind_Data_Model $bindData){
        $this->bindData = $bindData;
        return $this;
    }
    public function get_from_data(): Page_Bind_Data_Model{
		if (!$this->bindData){
			$this->bindData = Page_Bind_Data_Model::find_by_uuid($this->mutation_from_uuid);
		}
        return $this->bindData;
    }

	/**
	 * @return Expression
	 */
	public function get_expression(){
		if (!$this->expressionModel){
			$exp = json_decode(html_entity_decode($this->expression), true);
			if($exp) $this->expressionModel = new Expression($exp);
		}
		return $this->expressionModel;
	}
	public function get_muation_data(){
		$expression = $this->get_expression();
		return [
			'expression' => $expression ? json_decode(html_entity_decode($this->expression)) : new \stdClass(),
			'expression_code' => $expression ? $expression->get_expression_code() : $this->expression,
			'from_uuid' => $this->mutation_from_uuid,
			'data_id' => $this->mutation_data_id,
			'data_name' => $this->mutation_data_name,
			'data_type' => $this->mutation_data_type
		];
	}

	public static function save_mutation($action_id, $mutationConfigs){
		Mutation_Model::from()->where('action_id=:aid')->delete([':aid'=>$action_id]);
		foreach ($mutationConfigs as $data_id => $mutation){
			if(!$mutation['expression'] || !$mutation['expression']['type']) continue;
			$model = new Mutation_Model();
			$model->set('mutation_from_uuid', $mutation['from_uuid'])
				->set('mutation_data_id', $data_id)
				->set('mutation_data_name', $mutation['data_name'])
				->set('mutation_data_type', $mutation['data_type'])
				->set('uuid', Mutation_Model::uuid())
				->set('action_id', $action_id)
				->set('expression', json_encode($mutation['expression']))
				->save();
		}
	}
}?>
