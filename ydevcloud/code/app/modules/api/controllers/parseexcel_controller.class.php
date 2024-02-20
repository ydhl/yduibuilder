<?php
namespace app\api;
use app\common\File_Model;
use app\project\Project_Model;
use app\user\User_Model;
use app\vendor\Jwt;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
* @package api
*/
class Parseexcel_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    /**
     * @actionname 解析EXCEL
     * @return YZE_JSON_View
     */
    public function index () {
        $request = $this->request;
        $this->layout = '';
        $pid = $request->get_from_get('pid');
        $fid = $request->get_from_get('fid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        if (!$project) return YZE_JSON_View::error($this, __('project not found'));
        if (!$project->get_member($loginUser->id)) return YZE_JSON_View::error($this, __('project not found'));
        $file = find_by_uuid(File_Model::CLASS_NAME, $fid);
        if (!$file) return YZE_JSON_View::error($this, __('file not found'));
        $data = [
            'header'=>[],
            'footer'=>[],
            'row'=>[]
        ];
        $tmp = tempnam('/tmp', 'excel');
        file_put_contents($tmp, file_get_contents(YZE_UPLOAD_PATH.$file->url));
        $spreadsheet = IOFactory::load($tmp); //载入excel表格

        $worksheet = $spreadsheet->getActiveSheet();
//        $rowData = array_map(function ($row) {
//            return array_filter($row);
//        },$worksheet->toArray());
        $rowData = $worksheet->toArray();

//        print_r($rowData);
        $data['row'] = array_map(function ($row) {
            return array_map(function ($column) {
                return ['text'=>$column];
            }, $row);
        }, $rowData);
        // 第一行默认为header，最后一行默认为footer
        $data['header'] = array_shift($data['row']);
        $data['footer'] = array_pop($data['row']);
        return YZE_JSON_View::success($this, $data);
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
