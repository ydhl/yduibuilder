<?php
namespace app\api;
use app\project\Function_Model;
use app\project\Page_Bind_State_Model;
use app\project\Page_Bind_Style_Model;
use app\project\Page_Model;
use app\project\Page_User_Model;
use app\project\Page_Version_Model;
use app\project\Project_Member_Model;
use app\project\Uicomponent_Instance_Model;
use app\vendor\Save_Model_Helper;
use yangzie\YZE_DBAImpl;
use yangzie\YZE_FatalException;
use yangzie\YZE_Hook;
use yangzie\YZE_Model;
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
class Copy_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }

    /**
     * 前端复制ui或者使用组件，这时需要把所复制的ui中的id重现生成新的id，并且对应的更改style绑定关系；
     * 同时复制没有使用变量的state，如伪类
     * 原UI的其他绑定，在创建后的ui中不包含
     */
    public function post_ui(){
        $request = $this->request;
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $this->layout = '';
        $data = json_decode(file_get_contents("php://input"));
        $page_uuid = $data->page_uuid;
        $uiconfig  = $data->uiconfig;
        $uiids = [];
        $subPageIds = [];
        Page_Model::all_sub_item_uiid($uiconfig, $uiids, $subPageIds);
        if (!$uiids) return YZE_JSON_View::success($this, $uiconfig);
        $page = find_by_uuid(Page_Model::CLASS_NAME, $page_uuid);
        if (!$page) throw new YZE_FatalException(__('page not found'));
        $oldID2newID = [];
        foreach ($uiids as $uiid=>$type){
            $newID = uuid(5,null, $page_uuid);
            $oldID2newID[$uiid] = $newID;
        }

        // 复制normal的style
        $sql = "insert into page_bind_style(uuid, page_id, style_id, uiid) values";
        $insertValues = [];
        foreach (Page_Bind_Style_Model::from()
            ->where("page_id=:pid and uiid in ('".join("','", array_keys($uiids))."') and is_deleted=0")
                     ->select([':pid'=>$page->id]) as $oldBind){
            if (!$oldID2newID[$oldBind->uiid]) continue;
            $insertValues[] = "(uuid(), {$page->id}, {$oldBind->style_id}, '{$oldID2newID[$oldBind->uiid]}')";
        }
        if ($insertValues) YZE_DBAImpl::get_instance()->exec($sql.join(',', $insertValues));

        // 复制伪类的style
        $sql = "insert into page_bind_state(uuid, page_id, uiid, state_type, state_name, style, style_id, expression) values";
        $insertValues = [];
        foreach (Page_Bind_State_Model::from()
            ->where("page_id=:pid and uiid in ('".join("','", array_keys($uiids))."') and is_deleted=0 and state_type='pseudo'")
                     ->select([':pid'=>$page->id]) as $oldBind){
            if (!$oldID2newID[$oldBind->uiid]) continue;
            $style = $oldBind->style ? html_entity_decode($oldBind->style) : '';
            $expression = $oldBind->expression ? html_entity_decode($oldBind->expression) : '';
            $style_id = $oldBind->style_id ?: 'NULL';
            $insertValues[] = "(uuid(), {$page->id}, '{$oldID2newID[$oldBind->uiid]}', '{$oldBind->state_type}', '{$oldBind->state_name}', '{$style}',{$style_id},'{$expression}')";
        }
        if ($insertValues) YZE_DBAImpl::get_instance()->exec($sql.join(',', $insertValues));

        $uiconfig = Page_Model::replace_uiid($uiconfig, $oldID2newID);
        $uiconfig = Page_Model::remove_node($uiconfig);

        if ($subPageIds){
            foreach ($subPageIds as $subPageId=>$uiid){
                $uicomponentPage = Page_Model::find_by_uuid($subPageId);
                if ($uicomponentPage){
                    Uicomponent_Instance_Model::add_instance($page->id, $uicomponentPage->id, $oldID2newID[$uiid]);
                }
            }
        }

        return YZE_JSON_View::success($this, $uiconfig);
    }

    /**
     * 复制指定的page id， 和api/save接口的区别是，api/save复制的情况是传入完整的复制页面的config
     * @return YZE_JSON_View
     * @throws \yangzie\YZE_DBAException
     */
    public function post_page(){
        $request = $this->request;
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $this->layout = '';
        $data = json_decode(file_get_contents('php://input'), true);
        $copy_page_uuid = $data['copy_page_uuid'];
        $copyFromPage = find_by_uuid(Page_Model::CLASS_NAME, $copy_page_uuid);
        if (!$copyFromPage) return YZE_JSON_View::error($this, __('Page Not Found'));
        $project = $copyFromPage->get_project();

        $pageConfig = json_decode(html_entity_decode($copyFromPage->config));

        // 如果当前编辑的是组件，则没有function，module
        $function = Function_Model::find_by_id($copyFromPage->function_id);
        if ($function) {
            $module = $function->get_module();
            $project = $module->get_project();
        }

        $member = $project->get_member($loginUser->id);
        if (!$member || !$member->can_edit()){
            return YZE_JSON_View::error($this, __('you can not edit in this project'));
        }
        $permission = new \Check_User_Permission();
        $permission->user = $loginUser;
        YZE_Hook::do_hook(CHECK_USER_PERMISSION, $permission);

        $oldID2newID = [];
        $uiids = [];
        Page_Model::all_sub_item_uiid($pageConfig, $uiids);
        foreach ($uiids as $uiid=>$type){
            $newID = uuid(5,null, 'Page'.$project->id);
            $oldID2newID[$uiid] = $newID;
        }
        $pageConfig = Page_Model::replace_uiid($pageConfig, $oldID2newID);
        $pageConfig->meta->title .= ' Copy';

        $currPage = new Page_Model();
        $currPage->set(Page_Model::F_UUID, $pageConfig->meta->id)
            ->set(Page_Model::F_FUNCTION_ID, $function->id)
            ->set(Page_Model::F_PROJECT_ID, $project->id)
            ->set(Page_Model::F_CREATE_USER_ID, $loginUser->id)
            ->set(Page_Model::F_PAGE_TYPE, $copyFromPage->page_type)
            ->set(Page_Model::F_MODULE_ID, $module->id)
            ->set(Page_Model::F_NAME, @$pageConfig->meta->title)
            ->set(Page_Model::F_URL, @$pageConfig->meta->custom->url?:'')
            ->set(Page_Model::F_FILE, @$pageConfig->meta->custom->file)
            ->set(Page_Model::F_IS_SNAPSHOTING, 1)
            ->set(Page_Model::F_MODIFIED_ON, date('Y-m-d H:i:s'))
            ->set(Page_Model::F_CONFIG, json_encode($pageConfig))
            ->save();


        $currPage->copy_bind_from($copyFromPage, $oldID2newID);

        $need_activity = false;
        $last_version = $currPage->get_last_version();
        if (!$last_version){
            $last_version = Page_Version_Model::save_version($member->id, $currPage, sprintf(__('copy from %s'),$copyFromPage->name));
            $need_activity = true;
        }

        if ($need_activity){
            $data = [
                'project_id'=>$member->project_id,
                'member_id'=>$member->id,
                'content'=> ($currPage->screen ? '<img src="/download?file='.urlencode($currPage->screen).'" style="width: 50px"/>' : '').vsprintf(__('Save UI, Version: %s'), $last_version->index),
                'type'=>'ui'];
            YZE_Hook::do_hook(YDE_CLOUD_PROJECT_ACTIVITY, $data);
        }

        $screen_path = "/screen/{$project->uuid}/{$pageConfig->meta->id}-{$last_version->id}.jpg";
        $currPage->set(Page_Model::F_SCREEN, OSS_BUCKET_HOST.$screen_path)->save();
        $last_version->set(Page_Model::F_SCREEN, $currPage->screen)->save();

        $member->set('last_page_id', $currPage->id)
            ->set('last_function_id', $function->id?:0)
            ->save();

        $project->set('last_page_id', $currPage->id)
            ->set('last_function_id', $function->id?:0)
            ->save();

        $width = 1024;
        $height = 768;
        post_snapshot_message($currPage->page_type != 'popup', $pageConfig->meta->id, SITE_URI.'preview/page/'.$pageConfig->meta->id, $screen_path, $width, $height);
        $currPage->remove_gone_uiid();
        return YZE_JSON_View::success($this, $currPage->get_config());
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }
}
?>
