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
class Add_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'project');
        $this->layout = "admin";
    }

    /**
     * @actionname 访问添加项目页面
     */
    public function index(){
        $request = $this->request;
        $id = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $id);
        $this->set_View_Data('project', $project);
        $this->set_view_data('yze_page_title', $project ? sprintf(__('Edit %s'), $project->name) : __('Add Project'));
    }

    /**
     * @actionname 保存新项目
     */
    public function post_index(){
        $request = $this->request;
        $pid = $request->get_from_post('p-uuid');
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $this->layout = '';
        $post_data = $request->the_post_datas();
        $setting = [
            'name'=>['logo'],
            'value'=>[$post_data['s-logo']]
        ];

        // 修改的时候不能修改除项目基本信息以外的其他信息
        if (!$pid){
            $post_data['pm-role'] = Project_Member_Model::ROLE_ADMIN;
            $post_data['pm-user_id'] = $loginUser->id;
            $post_data['pm-is_creater'] = 1;

            $setting['name'] = ['logo', 'ui', 'ui_version', 'frontend', 'backend','frontend_framework',
                'frontend_framework_version', 'framework', 'framework_version', 'frontend_language',
                'frontend_language_version', 'backend_language', 'backend_language_version'];
            foreach ($setting['name'] as $index => $name){
                $setting['value'][$index] = $post_data['s-'.$name];
            }
        }

        $new_project_id = 0;

        $save_helper = new Save_Model_Helper();
        $save_helper->alias_classes = ['p'=>Project_Model::CLASS_NAME, 'pm'=>Project_Member_Model::CLASS_NAME];
        $save_helper->save_order = ['p', 'pm', 's'];
        $save_helper->fetch_modify_model = [
            'p' => function () use($pid) {
                return find_by_uuid(Project_Model::CLASS_NAME, $pid);
            },
            'pm' => function() use($pid) {
                if (!$pid) return null;
                $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
                return Project_Member_Model::from('pm')
                    ->left_join(Project_Model::CLASS_NAME, 'p', 'p.id = pm.project_id')
                    ->where('pm.user_id=:uid and p.uuid=:pid')
                    ->get_Single([':uid'=>$loginUser->id, ':pid'=>$pid], 'pm');
            }
        ];
        $save_helper->before_save = function ($model) use (&$new_project_id) {
            // 新增的情况
            if (!$model->uuid) {
                $model->set('uuid', Project_Model::uuid());

                if (is_a($model, Project_Member_Model::CLASS_NAME )){
                    $model->set(Project_Member_Model::F_PROJECT_ID, $new_project_id);
                }

                // 新增时检查项目数量限制
                if (is_a($model, Project_Model::CLASS_NAME )) {
                    if (!trim($model->name)){
                        throw new YZE_FatalException(__('please input project name'));
                    }

                }
                return $model;
            }

            // 如果是修改的情况，则不处理member, 并且判断是否有权限修改
            if (is_a($model, Project_Member_Model::CLASS_NAME )){
                if (!$model->can_edit()) throw new YZE_FatalException(__('you can not edit project'));
                return null;
            }

            return $model;
        };
        $save_helper->after_save = function ($model) use (&$new_project_id) {
            if (is_a($model, Project_Model::CLASS_NAME )){
                $new_project_id= $model->id;
            }else if (is_a($model, Project_Member_Model::CLASS_NAME)){
                $data = [
                    'project_id'=>$model->project_id,
                    'member_id'=>$model->id,
                    'content'=>sprintf(__('Create Project %s'), $model->get_project()->name),
                    'type'=>'dev'];
                YZE_Hook::do_hook(YDE_CLOUD_PROJECT_ACTIVITY, $data);
            }
        };
        $save_helper->valid_column_and_rules = [
            Project_Model::F_NAME => function($model, $value){
                if (!strlen(trim($value))) throw new YZE_FatalException(__('Project Name Is Empty'));
            }
        ];

        $save_helper->save($post_data);
        if ($setting){
            $setting_saver = new Save_Model_Helper();
            $setting_saver->alias_classes = Project_Setting_Model::CLASS_NAME;
            $setting_saver->multi_model_identity_column = 'name';
            $setting_saver->before_save = function ($model) use(&$new_project_id){
                $model->set('uuid', Project_Setting_Model::uuid())
                    ->set('project_id', $new_project_id);
                return $model;
            };
            $setting_saver->save($setting);
        }

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
