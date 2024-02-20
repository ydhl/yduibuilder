<?php

namespace app\api;

use app\common\File_Model;
use app\vendor\Uploader;
use app\project\Project_Model;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_RuntimeException;
use yangzie\YZE_Hook;
use yangzie\YZE_JSON_View;
use yangzie\yze_remove_path;
use function yangzie\__;
use function yangzie\yze_isimage;


class Upload_Controller extends YZE_Resource_Controller {
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
    }
    /**
     * @actionname 上传图片
     */
    public function post_index() {
        $request = $this->request;
        $this->layout = "";
        set_time_limit(0);

        $pid = $request->get_var('pid');
        $type = trim($request->get_from_get("type"));
        $name = trim(urldecode($request->get_from_get("name")));

        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        if (!$project) return YZE_JSON_View::error($this, __('Project not found'));
        if (!$project->get_member($loginUser->id)) return YZE_JSON_View::error($this, __('Project not found'));

        $upload_file_name = "file";
        if (!file_exists(YZE_UPLOAD_PATH.'project')) mkdir(YZE_UPLOAD_PATH.'project');
        if (!file_exists(YZE_UPLOAD_PATH.'project/'.$project->uuid)) mkdir(YZE_UPLOAD_PATH.'project/'.$project->uuid);

        $path = YZE_UPLOAD_PATH.'project/'.$project->uuid;

        $upload = new Uploader($path);
        $res = $upload->upload($upload_file_name);

        if (!$res) return YZE_JSON_View::error($this,  __("Upload Failed"));
        $folder = strtr(YZE_UPLOAD_PATH, "\\", "/");
        $res = strtr($res, "\\", "/");

        if (preg_match("{" . $folder . "}", $res)) {
            $filepath = \yangzie\yze_remove_path($res, YZE_UPLOAD_PATH);
        }
        if (!$filepath) return YZE_JSON_View::error($this,  __("Upload Failed"));

        $ext = strtoupper(pathinfo($_FILES[$upload_file_name]['name'], PATHINFO_EXTENSION));
        $file = new File_Model();
        $file->set(File_Model::F_CREATED_ON, date('Y-m-d H:i:s'))
            ->set(File_Model::F_FILE_NAME, $name ?: $_FILES[$upload_file_name]['name'])
            ->set(File_Model::F_FILE_SIZE, $_FILES[$upload_file_name]['size'])
            ->set(File_Model::F_UUID, File_Model::uuid())
            ->set(File_Model::F_URL, $filepath)
            ->set(File_Model::F_PROJECT_ID, $project->id)
            ->set(File_Model::F_UPLOAD_DATE, date('Y-m-d H:i:s'))
            ->set(File_Model::F_TYPE, file_type($ext))
            ->save();

        return YZE_JSON_View::success($this, ['url'=>UPLOAD_SITE_URI.$filepath, 'ext'=>strtolower($ext), 'id'=>$file->uuid, 'name'=>$file->file_name]);
    }

    public function exception(\Exception $e) {
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }

}

?>
