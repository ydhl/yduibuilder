<?php

namespace app\api;

use app\common\File_Model;
use app\project\Page_Bind_Style_Model;
use app\project\Page_Model;
use app\project\Uicomponent_Instance_Model;
use app\vendor\Uploader;
use app\project\Project_Model;
use yangzie\YZE_FatalException;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_RuntimeException;
use yangzie\YZE_Hook;
use yangzie\YZE_JSON_View;
use yangzie\yze_remove_path;
use function yangzie\__;


class Uicomponent_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    public function index() {
        $request = $this->request;
        $this->layout = '';
        $uuid = trim($request->get_from_get("uuid"));
        $is_page = trim($request->get_from_get("is_page"));// 只查询页面类型的组件
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $project = find_by_uuid(Project_Model::CLASS_NAME, $uuid);
        $member = $project->get_member($loginUser->id);
        if (!$member){
            return YZE_JSON_View::error($this, __('project not found'));
        }

        // 该项目中我及其他人创建的ui组件
        $projectLibs = Page_Model::from('lib')
            ->where("lib.project_id=:pid and lib.is_deleted=0 and is_component=1 and ".($is_page ? "lib.`page_type`!='component'" : "lib.`page_type`='component'"))
            ->order_By('id','asc', 'lib')
            ->select([':pid'=>$project->id]);
        $projectUIComponents = [];
        foreach ($projectLibs as $projectLib){
            $config = json_decode(html_entity_decode($projectLib->config));
            $projectUIComponents[] = [
                "isInput" => (bool)$config->meta->form,
                "id" => $projectLib->uuid,
                "kind" => [$projectLib->component_end_kind],
                "name" => $projectLib->name,
                "type" => $config->type
            ];
        }

        // 我在其他同类型end kind的项目中创建的组件
        $myLibs = Page_Model::from('my')
            ->where("my.create_user_id=:myid and my.is_deleted=0 and my.create_user_is_deleted=0 and my.project_id!=:pid and my.is_component=1 and my.component_end_kind=:endkind and "
                .($is_page ? "my.`page_type`!='component'" : "my.`page_type`='component'"))
            ->order_By('id','asc', 'my')
            ->select([':myid'=>$loginUser->id, ':pid'=>$project->id, ':endkind'=>$project->end_kind], 'my');
        $myUIComponents = [];
        foreach ($myLibs as $myLib){
            $config = json_decode(html_entity_decode($myLib->config));
            $myUIComponents[] = [
                "isInput" => (bool)$config->meta->form,
                "id"=>$myLib->uuid,
                "kind" => [$myLib->component_end_kind],
                "name" => $myLib->name,
                "type" => $config->type
            ];
        }
        $data = [
            'projectUIComponent'=>$projectUIComponents,
            'myUIComponent'=>$myUIComponents
        ];
        return YZE_JSON_View::success($this, $data);
    }
    // 创建ui组件
    public function post_export(){
        $request = $this->request;
        $this->layout = '';
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $pageid = trim($request->get_from_post("pageid"));
        $uiid = trim($request->get_from_post("uiid"));
        $name = trim($request->get_from_post("name"));

        if (!$name) throw new YZE_FatalException(__("Please type UI Name"));
        $page = find_by_uuid(Page_Model::CLASS_NAME, $pageid);
        if (!$page) throw new YZE_FatalException(__("Page Not Found"));
        $config = $page->find_ui_item($uiid);
        if (!$config) throw new YZE_FatalException(__("UI Item Not Found"));
        $project = $page->get_project();

        // 补全uiBase结构体的数据
        $config->pageType = strcasecmp($config->type, 'page') ? Page_Model::PAGE_TYPE_COMPONENT : Page_Model::PAGE_TYPE_PAGE;
        $config->meta->title = $name;

        $uicomponent = new Page_Model();
        $uicomponent->set('create_user_id',$loginUser->id)
            ->set('uuid',Page_Model::uuid())
            ->set('name', $name)
            ->set('config', '')
            ->set('page_type', strcasecmp($config->type, 'page') ? Page_Model::PAGE_TYPE_COMPONENT : Page_Model::PAGE_TYPE_PAGE)
            ->set('project_id',$project->id)
            ->set('is_component',1)
            ->set('component_end_kind', $project->end_kind)
            ->set('component_uiid', $uiid)
            ->save();

        //leeboo@20231228 如果创建的ui使用了selector，则建立关联
        $uiids = [];
        Page_Model::all_sub_item_uiid($config, $uiids);
        $oldID2newID = [];
        foreach ($uiids as $uiid=>$type){
            $oldID2newID[$uiid] = uuid(5,null,'page'.$uicomponent->id);
        }
        $this->copy_ui_bind_style($uicomponent, $page, $uiids, $oldID2newID);
        $uiconfig = Page_Model::replace_uiid($config, $oldID2newID);
        $uicomponent->set('uuid', $uiconfig->meta->id)// uuid就是meta中的id
            ->set('config', json_encode($uiconfig))
            ->save();

        return YZE_JSON_View::success($this);
    }
    public function info() {
        $request = $this->request;
        $this->layout = '';
        $uiid = trim($request->get_from_get("uiid"));
        $page_uuid = trim($request->get_from_get("page_uuid"));
        $page = find_by_uuid(Page_Model::CLASS_NAME, $page_uuid);
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $project = $page ? $page->get_project() : null;
        $member = $project->get_member($loginUser->id);
        if (!$member || !$page){
            return YZE_JSON_View::error($this,[$loginUser->id, $project->id]);
        }
        $uicomponent = Page_Model::from()
            ->where('project_id=:pid and is_deleted=0 and is_component=1 and component_uiid=:uiid')
            ->order_By('id', 'asc')
            ->get_Single([':pid'=>$project->id, ':uiid'=>$uiid]);

        $config = $page->find_ui_item($uiid);
        $uiids = [];
        Page_Model::all_sub_item_uiid($config, $uiids);

        if ($uicomponent){
            $data = [];
            if ($uicomponent){
                $data['name'] =$uicomponent->name;
                $data['msg'] =  sprintf(__("%s has been created as component. at %s"),
                            $uicomponent->get_user()->nickname, $uicomponent->created_on);
            }
            return YZE_JSON_View::success($this, $data);
        }
        return YZE_JSON_View::error($this);
    }

    /**
     * 拉取组件信息
     * @return YZE_JSON_View
     */
    public function post_detail() {
        $request = $this->request;
        $this->layout = '';
        $target_id = trim($request->get_from_post("target_id"));// 组件的page model uuid
        $uuid = trim($request->get_from_post("uuid"));// 组件的page model uuid也是uiid
        $instance_uuid = trim($request->get_from_post("instance_uuid"));// 创建的实例id
        $page_uuid = trim($request->get_from_post("page_uuid"));//当前页面uuid
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);

        $uicomponent = find_by_uuid(Page_Model::CLASS_NAME, $uuid);
        if (!$uicomponent){
            return YZE_JSON_View::error($this, __('UI Component not found'));
        }
        $project = $uicomponent->get_project();
        $member = $project->get_member($loginUser->id);
        if (!$member && $uicomponent->create_user_id != $loginUser->id){
            return YZE_JSON_View::error($this, __('UI Component not found.'));
        }
        // 在指定的页面中运用组件
        $page = find_by_uuid(Page_Model::CLASS_NAME, $page_uuid);
        if ($page){
            //检查是否自己包含了自己
            if($page->is_parent_contain_sub_page($target_id, $uuid)) return YZE_JSON_View::error($this, __('UI Component cannot contain oneself.'));
            $uiconfig = $uicomponent->get_config();
            $instance = new Uicomponent_Instance_Model();
            $instance->set('uuid', Uicomponent_Instance_Model::uuid())
                ->set('page_id', $page->id)
                ->set('uicomponent_page_id', $uicomponent->id)
                ->set('instance_uuid', $instance_uuid)
                ->save();
        }
        return YZE_JSON_View::success($this, $uiconfig);
    }

    /**
     * 把 source page中uiid关联的selector复制到dest page中, 并把uiid重新替换成$oldID2newID的内容
     * @param $destPage
     * @param $sourcePage
     * @param $uiids
     * @param $oldID2newID
     * @return void
     */
    private function copy_ui_bind_style($destPage, $sourcePage, $uiids, $oldID2newID) {
        foreach (Page_Bind_Style_Model::from()
                     ->where("page_id=:uicomponent and uiid in ('".join("','", array_keys($uiids))."' and is_deleted=0)")
                     ->select([':uicomponent'=>$sourcePage->id]) as $oldBind){
            $json = $oldBind->get_records();
            unset($json['id'],$json['created_on'],$json['modified_on'],$json['uuid']);
            $json['uuid'] = Page_Bind_Style_Model::uuid();
            $json['page_id'] = $destPage->id;
            $json['uiid'] = $oldID2newID[$oldBind->uiid]?:$oldBind->uiid;
            Page_Bind_Style_Model::from_Json($json)->save();
        }
    }
    public function exception(\Exception $e) {
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }

}

?>
