<?php
namespace app\api;
use app\project\Page_Bind_Data_Model;
use app\project\Page_Bind_State_Model;
use app\project\Page_Bind_Style_Model;
use app\project\Page_Model;
use app\project\Style_Model;
use yangzie\YZE_FatalException;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use \yangzie\YZE_JSON_View;
use function yangzie\__;

/**
*
* @version $Id$
* @package api
*/
class State_Controller extends YZE_Resource_Controller {
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
    // 拉取指定页面指定ui的所有state及其style
    public function index(){
        $request = $this->request;
        $this->layout = '';
        $page_uuid = trim($request->get_from_get("page_uuid"));
        $uiid = trim($request->get_from_get("uiid"));
        $this->valid($page_uuid);

        $bindStates = Page_Bind_State_Model::from()->where('page_id=:pid and uiid=:uiid')
            ->select([':pid'=>$this->page->id,':uiid'=>$uiid]);
//        var_dump([':pid'=>$this->page->id,':uiid'=>$uiid], 'page_id=:pid and uiid=:uiid');
        $datas = [];
        foreach ($bindStates as $pageState){
            $state = $pageState->get_state_data();
            $style = json_decode(html_entity_decode($pageState->style))?:[];
            if ($pageState->style_id){
                $selectors = Style_Model::find_by_ids($pageState->style_id);
                $selectorStyle = [];
                foreach ($selectors as $selector){
                    $selectorStyle = array_merge((array)$selectorStyle, (array)json_decode(html_entity_decode($selector->meta)));
                }
                $style->selector = $selectorStyle;
            }
            if ($pageState->state_type=='custom'){
                $datas[$pageState->uuid] = ['state'=>$state,'style'=>['meta'=>$style?:new \stdClass()]];
            }else if($pageState->state_type=='pseudo'){
                $datas[$pageState->state_name] = ['state'=>$state,'style'=>['meta'=>$style?:new \stdClass()]];
            }else{
                $datas[$pageState->state_type] = ['state'=>$state,'style'=>['meta'=>$style?:new \stdClass()]];
            }
        }
        return YZE_JSON_View::success($this, $datas?:new \stdClass());
    }
    public function post_add(){
        $request = $this->request;
        $data = json_decode(file_get_contents('php://input'), true);
        $page_uuid = $data['page_uuid'];
        $this->valid($page_uuid);
        $uiid = $data['uiid'];
        $state = $data['state'];

        $bindData = Page_Bind_Data_Model::find_by_uuid($state['leftRootDataId']);
        $bindState = $state['uuid'] ? Page_Bind_State_Model::find_by_uuid($state['uuid']) : null;
        if (!$bindState && $state['type']!='custom'){
            $bindState = Page_Bind_State_Model::from()->where('state_type=:type and state_name=:name and page_id=:pid and uiid=:uiid')
                ->get_Single([':type'=>$state['type'], ':name'=>$state['name'], ':pid'=>$this->page->id, ':uiid'=>$uiid]);
        }
        if (!$bindState){
            $bindState = new Page_Bind_State_Model();
            $bindState->set('uuid', Page_Bind_State_Model::uuid())
                ->set('page_id', $this->page->id);
        }
        $bindState->set('uiid', $uiid)
            ->set('state_type', $state['type'])
            ->set('state_name', $state['name']?:$state['type'])
            ->set('expression', json_encode($state['expression']))
            ->save();


        $stateInfo =$bindState->get_state_data();

        if ($bindState->state_type=='custom'){
            $data = ['key'=>$bindState->uuid, 'state'=>['state'=>$stateInfo,'style'=>['meta'=>new \stdClass()]]];
        }else if($bindState->state_type=='pseudo'){
            $data = ['key'=>$bindState->state_name, 'state'=>['state'=>$stateInfo,'style'=>['meta'=>new \stdClass()]]];
        }else{
            $data = ['key'=>$bindState->state_type, 'state'=>['state'=>$stateInfo,'style'=>['meta'=>new \stdClass()]]];
        }
        return YZE_JSON_View::success($this, $data);
    }
    public function post_remove(){
        $request = $this->request;
        $data = json_decode(file_get_contents('php://input'), true);
        $page_uuid = $data['page_uuid'];
        $state_uuid = $data['state_uuid'];
        $this->valid($page_uuid);

        $bindState= Page_Bind_State_Model::find_by_uuid($state_uuid);
        if ($bindState){
            $bindState->remove();
        }
        return YZE_JSON_View::success($this);
    }
    // 绑定state的style, 没有state则创建, 但如果有state却没有style就删除
    public function post_style(){
        $request = $this->request;
        $this->layout = '';
        $data = json_decode(file_get_contents("php://input"));
        $this->valid($data->page_uuid);
        $state_uuid = trim($data->state_uuid);
        $uiid = trim($data->uiid);
        $state_type = trim($data->state_type);
        $state_name = trim($data->state_name);
        $style = $data->style;
        $bindState = Page_Bind_State_Model::find_by_uuid($state_uuid);
        if ($state_uuid && !$bindState) throw new YZE_FatalException(__('State not exist'));
        if (!$style || !$uiid) return YZE_JSON_View::success($this);

        if (!$bindState && $state_type != 'custom'){
            $bindState = Page_Bind_State_Model::from()->where('state_type=:type and state_name=:name and page_id=:pid and uiid=:uiid')
                ->get_Single([':type'=>$state_type, ':name'=>$state_name, ':pid'=>$this->page->id, ':uiid'=>$uiid]);
        }
        if (!$bindState){
            $bindState = new Page_Bind_State_Model();
            $bindState->set('uuid', Page_Bind_State_Model::uuid())
                ->set('page_id', $this->page->id)
                ->set('uiid', $uiid)
                ->set('state_type', in_array($state_type, ['activated','disabled','hidden', 'pseudo']) ? $state_type : 'custom')
                ->set('state_name', $state_name?:$state_type)
                ->save();
        }
        if($state_uuid && !(array)$style && !$bindState->expression){
            $bindState->remove();
            return YZE_JSON_View::success($this);
        }else{
            $bindState->set('style', json_encode($style))->save();
        }

        return YZE_JSON_View::success($this,['uuid'=>$bindState->uuid]);
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }
}
?>
