<?php
namespace app\project;
use app\vendor\Save_Model_Helper;
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
* @package project
*/
class Setting_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'project');
        $this->layout = "admin";
    }

    /**
     * @actionname 访问项目设置界面
     */
    public function index(){
        $request = $this->request;
        $pid = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $this->set_View_Data('project', $project);
        $this->set_View_Data('menu', 'setting');
        $this->set_view_data('yze_page_title', __('Setting'));
    }

    public function post_apiurl(){
        $request = $this->request;
        $pid = $request->get_var('pid');
        $names = $request->get_from_post("name");
        $urls = $request->get_from_post("url");

        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $this->layout = '';
        if (!$project) throw new YZE_FatalException(__('project not found'));
        $setting = [];
        foreach ($names as $index => $name){
            $name = trim($name);
            if (!$name) continue;
            $setting[$name] = trim($urls[$index]);
        }
        Project_Setting_Model::set_setting_value($project->id,'api_env', json_encode($setting, JSON_UNESCAPED_UNICODE));
        return YZE_JSON_View::success($this);
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
