<?php
namespace app\project;
use yangzie\YZE_DBAImpl;
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
* @package uicomponent
*/
class Uicomponent_Controller extends YZE_Resource_Controller {
    private $project;
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'project');
        $pid = $request->get_var('pid');
        $this->project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $this->set_View_Data('project', $this->project);
        $this->layout = "admin";
    }

    public function index(){
        $request = $this->request;
        $kind = trim($request->get_from_get("kind"));
        $type = trim($request->get_from_get("type"));
        $query = trim($request->get_from_get("query"));
        $user_id = intval($request->get_from_get("user_id"));
        $page = intval($request->get_from_get("page"));
        if ($page < 1) $page = 1;

        $where = 'ui.project_id=:pid and ui.is_component=1 and ui.is_deleted=0';
        $args = [':pid'=>$this->project->id];

        if ($type){
            $where .= ' and ui.`page_type`=:type';
            $args[':type'] = $type;
        }
        if ($kind){
            $where .= ' and ui.`component_end_kind`=:kind';
            $args[':kind'] = $kind;
        }
        if ($user_id){
            $where .= ' and ui.`create_user_id`=:uid';
            $args[':uid'] = $user_id;
        }
        if ($query){
            $where .= ' and ui.`name` like :query';
            $args[':query'] = "%{$query}%";
        }
        $dba = YZE_DBAImpl::get_instance();
        $records = $dba->lookup_records('ui.page_type,u.nickname,u.id',
            'page as ui left join user as u on u.id=ui.create_user_id', $where, $args);
        $types = [];
        $members = [];
        foreach ($records as $record){
            $types[] = $record['page_type'];
            $members[$record['id']] = $record['nickname'];
        }

        $total = Page_Model::from('ui')->where($where)->count('id', $args,'ui');
        $uicomponents = Page_Model::from('ui')->where($where)->limit(($page - 1)*30, 30)->select($args,'ui');
        $this->set_view_data('uicomponents', $uicomponents);
        $this->set_view_data('total', $total);
        $this->set_view_data('currpage', $page);
        $this->set_view_data('types', array_unique($types));
        $this->set_view_data('members', $members);
        $this->set_View_Data('menu', 'uicomponent');
        $this->set_view_data('yze_page_title', __('Project UI Component'));
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
