<?php
namespace app\api;
use app\project\Function_Model;
use app\project\Page_Bind_Style_Model;
use app\project\Page_Model;
use app\project\Page_Version_Model;
use app\project\Project_Member_Model;
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
class Save_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }

    /**
     * 只保存当前页面,当前页面可能是一个页面（有module，function关联），也可能是一个组件（没有module，function关联）
     * @actionname 设计器保存
     */
    public function post_index(){
        $request = $this->request;
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $this->layout = '';
        /** 注意不能解码成关联数组，这样会把客户端的json对象{}变成了json数组[] **/
        $post_data = json_decode(html_entity_decode($request->get_from_post('data')));
        $newVersion = $request->get_from_post('new_version');
        $commitMessage = trim($request->get_from_post('message'));
        $copy = intval($request->get_from_post('copy'));
        $pageConfig = $post_data->page;
        $versionId = intval($post_data->versionId); // 当前保存的页面版本id，-1表示第一次保存
        if (!$pageConfig->meta->id) return YZE_JSON_View::error($this, __('Page Not Found'));
        $currPage = $copyFromPage = find_by_uuid(Page_Model::CLASS_NAME, $pageConfig->meta->id);

        if ($copy && !$currPage) return YZE_JSON_View::error($this, __('Page Not Found'));

        $pageType = $pageConfig->pageType ?: 'page';
        if ($currPage){
            $project = $currPage->get_project();
        }
        // 复制页面的情况，先取出project后在清空
        $currPage = $copy ? null : $currPage;

        // 如果当前编辑的是组件，则没有function，module
        $function = find_by_uuid(Function_Model::CLASS_NAME, $post_data->functionId);
        if ($function) {
            $module = $function->get_module();
            $project = $module->get_project();
        }

        $member = $project->get_member($loginUser->id);
        if (!$member || !$member->can_edit()){
            return YZE_JSON_View::error($this, __('you can not edit in this project'));
        }

        $oldID2newID = [];
        if ($copy){
            $uiids = [];
            Page_Model::all_sub_item_uiid($pageConfig, $uiids);
            foreach ($uiids as $uiid=>$type){
                $newID = uuid(5,null, 'Page'.$project->id);
                $oldID2newID[$uiid] = $newID;
            }
            $pageConfig = Page_Model::replace_uiid($pageConfig, $oldID2newID);
            $pageConfig->meta->title .= ' Copy';
        }

        if (!$currPage){
            $currPage = new Page_Model();
            $currPage->set(Page_Model::F_UUID, $pageConfig->meta->id)
                ->set(Page_Model::F_FUNCTION_ID, $function->id)
                ->set(Page_Model::F_PROJECT_ID, $project->id)
                ->set(Page_Model::F_CREATE_USER_ID, $loginUser->id)
                ->set(Page_Model::F_PAGE_TYPE, $pageType)
                ->set(Page_Model::F_MODULE_ID, $module->id);
        }
        $currPage->set(Page_Model::F_NAME, @$pageConfig->meta->title)
            ->set(Page_Model::F_URL, @$pageConfig->meta->custom->url?:'')
            ->set(Page_Model::F_FILE, @$pageConfig->meta->custom->file)
            ->set(Page_Model::F_IS_SNAPSHOTING, 1)
            ->set(Page_Model::F_MODIFIED_ON, date('Y-m-d H:i:s'))
            ->set(Page_Model::F_CONFIG, json_encode($pageConfig));

        if (!$currPage->id){
            // 新增
            $currPage->save();
            if ($copy) {
                // 复制页面的关联内容
                $currPage->copy_bind_from($copyFromPage, $oldID2newID);
            }
        }else{
            // 修改，修改防止多人同时保存
            $records = $currPage->get_records();
            $updateSets = [];
            $dba = YZE_DBAImpl::get_instance();
            foreach ($records as $column => $value) {
                $updateSets[] = "`{$column}` = ".$dba->quote($value);
            }
            $sql = "UPDATE `page` set ".join(",",$updateSets)." 
            WHERE `id` = ".$currPage->id." and {$versionId} = (select b.last_version_id from (select p.last_version_id from `page` as p where p.id=".$currPage->id." ) as b)";
//            echo $sql;
            try{
                $affected = YZE_DBAImpl::get_instance()->exec($sql);
                if ($affected<=0) throw new YZE_FatalException('');
            }catch (\Exception $e){
                $currPage->refresh();
                $last_version = $currPage->get_last_version();
                if ($last_version){
                    return YZE_JSON_View::error($this, sprintf(__('page has been saved by %s at %s (%s)'),
                        $last_version->get_project_member()->get_user()->nickname, $last_version->created_on,$last_version->index));
                }else{
                    return YZE_JSON_View::error($this, __('page has been saved by other'));
                }
            }
        }
        $need_activity = false;
        if ($newVersion){
            $last_version = Page_Version_Model::save_version($member->id, $currPage, $commitMessage);
            $need_activity = true;
        }else{
            $last_version = $currPage->get_last_version();
        }
        if (!$last_version){
            $last_version = Page_Version_Model::save_version($member->id, $currPage, $commitMessage?:'init');
            $need_activity = true;
        }

        if ($need_activity){
            $data = [
                'project_id'=>$member->project_id,
                'member_id'=>$member->id,
                'content'=> ($currPage->screen ? '<img src="'.YZE_UPLOAD_PATH.$currPage->screen.'" style="width: 50px"/>' : '').vsprintf(__('Save UI, Version: %s'), $last_version->index),
                'type'=>'ui'];
            YZE_Hook::do_hook(YDE_CLOUD_PROJECT_ACTIVITY, $data);
        }

        $screen_path = "/screen/{$project->uuid}/{$pageConfig->meta->id}-{$last_version->id}.jpg";
        $currPage->set(Page_Model::F_SCREEN, YZE_UPLOAD_PATH.$screen_path)->save();
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
        return YZE_JSON_View::success($this, [ 'versionId' => $last_version->id, 'page_uuid'=> $currPage->uuid ]);
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }
}
?>
