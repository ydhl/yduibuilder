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
class Function_Controller extends YZE_Resource_Controller {
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
     * @actionname 访问功能添加/修改界面
     */
    public function add(){
        $request = $this->request;
        $mid = $request->get_var('mid');
        $module = find_by_uuid(Module_Model::CLASS_NAME, $mid);
        $fid = $request->get_var('fid');
        $function = find_by_uuid(Function_Model::CLASS_NAME, $fid);
        if ($function) {
            $module = $function->get_module();
        }
        $project = $module->get_project();
        $this->set_View_Data('project', $project);
        $this->set_View_Data('module', $module);
        $this->set_View_Data('function', $function);
        $this->set_View_Data('menu', 'module');
        $this->set_view_data('yze_page_title', __('Add Function'));
    }

    /**
     * @actionname 保存功能信息
     */
    public function post_add(){
        $request = $this->request;
        $post_data = $request->the_post_datas();
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $mid = $request->get_var('mid');
        $module = find_by_uuid(Module_Model::CLASS_NAME, $mid);
        $function = find_by_uuid(Function_Model::CLASS_NAME, $post_data['uuid']);
        $new_module = find_by_uuid(Module_Model::CLASS_NAME, $post_data['module']);
        $module = $new_module?:$module;
        $project = $module->get_project();
        $project_member = $project->get_member($loginUser->id);
        if (!$project_member->can_edit()) throw new YZE_FatalException(__('you can not edit function'));

        $this->layout = '';
        $save_helper = new Save_Model_Helper();
        $save_helper->alias_classes = Function_Model::CLASS_NAME;
        $save_helper->fetch_modify_model = function () use ($function) {
            return $function;
        };
        $save_helper->before_save = function ($model) use ($module) {
            if (!strlen(trim($model->name))) throw new YZE_FatalException(__('Function Name Is Empty'));
            if (!$model->uuid) $model->set(Function_Model::F_UUID, Module_Model::uuid());
            $model->set(Function_Model::F_MODULE_ID, $module->id);
            return $model;
        };
        $save_helper->after_save = function ($model) use ($project_member) {
            $data = [
                'project_id'=>$project_member->project_id,
                'member_id'=>$project_member->id,
                'content'=>sprintf(__('Add Function %s'), $model->name),
                'type'=>'dev'];
            YZE_Hook::do_hook(YDE_CLOUD_PROJECT_ACTIVITY, $data);
        };
        $save_helper->save($post_data);
        return YZE_JSON_View::success($this);
    }

    /**
     * @actionname 删除功能
     */
    public function post_delete(){
        $request = $this->request;
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $fid = $request->get_var('fid');
        $value = $request->get_from_post('value');
        $function = find_by_uuid(Function_Model::CLASS_NAME, $fid);
        if (!$function) throw new YZE_FatalException(__('Function not exist'));
        if ($function->name != $value) throw new YZE_FatalException(__('Function name is not correct'));
        $module = $function->get_module();
        $project = $module->get_project();
        $project_member = $project->get_member($loginUser->id);
        if (!$project_member->can_edit())throw new YZE_FatalException(__('you can not delete function'));
        $function->remove();
        $data = [
            'project_id'=>$project_member->project_id,
            'member_id'=>$project_member->id,
            'content'=>sprintf(__('Delete Function %s'), $function->name),
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
