<?php
namespace app\project;
use app\vendor\Expression;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use yangzie\GraphqlSearchNode;
use function yangzie\__;

/**
 *
 *
 * @version $Id$
 * @package project
 */
trait Page_Bind_Api_Action_Model_Method{
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
		case self::F_FROM_CLASS: return "绑定关联的对象";
		case self::F_FROM_UUID: return "from_uuid";
		case self::F_OUTPUT_DATA_ID: return "output_data_id";
		case self::F_PAGE_ID: return "page_id";
		case self::F_MODE: return "mode";
		case self::F_CODE: return "code";
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
		return 'page_bind_api_action model';
	}

	/**
	 * 当前model是否允许在graphql中进行查询
	 * @return boolean
	 */
	public function is_enable_graphql(){
		return true;
	}

	/**
	 * 自定义的Field，field的type nane是系统的表名, 该接口提供一个在通过graphql查询page_bind_api_action时可以联合查询的其他表的方式
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
    private $actions = [];
    private $expressionModel;

    /**
     * @return Expression
     */
    public function get_expression(){
        if (!$this->expressionModel){
            $exp = json_decode(html_entity_decode($this->expression), true);
            if (!$exp) return;
            $this->expressionModel = new Expression($exp);;
        }
        return $this->expressionModel;
    }
    public function add_action(Action_Model $action_Model){
        $this->actions[$action_Model->id] = $action_Model;
        return $this;
    }
    public function get_actions(){
        if (!$this->actions){
            $actions = [];
            foreach (Action_Model::from('a')
                         ->where('a.page_id=:pid and a.bind_class=:cls and a.bind_uuid=:uuid and a.is_deleted=0')
                         ->select([':pid'=>$this->page_id,':cls'=>self::CLASS_NAME, ':uuid'=>$this->uuid]) as $item){
                $actions[$item->id] = $item;
            }
            $this->actions = $actions;
        }
        return $this->actions;
    }
    public static function get_bind_actions($page_id, $from_class, $from_uuid){
        $actions = [];
        foreach (Page_Bind_API_Action_Model::from('ba')
                     ->left_join(Action_Model::CLASS_NAME,'a', "ba.page_id = a.page_id and a.is_deleted=0 and a.bind_class=:bclass and a.bind_uuid=ba.uuid")
                     ->where('ba.page_id=:pid and ba.from_class=:cls and ba.from_uuid=:uuid and ba.is_deleted=0')
                     ->select([':pid'=>$page_id,':cls'=>$from_class, ':uuid'=>$from_uuid,':bclass'=>Page_Bind_API_Action_Model::CLASS_NAME]) as $item){
            $ba = $actions[$item['ba']->id]?:$item['ba'];

            if ($item['a']) {
                $ba->add_action($item['a']);
            }

            $actions[$ba->id] = $ba;
        }
        return $actions;
    }
    /**
     * 从condition中取出一个条件，同时从condition中删除
     * @param $condition
     * @return array
     */
    private function _get_js_condition($condition, $path='', &$return=[], $accessor="?.") {
        if (!$condition) return;
        if ($condition['type']=='object'){
            $name = ($condition['name'] ? $accessor.$condition['name']: "");
            foreach ((array)$condition['props'] as $prop){
                $this->_get_js_condition($prop, $path.$name, $return, $accessor);
            }
            if ($condition['value'] == 'not_empty') {
                $return[] = '!YDECloud.isEmptyObject('.$path.$name.')';
            }else if ($condition['value'] == 'empty') {
                $return[] = 'YDECloud.isEmptyObject('.$path.$name.')';
            }
            return;
        }
        if ($condition['type']=='array') {
            $_ = [];
            if ($condition['name']){
                $_[] = $accessor.$condition['name'].$accessor;
            }
            if ($condition['value'] == 'not_empty') {
                $return[] = $path.join('', $_).'length > 0';
            }else if ($condition['value'] == 'empty') {
                $return[] = $path.join('', $_).'length == 0';
            }
            $_[] = '['.($condition['index']?:0).']';
            $this->_get_js_condition($condition['item'], $path.join('', $_), $return, $accessor);

            return;
        }
        if ($condition['type']=='boolean') {
            if ($condition['value']=='false'){
                if ($condition['name']){
                    $return[] = '!'.$path.$accessor.$condition['name'];
                }else{
                    $return[] = '!'.$path;
                }
            }else{
                if ($condition['name']){
                    $return[] = $path.$accessor.$condition['name'];
                }else{
                    $return[] = $path;
                }
            }
            return;
        }
        if ($condition['type']=='integer') {
            if ($condition['name']){
                $return[] = $path.$accessor.$condition['name'].' == '.$condition['value'];
            }else{
                $return[] = $path.' == '.$condition['value'];
            }
            return;
        }
        if ($condition['name']){
            $return[] = $path.$accessor.$condition['name'].' == "'.$condition['value'].'"';
        }else{
            $return[] = $path.' == '.$condition['value'];
        }
    }

    /**
     * 删除那些在页面上已经删除掉的ui绑定
     * @param $page
     * @return void
     */
    public static function remove_gone_uiid(Page_Model $page) {
        foreach (self::from('apiaction')
                     ->left_join(Page_Bind_Api_Model::CLASS_NAME, 'api', 'api.uuid=apiaction.from_uuid and apiaction.from_class=:class')
                     ->where('apiaction.page_id=:pid')
                     ->select([':pid'=>$page->id, ':class'=>Page_Bind_Api_Model::CLASS_NAME]) as $objs){
            if (!$objs['api']){
                $objs['apiaction']->remove();
            }
        }
    }
    public function remove(){
        parent::remove();
        // 删除自己触发的api
        foreach (Page_Bind_Api_Model::from()->where("page_id=:pid and bind_class=:cls and bind_uuid=:uuid")
                     ->select([':pid'=>$this->page_id, ':cls'=>self::CLASS_NAME, ':uuid'=>$this->uuid]) as $api){
            $api->remove();
        }
        // 删除自己包含的action
        foreach (Action_Model::from()->where("page_id=:pid and bind_class=:cls and bind_uuid=:uuid")
                     ->select([':pid'=>$this->page_id, ':cls'=>self::CLASS_NAME, ':uuid'=>$this->uuid]) as $action){
            $action->remove();
        }
    }
    public function get_action_data(){
        $actions = $this->get_actions();
        $trueActionData = [];
        $falseActionData = [];
        foreach ($actions as $action){
            if ($action->bind_condition == 'true'){
                $trueActionData[] = $action->get_action_data();
            }else{
                $falseActionData[] = $action->get_action_data();
            }
        }
        $expression = $this->get_expression();
        return  [
            'uuid'=>$this->uuid,
            'mode'=>$this->mode,
            'code'=>$this->code,
            'expression'=>json_decode(html_entity_decode($this->expression), true),
            'expression_desc'=>$expression ? $expression->get_expression_code() : '',
            'output_data_id'=>$this->output_data_id,
            'trueActions'=>$trueActionData,
            'falseActions'=>$falseActionData,
        ];
    }
    public function get_condition_info(){
        $info = [];
        switch($this->from_class){
            case Page_Bind_Api_Model::CLASS_NAME:
                $bindApi = Page_Bind_Api_Model::find_by_uuid($this->from_uuid);
                $exp = '';
                if ($this->mode == 'setting'){
                    $expression = $this->get_expression();
                    if ($expression){
                        $exp = $expression->get_expression_code();
                    }
                }else{
                    $exp = 'CUSTOM CODE';
                }
                $info[] = sprintf(__('When the API 「%s」 is called and the expression (%s) is true '), $bindApi->name, $exp);
        }
        return  join('', $info);
    }
    /**
     * 触发该Action时能使用的局部变量：
     * 1。如果是page bind api触发的，本地数据就是api的output中包含output_data_id响应的结构体
     * @return void
     */
    public function get_local_variables(){
        if ($this->mode=='code') return null;
        if ($this->from_class == Page_Bind_Api_Model::CLASS_NAME){
            $bindApi = Page_Bind_Api_Model::find_by_uuid($this->from_uuid);
            $outputs = json_decode(html_entity_decode($bindApi->output));
            foreach ($outputs as $output){
                if ($output->uuid == $this->output_data_id) {
                    $output->body->name = 'rst';
                    $output->body->title = $bindApi->name;
                    return [$output->body];
                }
            }
            return null;
        }
    }
}?>
