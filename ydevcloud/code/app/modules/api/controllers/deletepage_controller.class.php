<?php
namespace app\api;
use app\project\Function_Model;
use app\project\Page_Model;
use app\project\Page_User_Model;
use app\project\Project_Member_Model;
use app\vendor\Save_Model_Helper;
use yangzie\YZE_DBAImpl;
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
class Deletepage_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }

    /**
     * 页面移到回收站，并返回下一个（如果有）或者上一个（如果有）页面
     * @actionname 设计器保存
     */
    public function post_index(){
        $request = $this->request;
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $this->layout = '';
        $pageid = $request->get_from_post('pageid');
        $type = $request->get_from_post('type');// 指定删除的页面必须是type类型
        $page = find_by_uuid(Page_Model::CLASS_NAME, $pageid);
        if (!$page || ($type && $page->page_type!=$type)) return YZE_JSON_View::error($this, __('Page not found'));

        $project = $page->get_project();
        $member = $project->get_member($loginUser->id);
        if (!$member || !$member->can_edit()){
            return YZE_JSON_View::error($this, __('you can not edit in this project'));
        }
        $data = [
            'project_id'=>$member->project_id,
            'member_id'=>$member->id,
            'content'=> ($page->screen ? '<img src="'.UPLOAD_SITE_URI.$page->screen.'" style="width: 50px"/>' : '').__('Deleted Page：').$page->name,
            'type'=>'ui'];
        YZE_Hook::do_hook(YDE_CLOUD_PROJECT_ACTIVITY, $data);

        $return['deletedPageId'] = $page->id;

        $page->set('is_deleted', 1)->save();
        return YZE_JSON_View::success($this, $return);
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }
}
?>
