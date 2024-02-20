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
class Recycle_Controller extends YZE_Resource_Controller {
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
     * @actionname 访问回收站
     */
    public function index(){
        $request = $this->request;
        $pid = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);

        $params = [':pid'=>$project->id];
        $where = 'p.project_id=:pid and p.is_deleted=1';

        $query = Page_Model::from('p')
            ->where($where)->order_By('modified_on','desc','p');
        $breadcrumbs = ['/project/'.$project->uuid.'/structure'=>__('UI')];
        $breadcrumbs['/project/'.$project->uuid.'/recycle'] = __('Recycle');

        $pages = $query->select($params, 'p');
        $this->set_View_Data('project', $project);
        $this->set_View_Data('menu', 'structure');
        $this->set_View_Data('breadcrumbs', $breadcrumbs);
        $this->set_View_Data('pages', $pages);
        $this->set_view_data('yze_page_title', __('Recycle'));
    }
    /**
     * @actionname 删除页面
     */
    public function post_index(){
        $request = $this->request;
        $pid = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        if (!$project) throw new YZE_FatalException('Project not found');
        $user = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $member = $project->get_member($user->id);
        if (!$member || !$member->can_edit()) throw new YZE_FatalException('you can not edit project');
        $page = find_by_uuid(Page_Model::CLASS_NAME, $request->get_from_get('page'), true);
        if ($page){
            $page->remove();
        }

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
