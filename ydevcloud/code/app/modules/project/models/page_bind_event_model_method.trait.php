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
trait Page_Bind_Event_Model_Method{
    // 这里实现model的业务方法
    private $actions = [];
    public function add_action(Action_Model $action_Model){
        $this->actions[$action_Model->id] = $action_Model;
        return $this;
    }
    public function get_actions(){
        if (!$this->actions){
            $actions = [];
            foreach (Action_Model::from('a')
                         ->where('a.page_id=:pid and a.bind_class=:cls and a.bind_uuid=:uuid and a.is_deleted=0')
                ->order_By('index', 'asc', 'a')
                ->order_By('id', 'desc', 'a')
                         ->select([':pid'=>$this->page_id,':cls'=>self::CLASS_NAME, ':uuid'=>$this->uuid]) as $item){
                $actions[$item->id] = $item;
            }
            $this->actions = $actions;
        }
        return $this->actions;
    }
    public function remove(){
        parent::remove();
        foreach (Action_Model::from()->where("page_id=:pid and bind_class=:cls and bind_uuid=:uuid")
            ->select([':pid'=>$this->page_id, ':cls'=>self::CLASS_NAME, ':uuid'=>$this->uuid]) as $action) {
            $action->remove();
        }
        foreach (Page_Bind_Api_Model::from()->where("page_id=:pid and bind_class=:cls and bind_uuid=:uuid")
            ->select([':pid'=>$this->page_id, ':cls'=>self::CLASS_NAME, ':uuid'=>$this->uuid]) as $action) {
            $action->remove();
        }
    }

    /**
     * 删除那些在页面上已经删除掉的ui绑定
     * @param $page
     * @return void
     */
    public static function remove_gone_uiid(Page_Model $page) {
        $events = Page_Bind_Event_Model::from()->where('page_id=:pid and uicomponent_event_id is null')->select([':pid'=>$page->id]);
        foreach ($events as $event){
            $uiids = array_filter(explode(",", $event->uiid));
            $filter_uiids = [];
            foreach ($uiids as $uiid){
                if($page->find_ui_item($uiid)){
                    $filter_uiids[] = $uiid;
                }
            }
            if ($filter_uiids){
                $event->set('uiid', join(",", $filter_uiids))->save();
            }else{
                $event->remove();
            }
        }
    }
    public function get_event_data(){
        $record = $this->get_records();
        unset($record['id']);
        $actionData = [];
        foreach ($this->get_actions() as $action){
            $actionData[] = $action->get_action_data();
        }
        $record['actions'] = $actionData;
        $record['uiid'] = array_filter(explode(',', $record['uiid']));
        return $record;
    }
}?>
