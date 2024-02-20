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
class Page_Controller extends YZE_Resource_Controller {
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
     * @actionname 访问项目结构
     */
    public function index(){
        $request = $this->request;
        $pid = $request->get_var('pid');
        $funcid_uuid = $request->get_from_get('uuid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $function = find_by_uuid(Function_Model::CLASS_NAME, $funcid_uuid);
        if (!$function) throw new YZE_FatalException(__('Function Not Found'));

        $module = $function->get_module();
        $user = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);

        $payload=array(
            'iss'=> 'ydevcloud',
            'iat'=> time(),
            'exp'=> time() + 7200,
            'nbf'=> time(),
            'sub'=> $user->uuid,
            'jti'=> md5(uniqid('JWT').time()));
        $token = Jwt::getToken($payload);
        $params = [':pid'=>$project->id];
        $where = 'm.project_id=:pid and p.is_deleted=0 and m.is_deleted=0 and p.page_type="page" and f.is_deleted=0';
        $where .= ' and p.module_id=:mid';
        $params[':mid'] = $module->id;
        $where .= ' and p.function_id=:fid';
        $params[':fid'] = $function->id;

        $query = Page_Model::from('p')
            ->left_join(Module_Model::CLASS_NAME, 'm', 'm.id = p.module_id')
            ->left_join(Function_Model::CLASS_NAME, 'f', 'f.id = p.function_id')
            ->where($where)->order_By('modified_on','desc');

        $breadcrumbs = ['/project/'.$project->uuid.'/structure' => __('UI')];
        $breadcrumbs['/project/'.$project->uuid.'/func?uuid='.$module->uuid] = $module->name;
        $breadcrumbs['/project/'.$project->uuid.'/page?uuid='.$function->uuid] = $function->name;

        $pages = $query->select($params, 'p');

        $this->set_View_Data('project', $project);
        $this->set_View_Data('menu', 'structure');
        $this->set_View_Data('curr_module', $module);
        $this->set_View_Data('curr_function', $function);
        $this->set_View_Data('pages', $pages);
        $this->set_View_Data('breadcrumbs', $breadcrumbs);
        $this->set_View_Data('token', $token);
        $this->set_view_data('yze_page_title', __('UI'));
    }
    public function choosepage() {
        return $this->index();
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
