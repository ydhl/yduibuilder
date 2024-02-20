<?php
namespace app\project;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use \yangzie\YZE_JSON_View;

/**
*
* @version $Id$
* @package project
*/
class Project_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'project');
        $this->layout = "admin";
    }
    public function index(){
        $request = $this->request;
        $id = $request->get_var('pid');
        $page = intval($request->get_from_get('page'));
        $type = $request->get_from_get('type');
        if ($page < 1) $page = 1;

        $project = find_by_uuid(Project_Model::CLASS_NAME, $id);
        $where = 'project_id=:pid';
        $params = [':pid'=>$project->id];
        if ($type){
            $where .= ' and type=:type';
            $params[':type'] = $type;
        }

        $activities = Activity_Model::from()
            ->where($where)
            ->order_By('created_on', 'desc')
            ->limit(($page - 1 ) * 20, 20)->select($params);
        $this->set_View_Data('project', $project);
        $this->set_View_Data('activities', $activities);
        $this->set_View_Data('page', $page);
        $this->set_view_data('yze_page_title', $project->name);
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
