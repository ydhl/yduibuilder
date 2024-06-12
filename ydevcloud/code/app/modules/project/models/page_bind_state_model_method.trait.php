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
trait Page_Bind_State_Model_Method{
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
		case self::F_PAGE_ID: return "page_id";
		case self::F_UIID: return "uiid";
		case self::F_STATE_TYPE: return "state_type";
		case self::F_STATE_NAME: return "state_name";
		case self::F_EXPRESSION: return "expression";
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
		return 'page_bind_state model';
	}

	/**
	 * 当前model是否允许在graphql中进行查询
	 * @return boolean
	 */
	public function is_enable_graphql(){
		return true;
	}

	/**
	 * 自定义的Field，field的type nane是系统的表名, 该接口提供一个在通过graphql查询page_bind_state时可以联合查询的其他表的方式
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
     * 删除那些在页面上已经删除掉的ui绑定
     * @param $page
     * @return void
     */
    public static function remove_gone_uiid(Page_Model $page) {
        $bounds = Page_Bind_State_Model::from()->where('page_id=:pid')->select([':pid'=>$page->id]);
        foreach ($bounds as $bound){
            if(!$page->find_ui_item($bound->uiid)){
                $bound->remove();
            }
        }
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
	public function get_state_data(){
		$expression = $this->get_expression();
		return [
			'name'=> $this->state_name,
			'uuid'=> $this->uuid,
			'type'=> $this->state_type,
			'expression' => $expression ? json_decode(html_entity_decode($this->expression)) : new \stdClass(),
			'expression_code' => $expression ? $expression->get_expression_code() : $this->expression
		];
	}
}?>
