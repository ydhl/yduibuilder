<?php
namespace app\api;
use app\project\Page_Model;
use app\user\User_Model;
use app\vendor\Jwt;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use \yangzie\YZE_JSON_View;

/**
*
* @version $Id$
* @package api
*/
class Screenshot_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    function outputImage($file_path)
    {
        $filesize = filesize($file_path);
        ob_clean();

        header("Content-type: image/jpeg");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . $filesize);

        $read_buffer = 4096;
        $handle = fopen($file_path, 'rb');
        $sum_buffer = 0;

        while (!feof($handle) && $sum_buffer < $filesize) {
            echo fread($handle, $read_buffer);
            $sum_buffer += $read_buffer;
        }
        ob_flush();
        flush();
    }

    function downloadFile($url) {
        $cURL = curl_init($url);
        $file_path = YZE_UPLOAD_PATH.'tmp';
        $fh = fopen($file_path, "w+");
        curl_setopt_array($cURL, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FILE           => $fh,
            CURLOPT_USERAGENT      => $_SERVER["HTTP_USER_AGENT"]
        ]);

        curl_exec($cURL);
        curl_close($cURL);
        $this->outputImage($file_path);
        unlink($file_path);
        die();
    }
    /**
     * 输出页面的缩略图
     */
    public function index() {
        $request = $this->request;
        $this->layout = '';
        $pageid = $request->get_from_get('pageid');
        $page = find_by_uuid(Page_Model::CLASS_NAME, $pageid);
        set_time_limit(0);

        if (!$page){
            $file_path = YZE_PUBLIC_HTML."uibuilder.jpg";
            $this->outputImage($file_path);
            die();
        }
        $file_path = $page->screen;
//        echo file_get_contents("https://ydecloud.oss-cn-chengdu.aliyuncs.com/screen/c8c19b7c-e458-11eb-9801-00163e03b43f/page-2E32976DE8-287.jpg");
        $this->downloadFile($file_path);
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
