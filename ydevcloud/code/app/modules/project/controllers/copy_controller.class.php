<?php
namespace app\project;
use app\common\File_Model;
use app\vendor\Env;
use app\vendor\Save_Model_Helper;
use TencentCloud\Msp\V20180319\Models\Project;
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
class Copy_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'project');
        $this->layout = "admin";
    }

    /**
     * @actionname 访问复制项目界面
     */
    public function index(){
        $request = $this->request;
        $id = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $id);
        $this->set_View_Data('project', $project);
    }

    /**
     * @actionname 复制项目
     */
    public function post_index(){
        $request = $this->request;
        $id = $request->get_var('pid');
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $this->layout = '';
        $project = find_by_uuid(Project_Model::CLASS_NAME, $id);
        if (!$project) throw new YZE_FatalException(__('project not found'));
        $project_member = $project->get_member($loginUser->id);
        if (!$project_member)throw new YZE_FatalException(__('Just project member can copy'));
        $pname = trim($request->get_from_post("p-name"));
        if (!$pname)throw new YZE_FatalException(__('Please input new project name'));

        set_time_limit(0);
        $oldId2new = [];
        $newProject = $this->copy_project_info($project);
        $this->copy_api_info($project, $newProject, $oldId2new);
        $this->copy_module_info($project, $newProject, $oldId2new);
        $this->copy_page_info($project, $newProject, $oldId2new);

        return YZE_JSON_View::success($this, ['uuid'=>$newProject->uuid]);
    }
    private function copy_project_info($oldProject){
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $dba = YZE_DBAImpl::get_instance();
        $request = $this->request;
        $post_data = $request->the_post_datas();
        $pname = trim($request->get_from_post("p-name"));

        // copy project
        $records = $oldProject->get_records();
        unset($records['id']);
        $records['name'] = $pname;
        $records['uuid'] = Project_Model::uuid();
        $records['desc'] = sprintf(__('Copy from %s at %s'), $oldProject->name, date('Y-m-d H:i:s'));
        $newProject = Project_Model::from_Array($records);
        $newProject->save();

        // setting, file
        foreach ([
                     'file'=>array_keys(File_Model::$columns),
                 ] as $table=>$columns){
            $settingColumn = array_diff($columns, ['id', 'uuid', 'project_id']);
            $newSettingColumn = array_map(function ($item) { return 'tmp.'.$item; }, $settingColumn);

            $sql = "insert into {$table} (".join(',', $settingColumn).", uuid, project_id) 
            select ".join(',', $newSettingColumn).", uuid(), {$newProject->id} 
            from {$table} as tmp where tmp.project_id={$oldProject->id}";

            $dba->exec($sql);
        }

        // tech
        $setting['name'] = ['ui', 'ui_version', 'frontend', 'backend','frontend_framework',
            'frontend_framework_version', 'framework', 'framework_version', 'frontend_language',
            'frontend_language_version', 'backend_language', 'backend_language_version'];
        foreach ($setting['name'] as $name){
            $setting['value'][] = trim($post_data['s-'.$name]);
        }
        if (!$post_data['s-ui']){
            throw new YZE_FatalException(__('please select UI'));
        }

        $setting_saver = new Save_Model_Helper();
        $setting_saver->alias_classes = Project_Setting_Model::CLASS_NAME;
        $setting_saver->multi_model_identity_column = 'name';
        $setting_saver->before_save = function ($model) use($newProject){
            $model->set('uuid', Project_Setting_Model::uuid())
                ->set('project_id', $newProject->id);
            return $model;
        };
        $setting_saver->save($setting);

        // member
        $newMember = new Project_Member_Model();
        $newMember->set('uuid', Project_Member_Model::uuid())
            ->set('user_id', $loginUser->id)
            ->set('project_id', $newProject->id)
            ->set('role', Project_Member_Model::ROLE_ADMIN)
            ->set('is_creater', 1)
            ->save();
        $data = [
            'project_id'=>$newProject->id,
            'member_id'=>$newMember->id,
            'content'=> $newProject->desc,
            'type'=>'dev'];
        YZE_Hook::do_hook(YDE_CLOUD_PROJECT_ACTIVITY, $data);
        return $newProject;
    }
    private function copy_module_info($oldProject, $newProject, &$oldId2new){
        $models = Module_Model::from()
            ->where('project_id=:pid and is_deleted=0')->select([':pid'=>$oldProject->id]);
        foreach ($models as $model){
            $data = $model->get_records();
            unset($data['id'], $data['uuid']);
            $data['uuid'] = Module_Model::uuid();
            $data['project_id'] = $newProject->id;
            $newModel = new Module_Model();
            $newModel->save_from_data($data);
            $oldId2new[Module_Model::CLASS_NAME][$model->id] = $newModel->id;
        }


        $models = Function_Model::from('f')
            ->left_join(Module_Model::CLASS_NAME,'m','m.id = f.module_id')
            ->where('m.project_id=:pid and m.is_deleted=0 and f.is_deleted=0')->select([':pid'=>$oldProject->id],'f');
        foreach ($models as $model){
            if (!$oldId2new[Module_Model::CLASS_NAME][$model->module_id]) continue;
            $data = $model->get_records();
            unset($data['id'], $data['uuid']);
            $data['uuid'] = Function_Model::uuid();
            $data['module_id'] = $oldId2new[Module_Model::CLASS_NAME][$model->module_id];
            $newModel = new Function_Model();
            $newModel->save_from_data($data);
            $oldId2new[Function_Model::CLASS_NAME][$model->id] = $newModel->id;
        }
    }
    private function copy_page_info($oldProject, $newProject, &$oldId2new){
        $models = Style_Model::from()->where('project_id=:pid and is_deleted=0')->select([':pid'=>$oldProject->id]);
        foreach ($models as $model){
            $data = $model->get_records();
            unset($data['id'], $data['uuid']);
            $data['uuid'] = Style_Model::uuid();
            $data['project_id'] = $newProject->id;
            $newModel = new Style_Model();
            $newModel->save_from_data($data);
            $oldId2new[Style_Model::CLASS_NAME][$model->id] = $newModel->id;
        }

        $models = Page_Model::from()->where('project_id=:pid and is_deleted=0')
            ->order_By('ref_page_id', 'ASC')
            ->select([':pid'=>$oldProject->id]);
        foreach ($models as $model){
            $data = $model->get_records();
            $pageConfig = json_decode(html_entity_decode($model->config));
            unset($data['id']);
            $newID = uuid(5,null, 'Page'.$newProject->id);
            $oldUI2new = [];
            $oldUI2new[$model->uuid] = $newID;

            $pageConfig->meta->id = $newID;

            $data['uuid'] = $newID;
            $data['project_id'] = $newProject->id;
            $data['module_id'] = $oldId2new[Module_Model::CLASS_NAME][$model->module_id]?:NULL;
            $data['function_id'] = $oldId2new[Function_Model::CLASS_NAME][$model->function_id]?:NULL;
            $data['last_version_id'] = -1;
            $data['config'] = json_encode($pageConfig);
            $data['ref_page_id'] = $oldId2new[Page_Model::CLASS_NAME][$model->ref_page_id] ?: 0;
            $newModel = new Page_Model();
            $newModel->save_from_data($data);
            $newModel->copy_bind_from($model, $oldUI2new, false);
            $oldId2new[Page_Model::CLASS_NAME][$model->id] = $newModel->id;
        }
    }
    private function copy_api_info($oldProject, $newProject, &$oldId2new){
        $models = Api_Folder_Model::from()->where('project_id=:pid and is_deleted=0')->select([':pid'=>$oldProject->id]);
        $newModels = [];
        foreach ($models as $model){
            $data = $model->get_records();
            unset($data['id'], $data['uuid']);
            $data['uuid'] = Api_Folder_Model::uuid();
            $data['project_id'] = $newProject->id;
            $newModel = new Api_Folder_Model();
            $newModel->save_from_data($data);
            $newModels[] = $newModel;
            $oldId2new[Api_Folder_Model::CLASS_NAME][$model->id] = $newModel->id;
        }
        foreach ($newModels as $newModel){
            $newModel->set('api_folder_id', $oldId2new[Api_Folder_Model::CLASS_NAME][$newModel->api_folder_id] ?: null)->save();
        }

        $models = Label_Model::from()->where('project_id=:pid and is_deleted=0')->select([':pid'=>$oldProject->id]);
        foreach ($models as $model){
            $data = $model->get_records();
            unset($data['id'], $data['uuid']);
            $data['uuid'] = Label_Model::uuid();
            $data['project_id'] = $newProject->id;
            $newModel = new Label_Model();
            $newModel->save_from_data($data);
            $oldId2new[Label_Model::CLASS_NAME][$model->id] = $newModel->id;
        }
        $models = Web_Api_Model::from()
            ->where('project_id=:pid and is_deleted=0')->select([':pid'=>$oldProject->id]);
        foreach ($models as $model){
            $data = $model->get_records();
            unset($data['id'], $data['uuid']);
            $data['uuid'] = Web_Api_Model::uuid();
            $data['api_folder_id'] = $oldId2new[Api_Folder_Model::CLASS_NAME][$model->api_folder_id] ?: NULL;
            $data['project_member_id'] = NULL;
            $data['project_id'] = $newProject->id;
            $newModel = new Web_Api_Model();
            $newModel->save_from_data($data);
            $oldId2new[Web_Api_Model::CLASS_NAME][$model->id] = $newModel->id;
        }

        $models = Label_Target_Model::from('t')
            ->left_join(Label_Model::CLASS_NAME,'l','l.id = t.label_id')
            ->where('l.project_id=:pid and l.is_deleted=0 and t.is_deleted=0')->select([':pid'=>$oldProject->id],'t');
        foreach ($models as $model){
            if (!$oldId2new[Label_Model::CLASS_NAME][$model->label_id] || !$oldId2new[$model->target_class][$model->target_id]) continue;
            $data = $model->get_records();
            unset($data['id'], $data['uuid']);
            $data['uuid'] = Label_Target_Model::uuid();
            $data['label_id'] = $oldId2new[Label_Model::CLASS_NAME][$model->label_id];
            $data['target_id'] = $oldId2new[$model->target_class][$model->target_id];
            $newModel = new Label_Target_Model();
            $newModel->save_from_data($data);
        }
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = 'error';
        //Post 请求或者返回json接口时，出错返回json错误结果
        $format = $request->get_output_format();
        if (!$request->is_get() || strcasecmp ( $format, "json" )==0){
        	$this->layout = '';
        	return YZE_JSON_View::error($this, /*$e->getTraceAsString().*/$e->getMessage());
        }
    }
}
?>
