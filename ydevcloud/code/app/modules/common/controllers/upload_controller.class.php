<?php

namespace app\common;

use app\common\File_Model;
use app\vendor\Uploader;
use app\project\Project_Model;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_RuntimeException;
use yangzie\YZE_Hook;
use yangzie\YZE_JSON_View;
use yangzie\yze_remove_path;


class Upload_Controller extends YZE_Resource_Controller {
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
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);

        $upload_file_name = "file";
        if (!file_exists(YZE_UPLOAD_PATH.'user')) mkdir(YZE_UPLOAD_PATH.'user');
        if (!file_exists(YZE_UPLOAD_PATH.'user/'.$loginUser->uuid)) mkdir(YZE_UPLOAD_PATH.'user/'.$loginUser->uuid);

        $path = YZE_UPLOAD_PATH.'user/'.$loginUser->uuid;

        $upload = new Uploader($path);
        $res = $upload->upload($upload_file_name);

        if (!$res) return YZE_JSON_View::error($this,  __("Upload Failed"));
        $folder = strtr(YZE_UPLOAD_PATH, "\\", "/");
        $res = strtr($res, "\\", "/");

        if (preg_match("{" . $folder . "}", $res)) {
            $filepath = \yangzie\yze_remove_path($res, YZE_UPLOAD_PATH);
        }
        if (!$filepath) return YZE_JSON_View::error($this,  __("Upload Failed"));

        return YZE_JSON_View::success($this, ['url'=>UPLOAD_SITE_URI.$filepath, 'file'=>$filepath]);
    }

    public function exception(\Exception $e) {
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }

}

?>
