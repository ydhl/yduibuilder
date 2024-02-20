<?php
namespace app\build;
use app\project\Function_Model;
use app\project\Module_Model;
use app\project\Page_Model;
use app\project\Project_Model;
use yangzie\YZE_FatalException;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use \yangzie\YZE_JSON_View;
use function yangzie\__;

/**
*
* @version $Id$
* @package build
*/
class Preview_Controller extends YZE_Resource_Controller {
    /**
     * @actionname 预览UI
     */
    public function index(){
        $request = $this->request;
        $this->layout = 'preview';
        $pid = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        if (!$project) throw new YZE_FatalException(__('project Not Found...'));
        $module = trim($request->get_from_get("module"));
        $page = trim($request->get_from_get("page"));

        $curr_page = $page ? find_by_uuid(Page_Model::CLASS_NAME, $page) : null;
        $module = $module ? find_by_uuid(Module_Model::CLASS_NAME, $module) : null;

        $this->set_View_Data('module', $module);
        $this->set_View_Data('project', $project);
        $this->set_View_Data('curr_page', $curr_page);
        $this->set_View_Data('yze_page_title', __("Preview"));
    }

    public function page() {
        $request = $this->request;
        $this->layout = '';
        $pageid = $request->get_var('pageid');
        $page = find_by_uuid(Page_Model::CLASS_NAME, $pageid);
        if (!$page) throw new YZE_FatalException(__('Page Not Found'));

        $this->set_View_Data('page', $page);
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
