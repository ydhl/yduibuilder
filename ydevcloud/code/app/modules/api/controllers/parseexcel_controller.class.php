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
        $data = [];
        $config = [];
        $mergedColumn = [];
        try{
            $tmp = tempnam('/tmp', 'excel');
            @file_put_contents($tmp, file_get_contents(getOssLink($file->url)));
//            @file_put_contents($tmp, file_get_contents(UPLOAD_SITE_URI.$file->url));
            $spreadsheet = IOFactory::load($tmp); //载入excel表格

            $worksheet = $spreadsheet->getActiveSheet();
            $rowData = $worksheet->toArray();
            $mergeCells = $worksheet->getMergeCells();
            foreach ($mergeCells as $mergeCell){
                list ($mergeFrom, $mergeTo) = explode(":", $mergeCell);
                $mergeFromRow = preg_replace("/\D+/", "", $mergeFrom);
                $mergeToRow = preg_replace("/\D+/", "", $mergeTo);
                $mergeFromColumn = $this->get_column_index(preg_replace("/\d+/", "", $mergeFrom));
                $mergeToColumn = $this->get_column_index(preg_replace("/\d+/", "", $mergeTo));
                $tmp = [];
                if ($mergeFromRow !== $mergeToRow) {
                    $tmp['rowspan'] = $mergeToRow - $mergeFromRow + 1;
                }
                if ($mergeFromColumn !== $mergeToColumn) {
                    $tmp['colspan'] = $mergeToColumn - $mergeFromColumn + 1;
                }
                $config[$mergeFromRow-1][$mergeFromColumn] = $tmp;

                if ($mergeFromRow === $mergeToRow){// 跨列
                    for($col = $mergeFromColumn + 1; $col <= $mergeToColumn; $col++) {
                        $mergedColumn[$mergeFromRow-1][$col] = true;
                    }
                }else if ($mergeFromColumn === $mergeToColumn){// 跨行
                    for($row = $mergeFromRow; $row <= $mergeToRow-1; $row++) { //行的所以是从1开始
                        $mergedColumn[$row][$mergeFromColumn] = true;
                    }
                }else{//跨行跨列
//                    echo ($mergeFromRow-1).' / '.$mergeFromColumn." : ".($mergeToRow-1). '/' .$mergeToColumn;
                    for($row = $mergeFromRow-1; $row <= $mergeToRow-1; $row++) {
                        for($col = $mergeFromColumn; $col <= $mergeToColumn; $col++) {
                            if ($row == $mergeFromRow - 1 && $col == $mergeFromColumn) continue;// 第一个单元格忽略
                            $mergedColumn[$row][$col] = true;
                        }
                    }
                }
            }

    //        print_r($rowData);
//            print_r($mergedColumn);
            array_walk($rowData, function (&$row, $rowIndex) use($mergedColumn, &$config) {
                array_walk($row, function (&$column, $colIndex) use($mergedColumn, $rowIndex, &$config) {
                    $config[$rowIndex][$colIndex]['isMerged'] = (bool)@$mergedColumn[$rowIndex][$colIndex];
                    $column = ['name'=>$column];
                });
            });
        }catch (\Exception $e){
            $rowData[] = [['name'=>$e->getMessage()]];
        }
        return YZE_JSON_View::success($this, ['data'=>$rowData,'config'=>$config ?: new \stdClass()]);
    }
    private function get_column_index($str){
        $str = trim(strtoupper($str));
        return (strlen($str)-1) * 26 + ord(substr($str, -1)) - 65;
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
