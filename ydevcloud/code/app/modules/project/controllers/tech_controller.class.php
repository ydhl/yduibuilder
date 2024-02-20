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
class Tech_Controller extends YZE_Resource_Controller {
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
     * @actionname 访问添加项目技术更新界面
     */
    public function index(){
        $request = $this->request;
        $id = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $id);
        $this->set_View_Data('project', $project);
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $project_member = $project->get_member($loginUser->id);
        if ($project_member->role != Project_Member_Model::ROLE_ADMIN){
            throw new YZE_FatalException(__('just project admin can change project tech setting'));
        }
        $this->set_view_data('yze_page_title', $project ? sprintf(__('Edit %s'), $project->name) : __('Add Project'));
    }

    /**
     * @actionname 保存项目技术设置
     */
    public function post_index(){
        $request = $this->request;
        $id = $request->get_var('pid');
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $this->layout = '';
        $post_data = $request->the_post_datas();
        $setting = [];
        $project = find_by_uuid(Project_Model::CLASS_NAME, $id);
        if (!$project) throw new YZE_FatalException(__('project not found'));
        $member = $project->get_member($loginUser->id);

        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $project_member = $project->get_member($loginUser->id);
        if ($project_member->role != Project_Member_Model::ROLE_ADMIN){
            throw new YZE_FatalException(__('just project admin can change project tech setting'));
        }
        $setting['name'] = ['ui', 'ui_version', 'frontend', 'backend','frontend_framework',
            'frontend_framework_version', 'framework', 'framework_version', 'frontend_language',
            'frontend_language_version', 'backend_language', 'backend_language_version'];
        foreach ($setting['name'] as $name){
            $setting['value'][] = trim($post_data['s-'.$name]);
        }
        if (!$post_data['s-ui']){
            throw new YZE_FatalException(__('please select UI'));
        }

        $oldTech = $project->get_technology();
        Project_Setting_Model::from()
            ->where("project_id=:pid and name in ('".join("','", $setting['name'])."')")
            ->delete([':pid'=>$project->id]);
        $setting_saver = new Save_Model_Helper();
        $setting_saver->alias_classes = Project_Setting_Model::CLASS_NAME;
        $setting_saver->multi_model_identity_column = 'name';
        $setting_saver->before_save = function ($model) use($project){
            $model->set('uuid', Project_Setting_Model::uuid())
                ->set('project_id', $project->id);
            return $model;
        };
        $setting_saver->save($setting);
        $newTech = $project->get_technology();
        $data = [
            'project_id'=>$member->project_id,
            'member_id'=>$member->id,
            'content'=> sprintf(__('Change Tech. from %s to %s'), $oldTech, $newTech),
            'type'=>'dev'];
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
