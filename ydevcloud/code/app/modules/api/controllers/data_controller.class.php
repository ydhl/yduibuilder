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
*
* @version $Id$
* @package api
*/
class Data_Controller extends YZE_Resource_Controller {
    private $page;
    private $loginUser;
    private $project;
    private $project_member;
    public function valid($page_uuid)
    {
        $this->loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $this->page = find_by_uuid(Page_Model::CLASS_NAME, $page_uuid);
        if (!$this->page) throw new YZE_FatalException(__('Page not found'));
        $this->project = $this->page->get_project();
        $this->project_member = $this->project->get_member($this->loginUser->id);
        if (!$this->project_member) throw new YZE_FatalException(__('Project not found'));
    }

    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    // 拉取变量
    public function index(){
        $request = $this->request;
        $this->layout = '';

        $this->valid($request->get_from_get("page_uuid"));
        $items = Page_Bind_Data_Model::from('e')->where('e.page_id=:pid')
            ->order_By('name','asc', 'e')
            ->order_By('id','asc','e')->select([':pid'=>$this->page->id]);
        $datas = [];
        $error = ['type'=>'object','props'=>[], 'name'=>'error', 'title'=>__('error message when valid fail')];
        foreach ($items as $item){
            $datas[] = $item->get_data_model();
            $error['props'][] = ['type'=>'string','name'=>$item->name];
        }

        return YZE_JSON_View::success($this, ['page'=>$datas, 'global'=>[], 'error'=>$error]);
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }
}
?>
