<?php

namespace app\common;

use app\common\File_Model;
use app\project\Page_Model;
use app\vendor\Uploader;
use app\project\Project_Model;
use OSS\OssClient;
use yangzie\YZE_FatalException;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_RuntimeException;
use yangzie\YZE_Hook;
use yangzie\YZE_JSON_View;
use yangzie\yze_remove_path;
use function yangzie\__;
use function yangzie\yze_isimage;


class Download_Controller extends YZE_Resource_Controller {
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
        $this->layout = "";
        set_time_limit(0);
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $uuid = trim($request->get_from_get("uuid"));
        if ($uuid) $fileMode = find_by_uuid(File_Model::CLASS_NAME, $uuid);

        try{
            if (!$fileMode) throw new YZE_FatalException('');

            ob_clean();
            if (!yze_isimage($fileMode->url)) {
                header("Content-Disposition: attachment; filename=" . urlencode(basename($fileMode->url)));
            }
            $content = file_get_contents(YZE_UPLOAD_PATH.$fileMode->url);
            header("Content-type: " . get_download_mime_type(pathinfo($fileMode->url, PATHINFO_EXTENSION)));
            header("Accept-Ranges: bytes");
            header("Accept-Length: " . $fileMode->file_size);
            echo $content;

        } catch(OssException $e) {
            return;
        }
        ob_end_flush();
    }
    public function image() {
        $request = $this->request;
        $this->layout = "";
        set_time_limit(0);
        $pageid = trim($request->get_from_get("pageid"));
        $page = $pageid ? find_by_uuid(Page_Model::CLASS_NAME, $pageid) : null;

        try{
            if (!$page) throw new YZE_FatalException('page not found');
            $file = $page->screen;

            ob_clean();
            $content = file_get_contents($file);

            header("Content-type: " . get_download_mime_type(pathinfo($file, PATHINFO_EXTENSION)));
            header("Accept-Ranges: bytes");
            header("Accept-Length: " . filesize(YZE_UPLOAD_PATH.$file));
            header("Cache-Control: max-age=86400");
            echo $content;

        } catch(\Exception $e) {

            header("Content-type: image/png");
            header("Accept-Ranges: bytes");
            header("Accept-Length: ".filesize(YZE_PUBLIC_HTML.'logo.png'));
            header("Cache-Control: max-age=86400");
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()).' GMT');
            header('Expires:' . gmdate('D, d M Y H:i:s', time()+86400).' GMT');

            echo file_get_contents(YZE_PUBLIC_HTML.'logo.png');
        }
        ob_end_flush();
        die();
    }
    public function exception(\Exception $e) {
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }

}

?>
