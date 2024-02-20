<?php
namespace app\project;
use app\vendor\Env;
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
class Transfer_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'project');
        $this->layout = "admin";
    }

    /**
     * @actionname 访问转移项目界面
     */
    public function index(){
        $request = $this->request;
        $id = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $id);
        $this->set_View_Data('project', $project);
    }

    /**
     * @actionname 转移项目
     */
    public function post_index(){
        $request = $this->request;
        $id = $request->get_var('pid');
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $this->layout = '';
        $newCreater = find_by_uuid(Project_Member_Model::CLASS_NAME, $request->get_from_post('to'));
        $project = find_by_uuid(Project_Model::CLASS_NAME, $id);
        if (!$project) throw new YZE_FatalException(__('project not found'));
        if (!$newCreater)throw new YZE_FatalException(__('member not found, please invite first'));

        $oldCreater = Project_Member_Model::from()->where('is_creater=1 and project_id=:pid')->get_Single([':pid'=>$project->id]);
        if ($oldCreater){
            $oldCreater->set(Project_Member_Model::F_IS_CREATER, 0)
                ->save();
        }

        $newCreater->set(Project_Member_Model::F_IS_CREATER, 1)
            ->set(Project_Member_Model::F_ROLE, Project_Member_Model::ROLE_ADMIN)
            ->save();

        $data = [
            'project_id'=>$project->id,
            'member_id'=>$oldCreater->id ?: $newCreater->id,
            'content'=> sprintf(__('Transfer Project from %s to %s'), $oldCreater ? $oldCreater->get_user()->name() : '', $newCreater->get_user()->name()),
            'type'=>'member'];
        YZE_Hook::do_hook(YDE_CLOUD_PROJECT_ACTIVITY, $data);
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
