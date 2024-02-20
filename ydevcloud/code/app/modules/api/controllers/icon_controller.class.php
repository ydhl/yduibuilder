<?php
namespace app\api;
use app\project\Project_Model;
use app\user\User_Model;
use app\vendor\Jwt;
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
class Icon_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    /**
     * @actionname 查看icon集合
     * @return YZE_JSON_View
     */
    public function index () {
        $request = $this->request;
        $this->layout = 'preview';
        $pid = $request->get_from_get('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        if (!$project) return YZE_JSON_View::error($this, __('project not found'));
//        if (!$project->get_member($loginUser->id)) return YZE_JSON_View::error($this, __('project not found。'));
        $this->set_View_Data('project', $project);
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
