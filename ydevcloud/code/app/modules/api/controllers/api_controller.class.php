<?php

namespace app\api;

use app\project\Api_Folder_Model;
use app\project\Label_Model;
use app\project\Label_Target_Model;
use app\project\Project_Member_Model;
use app\project\Web_Api_Model;
use app\vendor\Save_Model_Helper;
use app\project\Project_Model;
use Overtrue\Pinyin\Pinyin;
use yangzie\YZE_DBAImpl;
use yangzie\YZE_FatalException;
use \yangzie\YZE_Resource_Controller;
use yangzie\YZE_Hook;
use yangzie\YZE_JSON_View;
use yangzie\yze_remove_path;
use function yangzie\__;


class Api_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    public function index() {
        $request = $this->request;
        $this->layout = '';
        $uuid = trim($request->get_from_get("projectId"));
        $apiid = trim($request->get_from_get("apiid"));
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $project = find_by_uuid(Project_Model::CLASS_NAME, $uuid);
        $member = $project->get_member($loginUser->id);
        if (!$member){
            return YZE_JSON_View::error($this, __('project not found'));
        }

        return YZE_JSON_View::success($this, []);
    }

    /**
     * 删除接口
     * @return YZE_JSON_View
     * @throws YZE_FatalException
     */
    public function post_delete(){
        $request = $this->request;
        $this->layout = '';
        $postData = $request->the_post_datas();

        $api = find_by_uuid(Web_Api_Model::CLASS_NAME, $postData['uuid']);
        if (!$api) throw new YZE_FatalException(__('Api not found'));
        $api->remove();
        return YZE_JSON_View::success($this, []);
    }
    private function assert_not_empty($column) {
        return function ($model, $value) use ($column) {
            if (!$value) throw new YZE_FatalException(sprintf(__("%s can not be empty"), $column));
        };
    }
    public function post_save(){
        $request = $this->request;
        $json = json_decode(trim(file_get_contents("php://input")), true);
        $this->layout = '';
        $apiData = $json['api'];
        $apiData['commit_msg'] = $json['commit'];
        $apiData['content'] = json_encode([
            'request' => $apiData['request'],
            'response' => $apiData['response']
        ], JSON_UNESCAPED_UNICODE);

        $project = find_by_uuid(Project_Model::CLASS_NAME, $json['project_uuid']);
        if (!$project) throw new YZE_FatalException(__('Project not found'));
        $folder = $apiData['folder']['uuid'] ? find_by_uuid(Api_Folder_Model::CLASS_NAME, $apiData['folder']['uuid']) : null;
        if ($folder) $apiData['api_folder_id'] = $folder->id;
        $apiData['project_id'] = $project->id;
        $project_member = $apiData['responsible']['uuid'] ? find_by_uuid(Project_Member_Model::CLASS_NAME, $apiData['responsible']['uuid']) : null;
        if ($project_member) $apiData['project_member_id'] = $project_member->id;
        unset($apiData['version']);

        // 保存web api
        $saveHelper = new Save_Model_Helper();
        $saveHelper->alias_classes = Web_Api_Model::CLASS_NAME;
        $saveHelper->fetch_modify_model = function() use($apiData){
            if ($apiData['uuid']){// 修改
                $model = find_by_uuid(Web_Api_Model::CLASS_NAME, $apiData['uuid']);
            }
            if (!$model) {// 新增或复制
                $model = new Web_Api_Model();
                $model->set('uuid', Web_Api_Model::uuid());
            }
            if ($model->major > $apiData['major'] || $model->minor > $apiData['minor'] || $model->revision > $apiData['revision']) {
                throw new YZE_FatalException(__('Version can only be incremented'));
            }
            if ($model->major != $apiData['major'] || $model->minor != $apiData['minor'] || $model->revision != $apiData['revision']) {
                $model->set('version', $model->version + 1);
            }
            return $model;
        };
        $saveHelper->valid_column_and_rules = [
          Web_Api_Model::F_METHOD => $this->assert_not_empty(__('web api method')),
          Web_Api_Model::F_PATH => $this->assert_not_empty(__('web api path')),
          Web_Api_Model::F_NAME => $this->assert_not_empty(__('web api name'))
        ];
        if (!$apiData['uuid']){
            unset($apiData['uuid']);
        }
        $webApi = $saveHelper->save($apiData);

        // 保存label
        $pinyin = new Pinyin();
        foreach ((array)$apiData['label'] as $uuid => $name) {
            if (!trim($name)) continue;
            $label = find_by_uuid(Label_Model::CLASS_NAME, $uuid);
            if (!$label) {
                $label = Label_Model::from()->where('name=:name and is_deleted=0')->get_Single([':name'=>$name]);
            }
            if (!$label) {
                $label = new Label_Model();
                $label->set('uuid', Label_Model::uuid());
                $label->set('name', trim($name));
                $label->set('pinyin', $pinyin->permalink($name, ''));
                $label->set('pinyin_sort', $pinyin->abbr($name));
                $label->set('project_id', $webApi->project_id);
                $label->save();
            }
            $model = new Label_Target_Model();
            $model->set('uuid', Label_Target_Model::uuid());
            $model->set('label_id', $label->id);
            $model->set('target_class', Web_Api_Model::CLASS_NAME);
            $model->set('target_id', $webApi->id);
            $model->save();
        }
        return YZE_JSON_View::success($this, $webApi->uuid);
    }
    private function basic_info($project){
        $members = [];
        foreach ($project->get_members() as $m){
            $user = $m->get_user();
            $members[] = ['uuid'=>$m->uuid, 'name'=>$user->nickname];
        }
        return [
            'folder'=>Api_Folder_Model::get_all_folders($project,$folderPaths, true),
            'member'=>$members];
    }
    /**
     * 拉取新增api时的基础数据
     * @return YZE_JSON_View
     * @throws YZE_FatalException
     */
    public function basic(){
        $request = $this->request;
        $this->layout = '';
        $getData = $request->the_get_datas();

        $project = find_by_uuid(Project_Model::CLASS_NAME, $getData['uuid']);
        if (!$project) throw new YZE_FatalException(__('Project not found'));

        return YZE_JSON_View::success($this, $this->basic_info($project));
    }
    /**
     * 拉取API基本信息
     * @return YZE_JSON_View
     * @throws YZE_FatalException
     */
    public function info(){
        $request = $this->request;
        $this->layout = '';
        $getData = $request->the_get_datas();
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);

        $webApi = find_by_uuid(Web_Api_Model::CLASS_NAME, $getData['uuid']);
        if (!$webApi) throw new YZE_FatalException(__('Web Api not found'));
        $project = $webApi->get_project();

        $project_member = $project->get_member($loginUser->id);
        if (!$project_member) throw new YZE_FatalException(__('not a project member'));

        $data = $this->basic_info($project);
        $api = $webApi->get_records();
        $content = json_decode(html_entity_decode($api['content']), true);
        unset($api['id'], $api['content'], $api['api_folder_id'], $api['project_member_id']);
        $api += $content;
        $api_folder = $webApi->get_api_folder();
        if ($api_folder){
            $api['folder'] = ['uuid'=>$api_folder->uuid, 'name'=>$api_folder->name];
        }else{
            $api['folder'] = new \stdClass();
        }
        $project_member = $webApi->get_project_member();
        if ($project_member){
            $api['responsible'] = ['uuid'=>$project_member->uuid, 'name'=>$project_member->get_user()->nickname];
        }else{
            $api['responsible'] = new \stdClass();
        }
        foreach (Label_Model::get_labels($webApi) as $label){
            $api['label'][$label->uuid] = $label->name;
        }
        $data['api'] = $api;
        return YZE_JSON_View::success($this, $data);
    }
    /**
     * 拉取API日志信息
     * @return YZE_JSON_View
     * @throws YZE_FatalException
     */
    public function logs(){
        $request = $this->request;
        $this->layout = '';
        $getData = $request->the_get_datas();
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);

        $webApi = find_by_uuid(Web_Api_Model::CLASS_NAME, $getData['uuid']);
        if (!$webApi) throw new YZE_FatalException(__('Web Api not found'));
        $project = $webApi->get_project();

        $project_member = $project->get_member($loginUser->id);
        if (!$project_member) throw new YZE_FatalException(__('not a project member'));

        $data = [];

        return YZE_JSON_View::success($this, $data);
    }
    public function exception(\Exception $e) {
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }

}

?>
