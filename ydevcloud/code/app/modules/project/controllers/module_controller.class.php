<?php
namespace app\project;
use app\vendor\Jwt;
use app\vendor\Save_Model_Helper;
use yangzie\YZE_DBAImpl;
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
class Module_Controller extends YZE_Resource_Controller {
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
     * @actionname 访问模块添加/修改界面
     */
    public function add(){
        $request = $this->request;
        $id = $request->get_var('pid');
        $mid = $request->get_var('mid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $id);
        $module = find_by_uuid(Module_Model::CLASS_NAME, $mid);
        if ($module){
            $project = $module->get_project();
        }
        $this->set_View_Data('project', $project);
        $this->set_View_Data('module', $module);
        $this->set_View_Data('menu', 'module');
        $this->set_view_data('yze_page_title', __('Add Module'));
    }

    /**
     * @actionname 保存模块信息
     */
    public function post_add(){
        $request = $this->request;
        $post_data = $request->the_post_datas();
        $id = $request->get_var('pid');
        $mid = $request->get_var('mid');
        if ($mid){//修改
            $module = find_by_uuid(Module_Model::CLASS_NAME, $post_data['uuid']);
        }else{
            $project = find_by_uuid(Project_Model::CLASS_NAME, $id);
        }
        if ($module){
            $project = $module->get_project();
        }
        if (!$project) throw new YZE_FatalException(__('you can not edit module'));
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $project_member = $project->get_member($loginUser->id);
        if (!$project_member->can_edit()) throw new YZE_FatalException(__('you can not edit module'));


        $this->layout = '';
        $save_helper = new Save_Model_Helper();
        $save_helper->alias_classes = Module_Model::CLASS_NAME;
        $save_helper->fetch_modify_model = function () use ($module) {
            return $module;
        };
        $save_helper->before_save = function ($model) use ($project) {
            if (!strlen(trim($model->name))) throw new YZE_FatalException(__('Module Name Is Empty'));
            if (!$model->uuid) $model->set(Module_Model::F_UUID, Module_Model::uuid());
            $model->set(Module_Model::F_PROJECT_ID, $project->id);
            // 验证folder name必须唯一
            if ($model->folder){
                $hasExist = YZE_DBAImpl::get_instance()->lookup('count(*)'
                , Module_Model::TABLE
                , 'project_id=:pid and is_deleted=0 and folder=:folder and id!=:id'
                , [':pid'=>$project->id, ':folder'=>$model->folder, ':id'=>intval($model->id)]);
                if ($hasExist){
                    throw new YZE_FatalException(sprintf(__('Folder %s has exist'), $model->folder).$model->id);
                }
            }
            return $model;
        };
        $save_helper->after_save = function ($model) use ($project_member) {
            $data = [
                'project_id'=>$project_member->project_id,
                'member_id'=>$project_member->id,
                'content'=>sprintf(__('Add Module %s'), $model->name),
                'type'=>'dev'];
            YZE_Hook::do_hook(YDE_CLOUD_PROJECT_ACTIVITY, $data);
        };
        $save_helper->save($post_data);
        return YZE_JSON_View::success($this);
    }
    /**
     * @actionname 删除模块
     */
    public function post_delete(){
        $request = $this->request;
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $fid = $request->get_var('mid');
        $value = $request->get_from_post('value');
        $module = find_by_uuid(Module_Model::CLASS_NAME, $fid);
        if (!$module) throw new YZE_FatalException(__('Module not exist'));
        if ($module->name != $value) throw new YZE_FatalException(__('Module name is not correct'));
        $project = $module->get_project();
        $project_member = $project->get_member($loginUser->id);
        if (!$project_member->can_edit())throw new YZE_FatalException(__('you can not delete module'));
        $module->remove();
        $data = [
            'project_id'=>$project_member->project_id,
            'member_id'=>$project_member->id,
            'content'=>sprintf(__('Delete Module %s'), $module->name),
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
