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
class Build_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'project');
        $this->layout = "admin";
    }

    /**
     * @actionname 访问项目Build功能
     */
    public function index(){
        $request = $this->request;
        $pid = $request->get_var('pid');
        $user = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $payload=array(
            'iss'=> 'ydevcloud',
            'iat'=> time(),
            'nbf'=> time(),
            'sub'=> $user->uuid,
            'jti'=> md5(uniqid('JWT').time()));
        $token = Jwt::getToken($payload);
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $this->set_View_Data('project', $project);
        $this->set_View_Data('menu', 'build');
        $this->set_View_Data('token', $token);
        $this->set_view_data('yze_page_title', __('Build'));
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
