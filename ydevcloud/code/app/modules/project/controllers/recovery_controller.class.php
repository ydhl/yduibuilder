<?php
namespace app\project;
use app\vendor\Jwt;
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
class Recovery_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'project');
        $this->layout = "admin";
    }
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    /**
     * @actionname 访问移动页面
     */
    public function index(){
        $request = $this->request;
        $pid = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $user = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $curr_page = find_by_uuid(Page_Model::CLASS_NAME, $request->get_from_get('page'));
        $this->set_View_Data('curr_page', $curr_page);
        $this->set_View_Data('project', $project);
        $this->set_View_Data('menu', 'structure');
    }
    /**
     * @actionname 移动页面
     */
    public function post_index(){
        $request = $this->request;
        $pid = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        if (!$project) throw new YZE_FatalException('Project not found');
        $user = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $member = $project->get_member($user->id);
        if (!$member || !$member->can_edit()) throw new YZE_FatalException('you can not edit project');
        $page = find_by_uuid(Page_Model::CLASS_NAME, $request->get_from_post('page'), true);
        $function = find_by_uuid(Function_Model::CLASS_NAME, $request->get_from_post('to'));
        if (!$page) throw new YZE_FatalException('Page not found');
        if (!$function) throw new YZE_FatalException('Function not found');
        $page->set(Page_Model::F_FUNCTION_ID, $function->id)
            ->set(Page_Model::F_MODULE_ID, $function->module_id)
            ->set(Page_Model::F_IS_DELETED, 0)
            ->save();
        $this->layout = '';
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
