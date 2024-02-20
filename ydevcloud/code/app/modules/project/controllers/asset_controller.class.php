<?php
namespace app\project;
use app\common\File_Model;
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
* @package project
*/
class Asset_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'project');
        $this->layout = "admin";
    }
    public function index(){
        $request = $this->request;
        $pid = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $this->set_View_Data('project', $project);
        $page = intval($request->get_from_get("page"));
        $type = strtolower(trim($request->get_from_get("type", 'image')));

        $page = $page < 1 ? 1 : $page;

        $where = "project_id=:pid";
        if ($type != 'image'){
            $where .= " and `type`!='image'";
        }else{
            $where .= " and `type`='image'";
        }
        $query = File_Model::from()->where($where)->order_By('id', 'DESC');

        $total = $query->count('id', [':pid'=>$project->id]);
        $files = $query->limit(($page - 1) * 20, 20)->select([':pid'=>$project->id]);

        $this->set_View_Data('files', $files);
        $this->set_View_Data('menu', 'asset');
        $this->set_View_Data('total', $total);
        $this->set_View_Data('currpage', $page);
        $this->set_View_Data('type', $type);
    }

    public function post_remove(){
        $request = $this->request;
        $this->layout = '';
        $pid = $request->get_var('pid');
        $uuid = trim($request->get_from_get("uuid"));
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);

        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $file = find_by_uuid(File_Model::CLASS_NAME, $uuid);
        if(!$project || $file->project_id != $project->id){
            return YZE_JSON_View::error($this, __('File not found'));
        }
        $member = $project->get_member($loginUser->id);
        if (!$member){
            return YZE_JSON_View::error($this, __('Project Not Found'));
        }
        $file->remove();
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
