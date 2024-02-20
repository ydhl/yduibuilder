<?php
namespace app\api;
use app\project\Function_Model;
use app\project\Page_Model;
use app\project\Project_Model;
use app\vendor\Save_Model_Helper;
use yangzie\YZE_FatalException;
use yangzie\YZE_Model;
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
class Module_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }

    /**
     * @actionname 加载项目的功能模块
     */
    public function index(){
        $request = $this->request;
        $this->layout = '';
        $uuid = $request->get_from_get('uuid');
        $curr_page_uuid = $request->get_from_get('curr_page_uuid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $uuid);
        if (!$project) return YZE_JSON_View::error($this, __('Project Not Found'));
        $data = [];
        $popups = [];
        $components = [];
        foreach ($project->get_modules() as $module){
            $moduleData = ['name'=>$module->name, 'id'=>$module->uuid, 'functions'=>[]];
            foreach ($module->get_functions() as $function){
                $pages = [];
                foreach($function->get_pages() as $page) {
                    $pages[] = ['name'=>$page->name, 'id'=>$page->uuid, 'screen'=>$page->screen];
                }
                $moduleData['functions'][] = ['name'=>$function->name, 'id'=>$function->uuid, 'pages'=>$pages];
            }
            $data[] = $moduleData;
        }
        foreach (Page_Model::from()->where("project_id=:pid and page_type in ('popup','component') and is_deleted=0")
                     ->select([':pid'=>$project->id]) as $popup){
            if ($popup->page_type =='popup'){
                $popups[] = ['name'=>$popup->name, 'id'=>$popup->uuid, 'screen'=>$popup->screen];
            }else{
                $count = $popup->get_instance_count();
                $components[] = ['name'=>$popup->name, 'instance_count'=>$count, 'id'=>$popup->uuid, 'end_kind'=>$popup->component_end_kind];
            }
        }
        $currPage = $curr_page_uuid ? find_by_uuid(Page_Model::CLASS_NAME, $curr_page_uuid) : null;
        $currFunction = $currPage ? $currPage->get_function() : null;
        $currModule = $currFunction ? $currFunction->get_module() : null;
        return YZE_JSON_View::success($this, ['curr_module_uuid'=>$currModule->uuid,'curr_function_uuid'=>$currFunction->uuid,'modules'=>$data,'popups'=>$popups,'components'=>$components]);
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
