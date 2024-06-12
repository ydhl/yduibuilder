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

    public function font() {
        $request = $this->request;
        $this->layout = "";
        set_time_limit(0);
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $uuid = trim($request->get_from_get("uuid"));
        if ($uuid) $fileMode = find_by_uuid(File_Model::CLASS_NAME, $uuid);
        if (!$fileMode) throw new YZE_FatalException(__('Font not found'));
        $file = $fileMode->url;
        $object = preg_replace("{^".OSS_BUCKET_HOST."/}", "", trim($file));
        // 阿里云主账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录 https://ram.console.aliyun.com 创建RAM账号。
        $accessKeyId = OSS_RAM_ACCESSKEYID;
        $accessKeySecret = OSS_RAM_ACCESSKEYSECRET;
        $endpoint = OSS_ENDPOINT;
        $bucket= OSS_BUCKET;

        if (!yze_isimage($file)) {
            header("Content-Disposition: attachment; filename=" . urlencode(basename($file)));
        }
        ob_clean();
        try{
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);

            $objectMeta = $ossClient->getObjectMeta($bucket, $object);
            $content = $ossClient->getObject($bucket, $object);

            header("Content-type: " . $objectMeta['content-type']);
            header("Accept-Ranges: bytes");
            header("Accept-Length: " . $objectMeta['content-length']);
            echo $content;

        } catch(OssException $e) {
            return;
        }
        ob_end_flush();
    }
    public function index() {
        $request = $this->request;
        $this->layout = "";
        set_time_limit(0);
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $file = trim($request->get_from_get("file"));
        $uuid = trim($request->get_from_get("uuid"));
        if ($uuid) $fileMode = find_by_uuid(File_Model::CLASS_NAME, $uuid);
        if ($fileMode) $file = $fileMode->url;
        $object = preg_replace("{^".OSS_BUCKET_HOST."/}", "", trim($file));
        // 阿里云主账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录 https://ram.console.aliyun.com 创建RAM账号。
        $accessKeyId = OSS_RAM_ACCESSKEYID;
        $accessKeySecret = OSS_RAM_ACCESSKEYSECRET;
        $endpoint = OSS_ENDPOINT;
        $bucket= OSS_BUCKET;

        if (!yze_isimage($file)) {
            header("Content-Disposition: attachment; filename=" . urlencode(basename($file)));
        }
        ob_clean();
        try{
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);

            $objectMeta = $ossClient->getObjectMeta($bucket, $object);
            $content = $ossClient->getObject($bucket, $object);

            header("Content-type: " . $objectMeta['content-type']);
            header("Accept-Ranges: bytes");
            header("Accept-Length: " . $objectMeta['content-length']);
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
        $file = trim($request->get_from_get("file"));
        $pageid = trim($request->get_from_get("pageid"));
        $page = $pageid ? find_by_uuid(Page_Model::CLASS_NAME, $pageid) : null;
        $file = $page ? $page->screen : $file;
        $object = preg_replace("{^".OSS_BUCKET_HOST."/}", "", trim($file));
        // 阿里云主账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录 https://ram.console.aliyun.com 创建RAM账号。
        $accessKeyId = OSS_RAM_ACCESSKEYID;
        $accessKeySecret = OSS_RAM_ACCESSKEYSECRET;
        $endpoint = OSS_ENDPOINT;
        $bucket= OSS_BUCKET;

        ob_clean();
        try{
            if ($accessKeyId) {
                $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);

                $objectMeta = $ossClient->getObjectMeta($bucket, $object);

                $content = $ossClient->getObject($bucket, $object);

                header("Content-type: " . $objectMeta['content-type']);
                header("Accept-Ranges: bytes");
                header("Accept-Length: " . $objectMeta['content-length']);
                header("Cache-Control: max-age=86400");
                header('Last-Modified: ' . $objectMeta['last-modified']);
                header('Expires:' . gmdate('D, d M Y H:i:s', strtotime($objectMeta['last-modified']) + 86400) . ' GMT');
                echo $content;
            }else{
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $mimeMap = [
                    'png'=> "image/png",
                    "svg"=> "image/svg+xml",
                    "gif"=> "image/gif",
                    "bmp"=> "image/bmp",
                    "ico"=>"image/x-icon",
                    "jpeg"=>'image/jpeg',
                    "jpg"=>'image/jpeg'
                ];
                header("Content-type: " . $mimeMap[$ext]?:'application/octet-stream');
                header("Accept-Ranges: bytes");
                header("Accept-Length: " . filesize(YZE_UPLOAD_PATH.$file));
                header("Cache-Control: max-age=86400");
                echo file_get_contents(YZE_UPLOAD_PATH.$file);
            }
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
