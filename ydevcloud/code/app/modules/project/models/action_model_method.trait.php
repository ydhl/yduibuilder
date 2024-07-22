<?php
namespace app\project;
use app\vendor\Expression;
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
trait Action_Model_Method{
    private $web_bind;
    private $popup_page;
    private $mutations;
    private $expression;
    public function set_bind_api($api){
        $this->web_bind = $api;
        return $this;
    }

    /**
     * @return Page_Bind_Api_Model|null
     * @throws YZE_DBAException
     */
    public function get_bind_api(){
        if (!$this->web_bind && $this->bind_api_id){
            $this->web_bind = Page_Bind_Api_Model::find_by_id($this->bind_api_id);
        }
        return $this->web_bind;
    }

    /**
     * 如果是alert的弹窗，由于没有数据绑定，所以直接返回expression对象，其他的action返回的是[dataid=>expression]数组
     * @return Expression | Array<Expression>
     */
    public function get_expression(){
        if (!$this->expressionModel){
            $exp = json_decode(html_entity_decode($this->input), true) ?: [];
            if ($this->type=='popup' && $this->popup_type=='alert'){
                if($exp['expression']) $this->expressionModel = new Expression($exp['expression']);
            }else{
                $models = [];
                foreach ($exp as $dataid => $item) {
                    $models[$dataid] = new Expression($item['expression']);
                }
                $this->expressionModel = $models;
            }
        }
        return $this->expressionModel;
    }
    public function get_popup_page(){
        if (!$this->popup_page && $this->popupPageId){
            $this->popup_page = find_by_uuid(Page_Model::CLASS_NAME, $this->popupPageId);
        }
        return $this->popup_page;
    }
    public function set_popup_page(Page_Model $page){
        $this->popup_page = $page;
        return $this;
    }
    public function remove(){
        $bind_api = $this->get_bind_api();
        if ($bind_api) $bind_api->remove();
        Mutation_Model::from()->where('action_id=:aid')->delete([':aid'=>$this->id]);
        parent::remove();
    }
    public function get_action_data(){
        $action_records = $this->get_records();
        unset($action_records['is_deleted'],$action_records['created_on'],$action_records['modified_on']);
        if ($this->get_popup_page()) {
            $action_records['popupPageTitle'] = $this->get_popup_page()->name;
        }
        $action_records['input'] = json_decode(html_entity_decode($action_records['input'])) ?: null;
        $action_records['output'] = json_decode(html_entity_decode($action_records['output'])) ?: null;

        $bind_api = $this->get_bind_api();
        if ($bind_api){
            $action_records['bindApi'] = $bind_api->get_basic_info();
        }

        if ($this->type == 'mutation'){
            $mutations = Mutation_Model::from()->where('is_deleted=0 and action_id=:id')->select([':id'=>$this->id]);
            $mutationData = [];
            foreach ($mutations as $mutation){
                $mutationData[$mutation->mutation_data_id] = $mutation->get_muation_data();
            }
            $action_records['mutations'] = $mutationData?:new \stdClass();
        }else if($this->type == 'emit'){
            $emit = $this->get_emit_event();
            if ($emit){
                $emitData = [
                    'event' => $emit->name,
                    'desc' =>$emit->desc,
                    'args' => json_decode(html_entity_decode($emit->args)),
                ];
                $action_records['emit'] = $emitData?:null;
            }
        }else if($this->type == 'interval'){
            $action_ids = array_filter(explode(',', $action_records['interval_action']));
            $complete_ids = array_filter(explode(',', $action_records['interval_complete']));
            $ids = array_filter(array_merge($action_ids, $complete_ids));

            $subActions = $ids ? Action_Model::from()->where('is_deleted = 0 and id in ('.join(',',$ids).')')->select() : [];
            $action_records['interval'] = [
                'duration' => $action_records['interval_duration'],
                'delay' => $action_records['interval_delay']
            ];
            $action = [];
            $complete = [];
            foreach ($subActions as $subAction){
                if (in_array($subAction->id, $action_ids)){
                    $action[] = $subAction->get_action_data();
                }
                if (in_array($subAction->id, $complete_ids)){
                    $complete[] = $subAction->get_action_data();
                }
            }
            if ($action) {
                $action_records['interval']['action'] = $action;
            }
            if ($complete) {
                $action_records['interval']['complete'] = $complete;
            }

            unset($action_records['interval_duration'], $action_records['interval_delay'], $action_records['interval_action'], $action_records['interval_complete']);
        }

        return $action_records;
    }
    /**
     * @return Array<Mutation_Model>
     * @throws YZE_DBAException
     */
    public function get_mutations(){
        if (!$this->mutations){
            $this->mutations = [];
            foreach (Mutation_Model::from('m')
                ->left_join(Page_Bind_Data_Model::CLASS_NAME, 'd', 'd.uuid = m.mutation_from_uuid')
                ->where('m.action_id=:aid and m.is_deleted=0 and d.is_deleted=0')
                         ->select([':aid'=>$this->id]) as $item){
                if($item['d']) $item['m']->set_from_data($item['d']);
                $this->mutations[$item['m']->id] = $item['m'];
            }
        }
        return $this->mutations;
    }

    /**
     * 删除那些在页面上已经删除掉的ui绑定
     * @param $page
     * @return void
     */
    public static function remove_gone_uiid(Page_Model $page) {
        foreach (self::from('act')
                     ->left_join(Page_Bind_Api_Action_Model::CLASS_NAME, 'api', 'api.uuid=act.bind_uuid and act.bind_class=:apiclass')
                     ->left_join(Page_Bind_Event_Model::CLASS_NAME, 'evt', 'evt.uuid=act.bind_uuid and act.bind_class=:evtclass')
                     ->where('act.page_id=:pid')
                     ->select([':pid'=>$page->id, ':apiclass'=>Page_Bind_Api_Action_Model::CLASS_NAME, ':evtclass'=>Page_Bind_Event_Model::CLASS_NAME]) as $objs){
            if (!$objs['api'] && !$objs['evt']){
                $objs['act']->remove();
            }
        }
    }
}?>
