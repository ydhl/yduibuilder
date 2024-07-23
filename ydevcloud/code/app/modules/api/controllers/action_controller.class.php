<?php
namespace app\api;
use app\modules\build\views\code\Code_Suggestion;
use app\project\Action_Model;
use app\project\Function_Model;
use app\project\Mutation_Model;
use app\project\Page_Bind_API_Action_Model;
use app\project\Page_Bind_Api_Model;
use app\project\Page_Bind_Event_Model;
use app\project\Page_Model;
use app\project\Uicomponent_Event_Model;
use app\project\Web_Api_Model;
use yangzie\YZE_DBAImpl;
use yangzie\YZE_FatalException;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_JSON_View;
use function yangzie\__;

/**
* 处理页面的行为绑定
* @version $Id$
* @package api
*/
class Action_Controller extends YZE_Resource_Controller {
    private $page;
    private $loginUser;
    private $project;
    private $project_member;
    public function valid($page_uuid, $page=null)
    {
        $this->loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        if ($page){
            $this->page = $page;
        }else{
            $this->page = find_by_uuid(Page_Model::CLASS_NAME, $page_uuid);
        }
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
    // 拉取 api 绑定的post processor action
    public function index(){
        $request = $this->request;
        $this->layout = '';
        $uuid = trim($request->get_from_get("uuid"));// bind api uuid
        $bindApi = find_by_uuid(Page_Bind_Api_Model::CLASS_NAME, $uuid);
        if (!$bindApi) {
            return YZE_JSON_View::success($this,[]);
        }
        $this->valid('', $bindApi->get_page());
        $outputs = json_decode(html_entity_decode($bindApi->output), true);

        $bindActions = Page_Bind_API_Action_Model::from('ba')
            ->left_join(Action_Model::CLASS_NAME,'a', "ba.page_id = a.page_id and a.is_deleted=0 and a.bind_class=:bclass and a.bind_uuid=ba.uuid")
            ->left_join(Page_Model::CLASS_NAME,'p', 'a.popupPageId = p.uuid')
            ->where('ba.page_id=:pid and ba.from_class=:cls and ba.from_uuid=:uuid and ba.is_deleted=0')
            ->select([':pid'=>$bindApi->page_id,':cls'=>Page_Bind_Api_Model::CLASS_NAME,':uuid'=>$bindApi->uuid,':bclass'=>Page_Bind_API_Action_Model::CLASS_NAME]);
        $datas = [];
        $models = [];
        foreach ($bindActions as $bindAction){
            $model = $models[$bindAction['ba']->id]?:$bindAction['ba'];
            if ($bindAction['p']) {
                $bindAction['a']->set_popup_page($bindAction['p']);
            }
            if ($bindAction['a']) {
                $model->add_action($bindAction['a']);
            }
            $models[$model->id] = $model;
        }
        foreach ($models as $model){
            $datas[] = $model->get_action_data();
        }
        $suggestions = [];
        foreach ($outputs as $output){
            $suggestion = array_merge([[
                    "label"=>'rst',
                    "kind"=>"monaco.languages.CompletionItemKind['Field']",
                    "insertText"=>'rst',
                    "detail"=>'API Response Result',
                    "documentation"=>'API Response Result'
            ]], Code_Suggestion::get_json_data_suggestions($output['body']));
            $suggestions[$output['uuid']] = $suggestion;
        }
        return YZE_JSON_View::success($this, ['bind_actions'=>$datas,'suggestions'=>$suggestions]);
    }
    public function post_removeapiaction(){
        $request = $this->request;
        $this->layout = '';
        $uuid = trim($request->get_from_post("uuid"));
        $action = find_by_uuid(Page_Bind_API_Action_Model::CLASS_NAME, $uuid);
        if ($action){
            $action->remove();
        }
        return YZE_JSON_View::success($this,['uuid'=>$action->uuid]);
    }

    // action 更新排序
    public function post_sort() {
        $request = $this->request;
        $data = json_decode(file_get_contents('php://input'), true);
        $page_uuid = trim($data["page_uuid"]);
        $index = $data["index"];
        $this->valid($page_uuid);

        $dba = YZE_DBAImpl::get_instance();
        foreach ($index as $uuid=>$index) {
            $dba->update(Action_Model::TABLE, '`index`=:index', 'uuid=:uuid', [':index'=>$index, ':uuid'=>$uuid]);
        }

        return YZE_JSON_View::success($this);
    }
    // 行为里面的行为
    public function post_addaction() {
        $request = $this->request;
        $action_uuid = trim($request->get_from_post("action_uuid"));
        $page_uuid = trim($request->get_from_post("page_uuid"));
        $name = trim($request->get_from_post("name"));// 行为名称
        $type = trim($request->get_from_post("type"));//行为分类

        $this->valid($page_uuid);

        $action = Action_Model::find_by_uuid($action_uuid);
        if(!$action) throw new YZE_FatalException(__('event not found'));

        $subAction = new Action_Model();
        $subAction->set('uuid', Action_Model::uuid())
            ->set('type', $name)
            ->set('page_id', $this->page->id)
            ->set('bind_class', Action_Model::CLASS_NAME)
            ->set('bind_uuid', $action->uuid)
            ->save();

        if ($action->type == Action_Model::TYPE_INTERVAL){
            $field = "interval_{$type}";
            if ($action->has_column($field)){
                $old_value = $action->get($field);
                $old_value = explode(",", $old_value);
                $old_value[] = $subAction->id;
                $action->set($field, join(",", $old_value))->save();
            }
        }

        return YZE_JSON_View::success($this, $subAction->get_records());
    }
    // 删除行为
    public function post_deleteaction() {
        $request = $this->request;
        $action_uuid = trim($request->get_from_post("action_uuid"));
        $page_uuid = trim($request->get_from_post("page_uuid"));

        $this->valid($page_uuid);

        $action = Action_Model::find_by_uuid($action_uuid);
        if(!$action) throw new YZE_FatalException(__('event not found'));
        $action->remove();
        return YZE_JSON_View::success($this);
    }

    /**
     * 保存接口的post process
     * @return YZE_JSON_View
     * @throws YZE_FatalException
     * @throws \yangzie\YZE_DBAException
     */
    public function post_saveapiaction(){
        $request = $this->request;
        $this->layout = '';
        $json = json_decode(file_get_contents("php://input"), true);
        if (!$json) return YZE_JSON_View::error($this, __('Invalid data'));
        $bindApiAction = find_by_uuid(Page_Bind_API_Action_Model::CLASS_NAME, $json['uuid']);
        $this->valid($json['page_uuid']);
        if (!$bindApiAction){
            $bindApiAction = new Page_Bind_API_Action_Model();
            $bindApiAction->set('uuid', Page_Bind_API_Action_Model::uuid());
        }else{
            foreach (Action_Model::from()->where("page_id=:pid and bind_class=:cls and bind_uuid=:uuid")
                ->select([':pid'=>$this->page->id, ':cls'=>Page_Bind_API_Action_Model::CLASS_NAME,':uuid'=>$bindApiAction->uuid]) as $action){
                $action->remove();
            }
        }
        $bindApiAction->set('page_id', $this->page->id)
            ->set('from_class', $json['type']=='bind_api' ? Page_Bind_API_Model::CLASS_NAME : '')// 目前仅和web api有关联
            ->set('from_uuid', $json['from_uuid'])
            ->set('output_data_id', $json['output_data_id'])
            ->set('mode', $json['mode'])
            ->set('code', $json['code'])
            ->set('expression', $json['expression'] ? json_encode($json['expression']) : '')
            ->save();

        foreach ($json['actions'] as $action){
            if ($action['type']=='popup' && !in_array($action['popup_type'], Action_Model::get_popup_type())){
                throw new YZE_FatalException(__('Please select popup type'));
            }
            if ($action['type']=='emit'){
                $emitEvent = Uicomponent_Event_Model::from()
                    ->where("page_id=:pid and name=:name and is_deleted=0")
                    ->get_Single([':pid'=>$this->page->id, ':name'=>$action['emit']['event']]);
                $action['emit_event_id'] = $emitEvent->id ?: NULL;
            }
            if ($action['type']=='interval'){
                $intervalActionIDs = $intervalCompleteIDs = [];
                foreach ((array)$action['interval']['action'] as $_item){
                    if (!$_item['id']) continue;
                    $intervalActionIDs[] = $_item['id'];
                }
                foreach ((array)$action['interval']['complete'] as $_item){
                    if (!$_item['id']) continue;
                    $intervalCompleteIDs[] = $_item['id'];
                }
                $action['interval_duration'] = intval($action['interval']['duration']);
                $action['interval_delay'] = intval($action['interval']['delay']);
                $action['interval_action'] = join(',', $intervalActionIDs);
                $action['interval_complete'] = join(',', $intervalCompleteIDs);
                unset($action['interval']);
            }
            $action['input'] = $action['input'] ? json_encode($action['input']) : '';
            $action['output'] = $action['output'] ? json_encode($action['output']) : '';

            $web_api = $action['bindApi']['apiUuid'] ? find_by_uuid(Web_Api_Model::CLASS_NAME, $action['bindApi']['apiUuid']) : null;
            if($web_api) {
                $bind_api = Page_Bind_Api_Model::save_from_web_api($this->page, $web_api, Page_Bind_API_Action_Model::CLASS_NAME, $bindApiAction->uuid);
                $action['bind_api_id'] = $bind_api->id;
            }else{
                $action['bind_api_id'] = 0;
            }
            unset($action['uuid'],$action['id'],$action['page_id'],$action['bind_class'],$action['bind_uuid']);
            $actionModel = new Action_Model();
            $actionModel->set('uuid', Action_Model::uuid())
                ->set('page_id', $this->page->id)
                ->set('bind_class', Page_Bind_API_Action_Model::CLASS_NAME)
                ->set('bind_uuid', $bindApiAction->uuid);
            $actionModel->save_from_data($action);

            if ($action['type']=='mutation'){
                Mutation_Model::save_mutation($actionModel->id, $action['mutations']);
            }
        }
        return YZE_JSON_View::success($this,$bindApiAction->get_action_data());
    }
    // 更新弹窗事件内容
    public function post_popup() {
        $request = $this->request;
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $postData = json_decode(file_get_contents("php://input"), true);
        $action_uuid = trim($postData["action_uuid"]);
        $popupPageId = trim($postData["popupPageId"])?:'';
        $functionUuid = trim($postData["function_uuid"]);
        $input = $postData["input"];
        $popup_type = trim($postData["popup_type"])?:'page';

        $action = find_by_uuid(Action_Model::CLASS_NAME, $action_uuid);
        $popupPage = find_by_uuid(Page_Model::CLASS_NAME, $popupPageId);
        $function = find_by_uuid(Function_Model::CLASS_NAME, $functionUuid);
        if(!$action) throw new YZE_FatalException(__('action not found'));
        if($functionUuid && !$function) throw new YZE_FatalException(__('function not found'));
        $module = $function ? $function->get_module() : null;
        $page = $action->get_page();

        // 传入id不存在就创建；传入id为空看作清空
        if(!$popupPage && $popup_type=='page' && $popupPageId) {
            $pageConfig = [
                "type"=> 'Modal',
                "pageType"=> 'popup',
                "meta"=> [
                    "id"=> $popupPageId,
                    "isContainer"=> true,
                    "title"=> 'unnamed popup'
                ],
                "items"=> []
            ];
            $popupPage = new Page_Model();
            $popupPage->set(Page_Model::F_UUID, $popupPageId)
                ->set(Page_Model::F_FUNCTION_ID, $function->id)
                ->set(Page_Model::F_PROJECT_ID, $page->project_id)
                ->set(Page_Model::F_CREATE_USER_ID, $loginUser->id)
                ->set(Page_Model::F_PAGE_TYPE, Page_Model::PAGE_TYPE_POPUP)
                ->set(Page_Model::F_MODULE_ID, $module->id)
                ->set(Page_Model::F_NAME, 'unnamed popup')
                ->set(Page_Model::F_CONFIG, json_encode($pageConfig))
                ->save();
        }
        $action->set('popupPageId', $popupPageId)
            ->set('type', Action_Model::TYPE_POPUP)
            ->set('input', json_encode($input))
            ->set('popup_type', $popup_type ?: Action_Model::POPUP_TYPE_PAGE)
            ->save();
        $action_records = $action->get_records();
        unset($action_records['id'],$action_records['is_deleted'],$action_records['created_on'],$action_records['modified_on']);
        $action_records['popupPageTitle'] = $popupPage->name;
        return YZE_JSON_View::success($this,$action_records);
    }

    // 更新redirect事件内容
    public function post_redirect() {
        $request = $this->request;
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $postData = json_decode(file_get_contents("php://input"), true);
        $action_uuid = trim($postData["uuid"]);
        $redirect = trim($postData["redirect"])?:'';//外部
        $redirect_type = trim($postData["redirect_type"]);
        $popup_page_type = trim($postData["popup_page_type"])?:'unset';
        $popupPageId = trim($postData["popupPageId"]);// 内部
        $input = $postData["input"];

        $action = $action_uuid ? find_by_uuid(Action_Model::CLASS_NAME, $action_uuid) : null;
        $popupPage = $popupPageId ? find_by_uuid(Page_Model::CLASS_NAME, $popupPageId) : null;

        if(!$action) throw new YZE_FatalException(__('action not found'));

        $action->set('popupPageId', $popupPageId?:'')
            ->set('type', Action_Model::TYPE_REDIRECT)
            ->set('redirect', $redirect)
            ->set('redirect_type', $redirect_type)
            ->set('input', json_encode($input))
            ->set('popup_type', Action_Model::POPUP_TYPE_UNSET)
            ->set('popup_page_type', $popup_page_type)
            ->save();
        $action_records = $action->get_records();
        unset($action_records['id'],$action_records['is_deleted'],$action_records['created_on'],$action_records['modified_on']);
        $action_records['popupPageTitle'] = $popupPage->name;
        return YZE_JSON_View::success($this,$action_records);
    }

    // 更新interval事件内容
    public function post_interval() {
        $request = $this->request;
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $postData = json_decode(file_get_contents("php://input"), true);
        $page_uuid = trim($postData["page_uuid"]);
        $action_uuid = trim($postData["action_uuid"]);
        $duration = intval($postData["duration"])?:1000;
        $delay = intval($postData["delay"])?:1000;
        $actionIds = $postData["action"]?:[];
        $completeIds = $postData["complete"]?:[];
        $this->valid($page_uuid);
        $action = $action_uuid ? find_by_uuid(Action_Model::CLASS_NAME, $action_uuid) : null;

        if(!$action) throw new YZE_FatalException(__('action not found'));

        $action->set('type', Action_Model::TYPE_INTERVAL)
            ->set('interval_duration', $duration)
            ->set('interval_delay', $delay)
            ->set('interval_action', join(",", $actionIds))
            ->set('interval_complete', join(",", $completeIds))
            ->save();
        $action_records = $action->get_records();
        unset($action_records['id'],$action_records['is_deleted'],$action_records['created_on'],$action_records['modified_on']);
        return YZE_JSON_View::success($this,$action_records);
    }

    // 更新webapi事件内容
    public function post_webapi() {
        $request = $this->request;
        $postData = json_decode(file_get_contents("php://input"), true);
        $page_uuid = trim($postData["page_uuid"]);
        $action_uuid = trim($postData["action_uuid"]);
        $api_uuid = trim($postData["api_uuid"]);
        $bind_type = trim($postData["bind_type"]);
        $bind_uuid = trim($postData["bind_uuid"]);
        $this->valid($page_uuid);

        switch ($bind_type){
            case 'bind_event': $bind_class = Page_Bind_Event_Model::CLASS_NAME;break;
            case 'bind_action': $bind_class = Page_Bind_API_Action_Model::CLASS_NAME;break;
            default: throw new YZE_FatalException(__('unknown bind type'));
        }

        $action = find_by_uuid(Action_Model::CLASS_NAME, $action_uuid);
        if(!$action) {
            throw new YZE_FatalException(__('action not found'));
        }
        $web_api = find_by_uuid(Web_Api_Model::CLASS_NAME, $api_uuid);
        if($web_api) {
            $bind_api = Page_Bind_Api_Model::save_from_web_api($this->page, $web_api, $bind_class, $bind_uuid);
            $action->set('bind_api_id', $bind_api->id)->save();
        }else{
            $action->set('bind_api_id', 0)->save();
        }
        return YZE_JSON_View::success($this,['uuid'=>$action->uuid]);
    }

    // 更新赋值事件内容
    public function post_mutation() {
        $request = $this->request;
        $postData = json_decode(file_get_contents("php://input"), true);
        $page_uuid = trim($postData["page_uuid"]);
        $action_uuid = trim($postData["action_uuid"]);
        $mutations = $postData["mutations"];
        $this->valid($page_uuid);

        $action = find_by_uuid(Action_Model::CLASS_NAME, $action_uuid);
        if(!$action) {
            throw new YZE_FatalException(__('action not found'));
        }

        Mutation_Model::save_mutation($action->id, $mutations);

        return YZE_JSON_View::success($this,['uuid'=>$action->uuid]);
    }

    // 更新emit事件内容
    public function post_emit() {
        $request = $this->request;
        $postData = json_decode(file_get_contents("php://input"), true);
        $page_uuid = trim($postData["page_uuid"]);
        $action_uuid = trim($postData["action_uuid"]);
        $event = trim($postData["event"]);
        $input = $postData["input"];
        $this->valid($page_uuid);

        $action = find_by_uuid(Action_Model::CLASS_NAME, $action_uuid);
        if(!$action) {
            throw new YZE_FatalException(__('action not found'));
        }

        $event = Uicomponent_Event_Model::from()->where('page_id=:id and name=:name')
            ->get_Single([':id'=>$this->page->id, ':name'=>$event]);
        if (!$event) throw new YZE_FatalException(__('event not found'));

        $action->set('emit_event_id', $event->id)
            ->set('input', json_encode($input))
            ->save();
        return YZE_JSON_View::success($this,['uuid'=>$action->uuid]);
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }
}
?>
