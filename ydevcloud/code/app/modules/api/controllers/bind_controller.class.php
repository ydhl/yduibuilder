<?php
namespace app\api;
use app\build\Build_Model;
use app\modules\build\views\preview\bootstrap\Page_View;
use app\modules\build\views\preview\Preview_View;
use app\project\Action_Model;
use app\project\Label_Model;
use app\project\Page_Bind_Api_Model;
use app\project\Page_Bind_Data_Model;
use app\project\Page_Bind_Event_Model;
use app\project\Page_Bind_Io_Model;
use app\project\Page_Bind_Variable_Model;
use app\project\Page_Model;
use app\project\Project_Model;
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
* 处理页面的数据和api绑定
* @version $Id$
* @package api
*/
class Bind_Controller extends YZE_Resource_Controller {
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
    private function bind_variables($from_page_uuid) {
        $from_page = Page_Model::find_by_uuid($from_page_uuid);
        if (!$from_page) throw new YZE_FatalException(__('page not found'));
        $where = 'page_id=:pid';
        $params = [':pid'=>$this->page->id];
        $where .= " and data_from ='query'";

        $bindDatas = Page_Bind_Data_Model::from()
            ->where($where)
            ->order_By('id','asc')
            ->select($params);
        $datas = [];
        foreach ($bindDatas as $data){
            $datas[] = $data->get_data_model();
        }
        $bound_variables = Page_Bind_Variable_Model::from()
            ->where('to_page_id=:toid and to_class=:cls and from_page_id=:fromid')
            ->select([':toid'=>$this->page->id, ':cls'=>Page_Bind_Data_Model::CLASS_NAME, ':fromid'=>$from_page->id]);
        $input = [];
        foreach ($bound_variables as $variable){
            //expression 结构体
            $input[$variable->to_data_id] = $variable->get_from_data();
        }
        return YZE_JSON_View::success($this, ['query'=>$datas, 'input'=>$input]);
    }
    // 拉取页面的输入数据
    public function input(){
        $request = $this->request;
        $this->layout = '';
        $this->valid($request->get_from_get("to_page_uuid"));
        return $this->bind_variables($request->get_from_get("from_page_uuid"));
    }
    // 保存页面输入数据的绑定信息
    public function post_connect(){
        $request = $this->request;
        $this->layout = '';
        $data = json_decode(file_get_contents('php://input'), true);
        $this->valid($data["to_page_uuid"]);
        $from_page_uuid = trim($data["from_page_uuid"]);
        $to_uuid = trim($data["to_uuid"]);
        $to_data_id = trim($data["to_data_id"]);
        $to_path = trim($data["to_path"]);
        $from_type = trim($data["from_type"]);
        $to_type = trim($data["to_type"]);
        $input = $data["input"]; //Expression 表达式

        $from_page = Page_Model::find_by_uuid($from_page_uuid);
        if (!$from_page) throw new YZE_FatalException(__('page not found'));

        $from_class = Page_Bind_Data_Model::CLASS_NAME;
        $to_class = Page_Bind_Data_Model::CLASS_NAME;
        if ($from_type=='bind_api'){
            $from_class = Page_Bind_Api_Model::CLASS_NAME;
        }
        if ($to_type=='bind_api'){
            $to_class = Page_Bind_Api_Model::CLASS_NAME;
        }

        // 从api的响应数据赋值给别的数据时，只能赋值给一个（界面只能展示一个）
        if ($from_type=='bind_api'){
            $bound_variable = Page_Bind_Variable_Model::from()
                ->where('from_page_id=:fid and from_uuid=:fromuuid and from_expression=:exp')
                ->get_Single([':fid'=>$from_page->id, ':fromuuid'=>$input['data']['fromUuid']?:'', ':exp'=>json_encode($input)]);
            if ($bound_variable) $bound_variable->remove();
        }

        $bound_variable = Page_Bind_Variable_Model::from()
            ->where('from_page_id=:fid and to_page_id=:tid and to_uuid=:touuid and to_data_id=:to_data_id and from_uuid=:fromuuid')
            ->get_Single([':fid'=>$from_page->id, ':tid'=>$this->page->id, ':touuid'=>$to_uuid, ':to_data_id'=>$to_data_id?:$to_uuid, ':fromuuid'=>$input['data']['fromUuid']?:'']);
        if (!$bound_variable) {
            $bound_variable = new Page_Bind_Variable_Model();
            $bound_variable->set('uuid', Page_Bind_Variable_Model::uuid())
                ->set('from_page_id', $from_page->id)
                ->set('to_page_id', $this->page->id)
                ->set('to_uuid', $to_uuid);
        }
        $bound_variable->set('from_class', $input['data'] ? $from_class : '') // todo 先考虑只有页面数据，可能还有global数据
            ->set('from_expression', json_encode($input))
            ->set('from_uuid', $input['data']['fromUuid']?:'')
            ->set('to_class', $to_class)
            ->set('to_data_id', $to_data_id?:$to_uuid)
            ->set('to_data_path', $to_path?:'')
            ->save();
        return YZE_JSON_View::success($this);
    }
    // 删除页面输入数据的绑定信息
    public function post_deleteconnect(){
        $request = $this->request;
        $this->layout = '';
        $data = json_decode(file_get_contents('php://input'), true);
        $this->valid($data["to_page_uuid"]);
        $from_page_uuid = trim($data["from_page_uuid"]);
        $to_uuid = trim($data["to_uuid"]);
        $from_uuid = trim($data["from_uuid"]);

        $from_page = Page_Model::find_by_uuid($from_page_uuid);
        if (!$from_page) throw new YZE_FatalException(__('page not found'));

        $bound_variable = Page_Bind_Variable_Model::from()
            ->where('from_page_id=:fid and to_page_id=:tid and to_uuid=:touuid and is_deleted=0 and from_uuid=:fromuuid')
            ->get_Single([':fid'=>$from_page->id, ':tid'=>$this->page->id, ':touuid'=>$to_uuid, ':fromuuid'=>$from_uuid]);
        if ($bound_variable) {
            $bound_variable->remove();
        }
        return YZE_JSON_View::success($this);
    }
    // 拉取页面的数据，按名称排序
    public function data(){
        $request = $this->request;
        $this->layout = '';
        $this->valid($request->get_from_get("page_uuid"));
        $data_from = $request->get_from_get("data_from");

        $bindDatas = Page_Bind_Data_Model::get_page_bind_data($this->page->id, $data_from);
        $datas = ['page'=>[],'path'=>[],'query'=>[]];
        foreach ($bindDatas as $data){
            $datas[$data->data_from][] = $data->get_data_model();
        }
        $in_out = Page_Bind_Io_Model::get_io($this->page, Page_Bind_Data_Model::CLASS_NAME);
        $rst = ['page'=>[],'path'=>[],'query'=>[]];
        foreach ($datas as $date_from => $items){
            foreach ($items as $index => $data){
                $rst[$date_from][$index] = Page_Bind_Io_Model::get_bind_io($in_out, $data);
            }
        }
        // 按名称排序
        foreach ($rst as $date_from => $datas){
            usort($datas, function ($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });
            $rst[$date_from] = $datas;
        }
        return YZE_JSON_View::success($this, $rst);
    }
    // 拉取绑定的api
    public function api(){
        $request = $this->request;
        $this->layout = '';

        $this->valid($request->get_from_get("page_uuid"));
        $bindApis = Page_Bind_Api_Model::from('b')->where('b.page_id=:pid')
            ->left_join(Web_Api_Model::CLASS_NAME, 'api', 'api.id=b.web_api_id')
            ->order_By('id','asc','b')->select([':pid'=>$this->page->id]);
        $datas = [];

        $inVariables = Page_Bind_Variable_Model::get_left_to_right_variable($this->page, Page_Bind_Api_Model::CLASS_NAME);
        $outVariables = Page_Bind_Variable_Model::get_right_to_left_variable($this->page, Page_Bind_Api_Model::CLASS_NAME);

        foreach ($bindApis as $item){
            $bind = $item['b'];
            $web_api = $item['api'];

            $inBoundData = [];
            $outBoundData = [];
            if ($inVariables[$bind->uuid]){
                $inBoundData = array_merge($inBoundData, $inVariables[$bind->uuid]);
            }

            if ($outVariables[$bind->uuid]) {
                $outBoundData = array_merge($outBoundData, $outVariables[$bind->uuid]);
            }

            $record = [
                'uuid' => $bind->uuid,
                'api_uuid' => $web_api->uuid,
                'name' => $bind->name,
                'method' => $bind->method,
                'major' => $bind->major,
                'minor' => $bind->minor,
                'revision' => $bind->revision,
                'requestBodyType' => $bind->requestBodyType,
                'responseType' => $bind->get_response_type(),
                'version' => $bind->version,
                'inBoundData' => $inBoundData,
                'outBoundData' => $outBoundData,
                'hasNewVersion' => $web_api->version != $bind->version,
                'newVersion' => $web_api->major.'.'.$web_api->minor.'.'.$web_api->revision,
                'commit_msg' => $web_api->version != $bind->version ? $web_api->commit_msg : '',
                'path' => $bind->path,
                'trigger' => $bind->get_trigger_info(),// 触发该api调用的相关描述
                'localVariables' => $bind->get_local_Variables(),// 能设置该api的局部变量
                'input' => json_decode(html_entity_decode($bind->input), true)?:new \stdClass(),
                'output' => json_decode(html_entity_decode($bind->output), true)?:[]
            ];

            $datas[] = $record;
        }

        return YZE_JSON_View::success($this, $datas);
    }
    // 页面绑定api升级
    public function post_upgradeapi(){
        $request = $this->request;
        $this->layout = '';

        $this->valid($request->get_from_post("page_uuid"));

        $api_uuid = trim($request->get_from_post("api_uuid"));
        $page = $this->page;
        $bind_api = find_by_uuid(Page_Bind_Api_Model::CLASS_NAME, $api_uuid);
        if (!$bind_api) throw new YZE_FatalException(__('Api not found'));
        if ($bind_api->page_id != $page->id) throw new YZE_FatalException(__('Api not found'));

        $bind_api = Page_Bind_Api_Model::save_from_web_api($page, $bind_api->get_web_api(), $bind_api->bind_class, $bind_api->bind_uuid);
        return YZE_JSON_View::success($this, ['uuid'=>$bind_api->uuid]);
    }
    // 页面绑定api, 该api没有和任何事件及action绑定，用于UI组件引用到api到情况，比如file组件到自动上传文件api
    public function post_webapi(){
        $request = $this->request;
        $this->layout = '';
        $data = json_decode(file_get_contents('php://input'), true);

        $this->valid($data["page_uuid"]);
        $api_uuid = trim($data["api_uuid"]);
        $page = $this->page;
        $web_api = find_by_uuid(Web_Api_Model::CLASS_NAME, $api_uuid);
        if (!$web_api) throw new YZE_FatalException(__('Api not found'));

        $bind_api = Page_Bind_Api_Model::from()->where("is_deleted=0 and bind_class='' and bind_uuid=:uiid and page_id=:pid and web_api_id=:id")
            ->get_Single([':id'=>$web_api->id,':pid'=>$page->id, ':uiid'=>$data["uiid"]]);
        if (!$bind_api){
            $bind_api = Page_Bind_Api_Model::save_from_web_api($page, $web_api, '', $data["uiid"]);
        }
        return YZE_JSON_View::success($this, ['uuid'=>$bind_api->uuid]);
    }

    /**
     * 拉取API基本信息
     * @return YZE_JSON_View
     * @throws YZE_FatalException
     */
    public function apiinfo(){
        $request = $this->request;
        $this->layout = '';
        $getData = $request->the_get_datas();
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);

        $bindApi = find_by_uuid(Page_Bind_Api_Model::CLASS_NAME, $getData['uuid']);
        if (!$bindApi) throw new YZE_FatalException(__('Web Api not found'));
        $project = $bindApi->get_page()->get_project();

        $project_member = $project->get_member($loginUser->id);
        if (!$project_member) throw new YZE_FatalException(__('not a project member'));
        $webApi = $bindApi->get_web_api();

        $api = $bindApi->get_records();
        $content = json_decode(html_entity_decode($webApi->content), true)?:[];
        unset($api['id'], $api['content'], $api['api_folder_id'], $api['project_member_id']);
        $api += $content;
        $api_folder = $webApi->get_api_folder();
        if ($api_folder){
            $api['folder'] = ['uuid'=>$api_folder->uuid, 'name'=>$api_folder->name];
        }else{
            $api['folder'] = new \stdClass();
        }
        $project_member = $webApi->get_project_member();
        if ($project_member){
            $api['responsible'] = ['uuid'=>$project_member->uuid, 'name'=>$project_member->get_user()->nickname];
        }else{
            $api['responsible'] = new \stdClass();
        }
        foreach (Label_Model::get_labels($webApi) as $label){
            $api['label'][$label->uuid] = $label->name;
        }
        return YZE_JSON_View::success($this, $api);
    }

    /**
     * 对于绑定的数组子项，只能绑定在上层数组绑定的UI内部ui中
     * @param $from_uuid
     * @param $data_id
     * @param $check_uiid
     * @return void
     * @throws \yangzie\YZE_DBAException
     */
    private function check_bind_out($from_uuid, $data_id, $check_uiid, $output_as, $UIView){
        $bindDataModel = find_by_uuid(Page_Bind_Data_Model::CLASS_NAME, $from_uuid);
        if (!$bindDataModel) return;
        $dataConfig = $bindDataModel->get_data_model();
        $bindData = $bindDataModel->find_data($data_id, $dataConfig);

        $rows = Page_Bind_Data_Model::from('d')
            ->left_join(Page_Bind_Io_Model::CLASS_NAME, 'i', 'i.from_uuid=d.uuid and i.from_class=:cls')
            ->where('i.page_id=:pid and i.uiid=:uid and i.data_id!=:dataid and i.type="out"')
            ->select([':pid'=>$this->page->id, ':cls'=>Page_Bind_Data_Model::CLASS_NAME, ':dataid'=>$data_id, ':uid'=>$check_uiid]);
        $iterateOutputAs = null;
        foreach ($rows as $row){
            $checkDataConfig = $row['d']->get_data_model();
            $checkBindData = $row['d']->find_data($row['i']->data_id, $checkDataConfig);
            if ($UIView->need_iterate_ui($row['i']->output_as, $checkBindData)) {
                $iterateOutputAs = $row['i']->output_as;
                break;
            }
        }
        if ($iterateOutputAs && $UIView->need_iterate_ui($output_as, $bindData)){
            throw new YZE_FatalException(sprintf(__('Cannot bind both %s and %s at the same time, because they will both loop the UI'), $iterateOutputAs, $output_as));
        }

        // 判断指定的uiid是否已经有同类型的输出绑定(除了NONE)，一个uiid只能绑定一个
        $hasBound = Page_Bind_Io_Model::from()
            ->where("page_id=:pid and uiid=:uid and data_id!=:did and type='out' and output_as=:as")
            ->get_Single([':pid'=>$this->page->id,':uid'=>$check_uiid,':did'=>$data_id,':as'=>$output_as]);
        if  ($hasBound) {
            throw new YZE_FatalException(sprintf(__('target UI has bound another %s output'), $output_as));
        }

        // 对于绑定的数组子项，只能绑定在上层数组绑定的UI内部ui中
        $parentArrData = $bindDataModel->get_parent_of_data_id($data_id, ['array','map']);
        if (!$parentArrData) return;
        // 上级数据绑定的ui, 上级数据绑定了，才能绑定下级
        $bindIO = Page_Bind_Io_Model::from()->where("page_id=:pid and data_id=:did and type='out'")
            ->get_Single([':pid'=>$this->page->id, ':did'=>$parentArrData['uuid']]);
        if (!$bindIO) throw new YZE_FatalException(__('Please bind the superior data output first'));
        $parentBoundUI = $this->page->find_ui_item($bindIO->uiid);
        if (!$parentBoundUI) throw new YZE_FatalException(__('Please bind the superior data output first'));
        if (!$this->page->is_sub_ui($check_uiid, $parentBoundUI) && $check_uiid!=$parentBoundUI->meta->id) throw new YZE_FatalException(__('Must be bound to the subordinate UI of the UI bound to the superior array'));
    }

    private function check_bind_in($from_uuid, $data_id, $check_uiid){
        // 判断指定的uiid是否已经有同类型的输出绑定，一个uiid只能绑定一个
        $hasBound = Page_Bind_Io_Model::from()
            ->where("page_id=:pid and uiid=:uid and data_id!=:did and type='in'")
            ->get_Single([':pid'=>$this->page->id,':uid'=>$check_uiid,':did'=>$data_id]);
        if  ($hasBound) {
            throw new YZE_FatalException(__('target UI has bound another input'));
        }
    }
    // 绑定ui和data的关系
    public function post_io(){
        $request = $this->request;
        $this->layout = '';
        $this->valid($request->get_from_post("page_uuid"));

        $data_id = trim($request->get_from_post("data_id"));
        $ui_id = trim($request->get_from_post("ui_id"));
        $type = strtolower(trim($request->get_from_post("type")));
        $from_uuid = trim($request->get_from_post("from_uuid"));
        $output_as = trim($request->get_from_post("output_as"));
        $bound_as = trim($request->get_from_post("bound_as"))?:'none';

        // 同一个数据的输入和循环输出不能绑定在同一个ui上：出现了自己把自己改变了的情况

        $build = new Build_Model($this, $this->page, 0);
        $UIView = Preview_View::create_View($build);

        $this->check_bind_inout($from_uuid, $ui_id, $output_as, $data_id, $type, $UIView);

        if ($type=='in'){
            $this->check_bind_in($from_uuid, $data_id, $ui_id);
            $bind = Page_Bind_Io_Model::from()->where("page_id=:pid and data_id=:did and type='in' and from_class=:cls and from_uuid=:fid")
                ->get_Single([':pid'=>$this->page->id,':did'=>$data_id, ':cls'=>Page_Bind_Data_Model::CLASS_NAME, ':fid'=>$from_uuid]);
        }else{
            $this->check_bind_out($from_uuid, $data_id, $ui_id, $output_as, $UIView);
            $bind = Page_Bind_Io_Model::from()->where("page_id=:pid and uiid=:uid and data_id=:did and type='out' and from_class=:cls and from_uuid=:fid")
                ->get_Single([':pid'=>$this->page->id,':uid'=>$ui_id,':did'=>$data_id,':cls'=>Page_Bind_Data_Model::CLASS_NAME,':fid'=>$from_uuid]);

        }

        if (!$bind) {
            $bind = new Page_Bind_Io_Model();
            $bind->set('uuid', Page_Bind_Io_Model::uuid());
        }

        $bind->set('output_as', $output_as?:'')->set('bound_as', $bound_as?:'')
            ->set('uiid', $ui_id)
            ->set('data_id', $data_id)
            ->set('page_id', $this->page->id)
            ->set('type', $type)
            ->set('from_uuid', $from_uuid)
            ->set('from_class', Page_Bind_Data_Model::CLASS_NAME)
            ->save();

        return YZE_JSON_View::success($this, ['uuid'=>$bind->uuid]);
    }
    private function check_bind_inout($from_uuid, $ui_id, $output_as, $data_id, $type, $UIView){
        $hasBothBound = Page_Bind_Io_Model::from()->where("page_id=:pid and uiid=:uid and data_id=:did and type!=:type")
            ->get_Single([':pid'=>$this->page->id,':uid'=>$ui_id,':did'=>$data_id,':type'=>$type]);
        if  (!$hasBothBound) return;

        if ($type=='out'){
            $bindDataModel = find_by_uuid(Page_Bind_Data_Model::CLASS_NAME, $from_uuid);
        }else{
            $row = Page_Bind_Data_Model::from('d')
                ->left_join(Page_Bind_Io_Model::CLASS_NAME, 'i', 'i.from_uuid=d.uuid and i.from_class=:cls')
                ->where('i.page_id=:pid and i.uiid=:uid and i.data_id=:dataid and i.type="out"')
                ->get_Single([':pid'=>$this->page->id, ':cls'=>Page_Bind_Data_Model::CLASS_NAME, ':dataid'=>$data_id, ':uid'=>$ui_id]);
            $bindDataModel = $row['d'];
            $output_as = $row['i']->output_as;
        }
        if (!$bindDataModel) return;

        $checkDataConfig = $bindDataModel->get_data_model();
        $checkBindData = $bindDataModel->find_data($data_id, $checkDataConfig);
        if ($UIView->need_iterate_ui($output_as, $checkBindData)) {
            throw new YZE_FatalException(__('Loop output and input cannot be bound to the same UI at the same time'));
        }
    }
    // 保存页面的数据
    public function post_data(){
        $request = $this->request;
        $this->layout = '';
        $post_data = json_decode(trim(file_get_contents("php://input")), true);
        $this->valid($post_data['page_uuid']);
        $page = $this->page;


        $saveHelper = new Save_Model_Helper();
        $saveHelper->alias_classes = Page_Bind_Data_Model::CLASS_NAME;
        $saveHelper->fetch_modify_model = function($data) {
            if ($data['uuid']){
                $model = find_by_uuid(Page_Bind_Data_Model::CLASS_NAME, $data['uuid']);
            }
            if (!$model) {
                $model = new Page_Bind_Data_Model();
                $model->set('uuid', Page_Bind_Data_Model::uuid());
            }
            return $model;
        };
        $saveHelper->valid_column_and_rules = [
            'name'=>function ($model, $value) {
                $value = trim($value);
                if (!$value) throw new YZE_FatalException(__('Please input name'));
                if (in_array(strtolower($value), $this->words())) throw new YZE_FatalException(__('The data name cannot be a reserved keyword'));
                $bind_data = Page_Bind_Data_Model::from()
                    ->where('name=:name and is_deleted=0 and id!=:id and page_id=:pid')
                    ->get_Single([':name'=>$value,':id'=>intval($model->id),':pid'=>$this->page->id]);
                if ($bind_data)
                    throw new YZE_FatalException(sprintf(__('Data name 「%s」 has exist'), $value));
            }
        ];
        $post_data['page_id'] = $page->id;
        if ($post_data['type']=='array'){
            $post_data['content'] = json_encode($post_data['item'], JSON_UNESCAPED_UNICODE);
        }else{
            $post_data['content'] = json_encode($post_data['props'], JSON_UNESCAPED_UNICODE);
        }
        $post_data['enumValue'] = json_encode($post_data['enumValue'], JSON_UNESCAPED_UNICODE);
        $bind_data = $saveHelper->save($post_data);
        return YZE_JSON_View::success($this, ['uuid'=>$bind_data->uuid]);
    }
    private function words(){
        return [
            'break',
            'case',
            'catch',
            'class',
            'const',
            'continue',
            'debugger',
            'default',
            'delete',
            'do',
            'else',
            'enum', // 保留字，但在ES中没有实际功能
            'export',
            'extends',
            'finally',
            'for',
            'function',
            'if',
            'implements', // 保留字，但在ES中没有实际功能
            'import',
            'in',
            'instanceof',
            'interface', // 保留字，但在ES中没有实际功能
            'let',
            'new',
            'package', // 保留字，但在ES中没有实际功能
            'private', // 提案中的关键字，尚未在ES规范中正式定义
            'protected', // 提案中的关键字，尚未在ES规范中正式定义
            'public', // 提案中的关键字，尚未在ES规范中正式定义
            'return',
            'static',
            'super',
            'switch',
            'this',
            'throw',
            'try',
            'typeof',
            'var',
            'void',
            'while',
            'with',
            'yield',
            'async',
            'await',
            'true',
            'false',
            'null',
            'undefined',
            'NaN',
            'Infinity'
        ];
    }
    // 页面删除绑定api
    /**
     * @deprecated
     * @return mixed
     * @throws YZE_FatalException
     */
    public function post_deleteapi(){
        $request = $this->request;
        $this->layout = '';
        throw new YZE_FatalException(__('deprecated api'));
    }
    // 页面删除绑定数据
    public function post_deletedata(){
        $request = $this->request;
        $this->layout = '';
        $uuid = trim($request->get_from_post("uuid"));
        $bind_data = find_by_uuid(Page_Bind_Data_Model::CLASS_NAME, $uuid);
        Page_Bind_Io_Model::from()->where('from_class=:cls and from_uuid=:id')
            ->delete([':cls'=>Page_Bind_Data_Model::CLASS_NAME,':id'=>$bind_data->uuid]);
        $bind_data->remove();
        return YZE_JSON_View::success($this, []);
    }
    // 删除绑定关系
    public function post_remove(){
        $request = $this->request;
        $this->layout = '';
        $page_uuid = trim($request->get_from_post("page_uuid"));
        $this->valid($page_uuid);
        $ui_id = trim($request->get_from_post("ui_id"));
        $data_id = trim($request->get_from_post("data_id"));
        $type = trim($request->get_from_post("type"));
        Page_Bind_Io_Model::from()->where('uiid=:uid and data_id=:did and `type`=:type and page_id=:pid')
            ->delete([':uid'=>$ui_id, ':did'=>$data_id, ':type'=>$type, ':pid'=>$this->page->id]);
        return YZE_JSON_View::success($this, []);
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }
}
?>
