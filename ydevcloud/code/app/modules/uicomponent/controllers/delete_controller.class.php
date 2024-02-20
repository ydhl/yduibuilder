<?php
namespace app\uicomponent;
use app\project\Page_Model;
use app\project\Project_Member_Model;
use app\project\Project_Model;
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
*
* @version $Id$
* @package uicomponent
*/
class Delete_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'uicomponent');
        $this->layout = "admin";
    }

    public function post_index(){
        $request = $this->request;
        $uuid = trim($request->get_from_post("uuid"));
        $project_uuid = trim($request->get_from_post("pid"));
        $uicomponent = find_by_uuid(Page_Model::CLASS_NAME, $uuid);
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        if(!$uicomponent){
            return YZE_JSON_View::error($this, __('UI Component not found'));
        }

        $project = $uicomponent->get_project();
        if ($project){
            $member = $project->get_member($loginUser->id);
            if ($member->role != Project_Member_Model::ROLE_ADMIN && $uicomponent->create_user_id != $loginUser->id){
                return YZE_JSON_View::error($this, __('Just admin or author can delete project UI component'));
            }
        }else{
            if ($uicomponent->create_user_id != $loginUser->id) throw new YZE_FatalException(__('UI Component not found'));

        }
        $uicomponent->set('create_user_is_deleted', 1)->save();
        return YZE_JSON_View::success($this);
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = 'error';
        //Post 请求或者返回json接口时，出错返回json错误结果
        $format = $request->get_output_format();
        if (!$request->is_get() || strcasecmp ( $format, "json" )==0){
        	$this->layout = '';
        	return YZE_JSON_View::error($this, $e->getMessage());
        }
    }
}
?>
