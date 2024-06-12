<?php
namespace app\api;
use app\project\Action_Model;
use app\project\Page_Bind_Api_Model;
use app\project\Page_Bind_Data_Model;
use app\project\Page_Bind_Event_Model;
use app\project\Page_Bind_Io_Model;
use app\project\Page_Model;
use app\project\Project_Model;
use app\project\Web_Api_Model;
use app\vendor\Save_Model_Helper;
use yangzie\YZE_FatalException;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use \yangzie\YZE_JSON_View;
use function yangzie\__;

/**
* 地址管理
* @version $Id$
* @package api
*/
class Url_Controller extends YZE_Resource_Controller {

    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    // 拉取绑定的事件
    public function index(){
        $request = $this->request;
        $this->layout = '';
        $project_uuid = trim($request->get_from_get("project_uuid"));
        $project = find_by_uuid(Project_Model::CLASS_NAME, $project_uuid);
        if (!$project){
            throw new YZE_FatalException(__('Project not found'));
        }

        $datas = [];
        foreach (Page_Model::from()->where("is_deleted=0 and project_id=:pid and url!=''")->select([':pid']) as $page) {
            $datas[] = ['url'=>$page->url, 'name'=>$page->name];
        }

        return YZE_JSON_View::success($this, $datas);
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }
}
?>
