<?php

namespace app\project;

use app\common\File_Model;
use app\vendor\Uploader;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_RuntimeException;
use yangzie\YZE_Hook;
use yangzie\YZE_JSON_View;
use yangzie\yze_remove_path;

/**
 *
 * @version $Id$
 * @package common
 *
 */
class Upload_Controller extends YZE_Resource_Controller {

    public function index() {
        $request = $this->request;
        $this->layout = '';
    }

    /**
     * @actionname 上传文件
     * @return YZE_JSON_View
     * @throws YZE_RuntimeException
     */
    public function post_index() {
        $request = $this->request;
        $this->layout = "";
        set_time_limit(0);

        $pid = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        if (!$project) return YZE_JSON_View::error($this, __('Project not found'));
        if (!$project->get_member($loginUser->id)) return YZE_JSON_View::error($this, __('Project not found'));

        $upload_file_name = "file";
        if (!file_exists(YZE_UPLOAD_PATH.'project')) mkdir(YZE_UPLOAD_PATH.'project');
        if (!file_exists(YZE_UPLOAD_PATH.'project/'.$project->uuid)) mkdir(YZE_UPLOAD_PATH.'project/'.$project->uuid);

        $path = '';
        $action = $request->get_from_get("action");
        if($action=='icon') $path = YZE_UPLOAD_PATH.'project/'.$project->uuid.'/iconfont';
        if (!$path) return YZE_JSON_View::error($this, __('unknown action'));

        $upload = new Uploader($path);
        $res = $upload->upload($upload_file_name);

        if (!$res) return YZE_JSON_View::error($this,  __("Upload Failed"));
        $folder = strtr(YZE_UPLOAD_PATH, "\\", "/");
        $res = strtr($res, "\\", "/");

        if (preg_match("{" . $folder . "}", $res)) {
            $filepath = \yangzie\yze_remove_path($res, YZE_UPLOAD_PATH);
        }
        if (!$filepath) return YZE_JSON_View::error($this,  __("Upload Failed"));

        if ($action=='icon'){
            // 解压zip
            $zip = new \ZipArchive();
            if ($zip->open($res) !== TRUE) {
                return YZE_JSON_View::error($this,  __('can not rend zip file'));
            }
            $numFiles = $zip->numFiles;
            $extractFile = [];
            $folder = $zip->statIndex(0);
            $dirInZip = $folder['name'];
            for($i=0; $i<$numFiles; $i++){
                $entry = $zip->statIndex($i);
                if (preg_match("/iconfont\.css|iconfont\.js|iconfont\.ttf|iconfont\.woff|iconfont\.woff2/", $entry["name"])){
                    $extractFile[] = $entry["name"];
                }
            }
            $zip->extractTo($path, $extractFile);
            $zip->close();
            unlink($res);

            // 把文件往上移动到iconfont里面（iconfont zip解压后自带一个目录）并在iconfont中放置.htaccess文件（允许跨站访问）
            foreach (glob($path."/{$dirInZip}/*") as $file){
                rename($file, $path."/".basename($file));
            }
            file_put_contents($path."/.htaccess", 'Header add Access-Control-Allow-Origin: *
Header add Access-Control-Allow-Headers: "Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect"
Header add Access-Control-Allow-Methods: "GET, POST, PUT,DELETE,OPTIONS,PATCH"
');
            rmdir($path."/{$dirInZip}");
            return YZE_JSON_View::success($this, $extractFile);
        }

        return YZE_JSON_View::success($this);
    }

    public function exception(\Exception $e) {
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
