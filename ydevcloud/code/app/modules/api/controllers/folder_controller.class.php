<?php

namespace app\api;

use app\common\File_Model;
use app\project\Api_Folder_Model;
use app\project\Page_Model;
use app\project\Web_Api_Model;
use app\vendor\Save_Model_Helper;
use app\vendor\Uploader;
use app\project\Project_Model;
use yangzie\YZE_DBAImpl;
use yangzie\YZE_FatalException;
use yangzie\YZE_Notpl_View;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_RuntimeException;
use yangzie\YZE_Hook;
use yangzie\YZE_JSON_View;
use yangzie\yze_remove_path;
use function yangzie\__;


class Folder_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    /**
     * 加载完整的api结构
     *
     * @return YZE_JSON_View
     * @throws YZE_FatalException
     */
    public function all() {
        $request = $this->request;
        $this->layout = '';
        $uuid = trim($request->get_from_get("projectId"));
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $project = find_by_uuid(Project_Model::CLASS_NAME, $uuid);
        if (!$project) throw new YZE_FatalException(__('Project not found'));
        $project_member = $project->get_member($loginUser->id);
        if (!$project_member) throw new YZE_FatalException(__('Project not found'));
        $apis = Web_Api_Model::from()->where('project_id=:pid and is_deleted=0')
            ->order_By('index','asc')
            ->order_By('id','asc')
            ->select([':pid'=>$project->id]);
//        print_r($apis);
        $data = Api_Folder_Model::get_all_folders($project, $folderPaths);
        foreach ($apis as $api){
            $apiData = [ 'title'=> $api->name, 'major'=> $api->major, 'path'=> $api->path, 'minor'=> $api->minor, 'revision'=> $api->revision, 'id'=> $api->uuid, 'isApi'=>true, 'method'=>$api->method, 'status'=>$api->status];
            if (!$api->api_folder_id) {
                $data['api'.$api->id] = $apiData;
                continue;
            }

            // 找到api所在的目录节点并放在里面
            $found = [];
            $foundIndex = -1;
            foreach ($folderPaths as $path){
                $foundIndex = array_search($api->api_folder_id, $path);
                if ($foundIndex!==false){
                    $found = $path;
                    break;
                }
            }
            // 从顶级往下的路径id
            $found = array_reverse(array_splice($found, $foundIndex));
            $this->set_api($data, $found, 0, $api->id, $apiData);
        }
        header("Content-Type: application/json; charset=utf-8");
        return YZE_JSON_View::success($this, array_values(Api_Folder_Model::filter_tree_data($data)));
    }
    private function set_api(&$data, $paths, $index, $apiid, $apiData) {
        $id = $paths[$index];
        if ($index < count($paths)-1){
            $this->set_api($data[$id]['children'], $paths, $index+1, $apiid, $apiData);
        }else{
            $data[$id]['children']['api'.$apiid] = $apiData;
        }
    }
    /**
     * 加载目录结构
     *
     * @return YZE_JSON_View
     * @throws YZE_FatalException
     */
    public function index() {
        $request = $this->request;
        $this->layout = '';
        $uuid = trim($request->get_from_get("projectId"));
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $project = find_by_uuid(Project_Model::CLASS_NAME, $uuid);
        if (!$project) throw new YZE_FatalException(__('Project not found'));
        $project_member = $project->get_member($loginUser->id);
        if (!$project_member) throw new YZE_FatalException(__('Project not found'));

        header("Content-Type: application/json; charset=utf-8");
        return YZE_JSON_View::success($this, Api_Folder_Model::get_all_folders($project, $folderPaths, true));
    }


    public function post_add(){
        $request = $this->request;
        $this->layout = '';
        $postData = $request->the_post_datas();

        $project = find_by_uuid(Project_Model::CLASS_NAME, $postData['project_uuid']);
        if (!$project) throw new YZE_FatalException(__('Project not found').$postData['project_uuid']);
        $postData['project_id'] = $project->id;

        $parent = $postData['parent'] ? find_by_uuid(Api_Folder_Model::CLASS_NAME, $postData['parent']) : null;
        if ($parent) $postData['api_folder_id'] = $parent->id;

        $helper = new Save_Model_Helper();
        $helper->alias_classes = Api_Folder_Model::CLASS_NAME;
        $helper->fetch_modify_model = function (&$postData) {
            if ($postData['uuid']) {
                $model = find_by_uuid(Api_Folder_Model::CLASS_NAME, $postData['uuid']);
            }
            if (!$model) {
                unset($postData['uuid']);
                $model = new Api_Folder_Model();
                $model->set('uuid', Api_Folder_Model::uuid());
            }
            return $model;
        };

        $helper->valid_column_and_rules = [
            Api_Folder_Model::F_NAME=> function ($model, $value) {
                if (!$value) throw new YZE_FatalException(__('Api folder can not be empty'));
            }
        ];
        $model = $helper->save($postData);
        $data = $model->get_records();
        unset($data['id']);
        $data['id'] = $data['uuid'];
        return YZE_JSON_View::success($this, $data);
    }
    public function post_delete(){
        $request = $this->request;
        $this->layout = '';
        $postData = $request->the_post_datas();

        $folder = find_by_uuid(Api_Folder_Model::CLASS_NAME, $postData['uuid']);
        if (!$folder) throw new YZE_FatalException(__('Folder not found'));
        $folder->remove();
        return YZE_JSON_View::success($this, []);
    }
    public function exception(\Exception $e) {
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }

}

?>
