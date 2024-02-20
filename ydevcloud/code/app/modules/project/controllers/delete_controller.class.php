<?php
namespace app\project;
use app\vendor\Save_Model_Helper;
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
class Delete_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'project');
        $this->layout = "admin";
    }

    /**
     * @actionname 删除项目
     */
    public function post_index(){
        $request = $this->request;
        $this->layout = '';
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $id = $request->get_var('pid');
        $value = $request->get_from_post('value');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $id);
        if (!$project) return YZE_JSON_View::error($this, __('Project not found'));
        $member = $project->get_member($loginUser->id);
        if ($member->role != Project_Member_Model::ROLE_ADMIN){
            return YZE_JSON_View::error($this, __('Just Admin Can Delete Project'));
        }
        if ($project->name != $value){
            return YZE_JSON_View::error($this, __('Please input project name'));
        }
        $project->set(Project_Model::F_IS_DELETED, 1)->save();

        // 保存setting
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
