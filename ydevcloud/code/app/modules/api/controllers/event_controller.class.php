<?php
namespace app\api;
use app\project\Action_Model;
use app\project\Page_Bind_Api_Model;
use app\project\Page_Bind_Data_Model;
use app\project\Page_Bind_Event_Model;
use app\project\Page_Bind_Io_Model;
use app\project\Page_Model;
use app\project\Project_Model;
use app\project\Uicomponent_Event_Model;
use app\project\Web_Api_Model;
use app\vendor\Save_Model_Helper;
use yangzie\YZE_DBAImpl;
use yangzie\YZE_FatalException;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use \yangzie\YZE_JSON_View;
use function yangzie\__;

/**
* 处理页面的事件绑定
* @version $Id$
* @package api
*/
class Event_Controller extends YZE_Resource_Controller {
    /**
     * @var Page_Model
     */
    private $page;
    private $loginUser;
    private $project;
    private $project_member;
    public function valid($page_uuid)
    {
        $this->loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $this->page = find_by_uuid(Page_Model::CLASS_NAME, $page_uuid);
        if (!$this->page) throw new YZE_FatalException(__('Page not found'));
        $this->project = $this->page->get_project();
        $this->project_member = $this->project->get_member($this->loginUser->id);
        if (!$this->project_member) throw new YZE_FatalException(__('Project not found'));
    }

    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    // 拉取绑定的事件, 不包含组件上的事件
    public function index(){
        $request = $this->request;
        $this->layout = '';

        $this->valid($request->get_from_get("page_uuid"));
        $items = Page_Bind_Event_Model::from('e')
            ->where('e.page_id=:pid and e.is_deleted=0 and e.uicomponent_event_id is null')
            ->left_join(Action_Model::CLASS_NAME, 'a', "a.page_id = e.page_id and a.is_deleted=0 and a.bind_class=:cls and a.bind_uuid=e.uuid")
            ->left_join(Page_Bind_Api_Model::CLASS_NAME, 'b', 'a.bind_api_id = b.id')
            ->left_join(Web_Api_Model::CLASS_NAME, 'api','api.id = b.web_api_id')
            ->order_By('id','asc','e')
            ->order_By('index','asc','a')
            ->order_By('id','desc','a')
            ->select([':pid'=>$this->page->id, ':cls'=>Page_Bind_Event_Model::CLASS_NAME]);
        $datas = [];
        foreach ($items as $item){
            $bindEvent = $datas[$item['e']->id] ?: $item['e'];
            if ($item['api']){
                $item['b']->set_web_api($item['api']);
                $item['a']->set_bind_api($item['b']);
            }
            if($item['a']) {
                $bindEvent->add_action($item['a']);
            }
            $datas[$bindEvent->id] = $bindEvent;
        }
        $rst = [];
        foreach ($datas as $data){
            $rst[] = $data->get_event_data();
        }

        return YZE_JSON_View::success($this, $rst);
    }
    // 拉取监听的事件(指定页面的自定义事件)
    public function listen() {
        $request = $this->request;
        $page_uuid = trim($request->get_from_get("page_uuid"));
        $subPageId = trim($request->get_from_get("sub_page_uuid"));
        $this->valid($page_uuid);

        $uipage = find_by_uuid(Page_Model::CLASS_NAME, $subPageId);
        $events = Uicomponent_Event_Model::from()->where('page_id=:pid and is_deleted=0')
            ->select([':pid'=>$uipage->id]);

        $datas = [];
        foreach ($events as $event){
            $items = Page_Bind_Event_Model::from('e')
                ->where('e.page_id=:pid and e.uicomponent_event_id=:eventid')
                ->left_join(Action_Model::CLASS_NAME, 'a', "a.bind_uuid = e.uuid and a.bind_class=:cls")
                ->left_join(Page_Bind_Api_Model::CLASS_NAME, 'b', 'a.bind_api_id = b.id')
                ->left_join(Web_Api_Model::CLASS_NAME, 'api','api.id = b.web_api_id')
                ->order_By('id','asc','e')
                ->order_By('index','asc','a')
                ->order_By('id','desc','a')
                ->select([':pid'=>$this->page->id,':eventid'=>$event->id,':cls'=>Page_Bind_Event_Model::CLASS_NAME]);
//            只会有一个事件bind event
            $bindEvent = null;
            foreach ($items as $item){
                $bindEvent = $bindEvent ?: $item['e'];
                if ($item['api']){
                    $item['b']->set_web_api($item['api']);
                    $item['a']->set_bind_api($item['b']);
                }

                if ($item['a']) $bindEvent->add_action($item['a']);
            }

            $datas[] = [
                'uuid'=>$event->uuid,
                'name'=>$event->name,
                'desc'=>$event->desc,
                'bind'=>$bindEvent ? $bindEvent->get_event_data() : null,
                'args'=>json_decode(html_entity_decode($event->args), true) ?: null,
            ];
        }
        return YZE_JSON_View::success($this, $datas);
    }
    // 添加事件绑定
    public function post_add() {
        $request = $this->request;
        $page_uuid = trim($request->get_from_post("page_uuid"));
        $uuid = trim($request->get_from_post("uuid"));
        $uiid = trim($request->get_from_post("uiid"));
        $type = trim($request->get_from_post("type"));
        $event = trim($request->get_from_post("event"));
        $desc = trim($request->get_from_post("desc"));
        $custom_event_uuid = trim($request->get_from_post("custom_event_uuid"));
        $this->valid($page_uuid);

        if ($custom_event_uuid){
            $uicomponent_event = Uicomponent_Event_Model::find_by_uuid($custom_event_uuid);
            if (!$uicomponent_event) {
                throw new YZE_FatalException(__('custom event not found'));
            }
        }

        if(!$type) throw new YZE_FatalException(__('invalid type'));
        if(!$event && !$uicomponent_event) throw new YZE_FatalException(__('please select event'));


        if ($uuid){
            $bind_event = Page_Bind_Event_Model::find_by_uuid($uuid);
        }

        $isAdd = false;
        if (!$bind_event){
            $bind_event = new Page_Bind_Event_Model();
            $bind_event->set('uuid', Page_Bind_Event_Model::uuid())
                ->set('uiid', $uiid?:'');
            $isAdd = true;
        }
        $bind_event->set('page_id', $this->page->id)
            ->set('event', $uicomponent_event->name ?: $event)
            ->set('desc', $desc?:'')
            ->set('uicomponent_event_id', $uicomponent_event->id?:NULL)
            ->save();

        if ($isAdd){
            $action = new Action_Model();
            $action->set('uuid', Action_Model::uuid())
                ->set('type', $type)
                ->set('page_id', $this->page->id)
                ->set('bind_class', Page_Bind_Event_Model::CLASS_NAME)
                ->set('bind_uuid', $bind_event->uuid)
                ->save();
        }
        return YZE_JSON_View::success($this, ['uuid'=>$bind_event->uuid]);
    }
    // 添加事件行为
    public function post_addaction() {
        $request = $this->request;
        $page_uuid = trim($request->get_from_post("page_uuid"));
        $event_uuid = trim($request->get_from_post("event_uuid"));
        $type = trim($request->get_from_post("type"));
        $this->valid($page_uuid);

        if(!$type) throw new YZE_FatalException(__('invalid type'));

        $bind_event = Page_Bind_Event_Model::find_by_uuid($event_uuid);
        if(!$bind_event) throw new YZE_FatalException(__('event not found'));

        $action = new Action_Model();
        $action->set('uuid', Action_Model::uuid())
            ->set('type', $type)
            ->set('page_id', $this->page->id)
            ->set('bind_class', Page_Bind_Event_Model::CLASS_NAME)
            ->set('bind_uuid', $bind_event->uuid)
            ->save();
        return YZE_JSON_View::success($this, ['uuid'=>$bind_event->uuid]);
    }
    // 删除自定义事件
    public function post_deletedeclare() {
        $request = $this->request;
        $data = json_decode(file_get_contents('php://input'), true);

        $page_uuid = $data["page_uuid"];
        $event_uuid = $data["event_uuid"];
        $this->valid($page_uuid);

        $event = find_by_uuid(Uicomponent_Event_Model::CLASS_NAME, $event_uuid);
        if($event) $event->remove();
        return YZE_JSON_View::success($this);
    }
    // 保存自定义事件
    public function post_declare() {
        $request = $this->request;
        $data = json_decode(file_get_contents('php://input'), true);

        $page_uuid = $data["page_uuid"];
        $event = $data["event"];
        $this->valid($page_uuid);

        $check = Uicomponent_Event_Model::from()->where('name=:name and page_id=:pid')
            ->get_Single([':name'=>$event['name'], ':pid'=>$this->page->id]);

        $eventModel = find_by_uuid(Uicomponent_Event_Model::CLASS_NAME, $event['uuid']);
        if(!$eventModel) {
            $eventModel = new Uicomponent_Event_Model();
            $eventModel->set('uuid', Uicomponent_Event_Model::uuid());
        }
        if ($check && $check->id != $eventModel->id) throw new YZE_FatalException(__('An event with the same name already exists'));
        $eventModel->set('page_id', $this->page->id)
            ->set('name', $event['name'])
            ->set('args', json_encode($event['args'], JSON_UNESCAPED_UNICODE))
            ->set('desc', $event['desc'])
            ->save();
        return YZE_JSON_View::success($this);
    }
    // 拉取自定义事件
    public function declare() {
        $request = $this->request;
        $page_uuid = trim($request->get_from_get("page_uuid"));
        $this->valid($page_uuid);

        $events = Uicomponent_Event_Model::from()->where('page_id=:pid and is_deleted=0')
            ->select([':pid'=>$this->page->id]);
        $datas = [];
        foreach ($events as $event){
            $datas[] = [
                'uuid'=>$event->uuid,
                'name'=>$event->name,
                'desc'=>$event->desc,
                'args'=>json_decode(html_entity_decode($event->args), true) ?: null,
            ];
        }
        return YZE_JSON_View::success($this, $datas);
    }

    // 删除事件绑定
    public function post_delete() {
        $request = $this->request;
        $page_uuid = trim($request->get_from_post("page_uuid"));
        $uuid = trim($request->get_from_post("uuid"));
        $this->valid($page_uuid);

        $bind_event = find_by_uuid(Page_Bind_Event_Model::CLASS_NAME, $uuid);
        if($bind_event) $bind_event->remove();
        return YZE_JSON_View::success($this);
    }
    // 事件绑定ui
    public function post_bindui() {
        $request = $this->request;
        $page_uuid = trim($request->get_from_post("page_uuid"));
        $event_id = trim($request->get_from_post("event_id"));
        $ui_id = trim($request->get_from_post("ui_id"));
        $this->valid($page_uuid);

        $bind_event = find_by_uuid(Page_Bind_Event_Model::CLASS_NAME, $event_id);
        if(!$bind_event) {
            throw new YZE_FatalException(__('event not found'));
        }
        $uiids = array_filter(explode(',',$bind_event->uiid));
        $uiids[] = $ui_id;
        $bind_event->set('uiid', join(',', array_unique($uiids)))->save();
        return YZE_JSON_View::success($this,['uuid'=>$bind_event->uuid]);
    }
    // 移出事件和ui的绑定
    public function post_removebindui() {
        $request = $this->request;
        $page_uuid = trim($request->get_from_post("page_uuid"));
        $event_id = trim($request->get_from_post("event_id"));
        $ui_id = trim($request->get_from_post("ui_id"));
        $this->valid($page_uuid);

        $bind_event = find_by_uuid(Page_Bind_Event_Model::CLASS_NAME, $event_id);
        if(!$bind_event) {
            throw new YZE_FatalException(__('event not found'));
        }
        $uiids = explode(',',$bind_event->uiid);
        $index = array_search($ui_id, $uiids);
        unset($uiids[$index]);
        $bind_event->set('uiid', join(',', array_filter($uiids)))->save();
        return YZE_JSON_View::success($this,$this->get_event_record($bind_event));
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }
}
?>
