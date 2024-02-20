<?php
namespace app\project;
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
* @package project
*/
class Quit_Controller extends YZE_Resource_Controller {
    public function post_index(){
        $request = $this->request;
        $this->layout = '';
        $uuid = trim($request->get_from_get("uuid"));
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $project = find_by_uuid(Project_Model::CLASS_NAME, $uuid);
        if (!$project){
            return YZE_JSON_View::error($this, __('Project not found'));
        }
        $member = $project->get_member($loginUser->id);
        if (!$member){
            return YZE_JSON_View::error($this, __('you are not a project member'));
        }

        $member->set('is_deleted', 1)->save();
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
