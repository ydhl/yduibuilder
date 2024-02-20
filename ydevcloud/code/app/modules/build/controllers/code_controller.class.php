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
class Code_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    /**
     * 展示预览代码页面
     * @actionname 预览代码
     */
    public function index(){
        $request = $this->request;
        $this->layout = 'preview';
        $pid = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        if (!$project) throw new YZE_FatalException(__('project Not Found...'));
        $page = trim($request->get_from_get("page"));
        $module = trim($request->get_from_get("module"));

        $curr_page = $page ? find_by_uuid(Page_Model::CLASS_NAME, $page) : null;
        $module = $module ? find_by_uuid(Module_Model::CLASS_NAME, $module) : null;
        $this->set_View_Data('module', $module);
        $this->set_View_Data('curr_page', $curr_page);
        $this->set_View_Data('project', $project);
        $this->set_View_Data('yze_page_title', __("Code"));
    }

    /**
     * 编译项目公用，或基础文件
     * @return void
     * @throws YZE_FatalException
     */
    public function common() {
        $request = $this->request;
        $this->layout = '';
        $projectid = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $projectid);
        if (!$project) throw new YZE_FatalException(__('Project Not Found'));

        $code_type = $request->get_from_get('code_type');
        $api_env = $request->get_from_get('api_env');

        $project_setting = $project->get_setting();
        $frontendFramework = $project_setting['frontend_framework'];
        $majorFrontendFrameworkVersion = $project_setting['frontend_framework_version'];
        $majorFrontendFrameworkVersion = substr($majorFrontendFrameworkVersion,0, strpos($majorFrontendFrameworkVersion, '.'));
        $view = 'code/'.$project_setting['frontend']."/{$frontendFramework}{$majorFrontendFrameworkVersion}-common";


        $this->set_View_Data('code_type', $code_type);
        $this->set_View_Data('api_env', $api_env);
        $this->set_View_Data('project', $project);
        if (file_exists($request->view_path().'/'.$view.'.tpl.php')){
            $this->view = $view;
        }else{
            throw new YZE_FatalException(sprintf(__('not found target %s'), $project_setting['frontend'].'/'.$frontendFramework.$majorFrontendFrameworkVersion ));
        }
    }

    /**
     * 编译自定页面的代码
     * @return void
     * @throws YZE_FatalException
     */
    public function page() {
        $request = $this->request;
        $this->layout = '';
        $pageid = $request->get_var('pageid');
        $page = find_by_uuid(Page_Model::CLASS_NAME, $pageid);
        if (!$page) throw new YZE_FatalException(__('Page Not Found'));

        $this->set_View_Data('page', $page);
        $code_type = $request->get_from_get('code_type');
        $api_env = $request->get_from_get('api_env');
        $mode = $request->get_from_get('mode');// preview 预览代码默认，compile 编译导出代码模式
        $project = $page->get_project();
        $project_setting = $project->get_setting();
        $frontendFramework = $project_setting['frontend_framework'];
        $majorFrontendFrameworkVersion = $project_setting['frontend_framework_version'];
        $majorFrontendFrameworkVersion = substr($majorFrontendFrameworkVersion,0, strpos($majorFrontendFrameworkVersion, '.'));
        $view = 'code/'.$project_setting['frontend']."/{$frontendFramework}{$majorFrontendFrameworkVersion}";

        $this->set_View_Data('code_type', $code_type);
        $this->set_View_Data('api_env', $api_env);
        $this->set_View_Data('mode', $mode);
        if (file_exists($request->view_path().'/'.$view.'.tpl.php')){
            $this->view = $view;
        }else{
            throw new YZE_FatalException(sprintf(__('not found target %s'), $project_setting['frontend'].'/'.$frontendFramework.$majorFrontendFrameworkVersion ));
        }
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
