<?php
namespace app\uicomponent;
use app\project\Page_Model;
use yangzie\YZE_DBAImpl;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_JSON_View;
use function yangzie\__;

/**
*
* @version $Id$
* @package uicomponent
*/
class Index_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'uicomponent');
        $this->layout = "admin";
    }

    public function index(){
        $request = $this->request;
        $kind = trim($request->get_from_get("kind"));
        $type = trim($request->get_from_get("type"));
        $query = trim($request->get_from_get("query"));
        $page = intval($request->get_from_get("page"));
        if ($page < 1) $page = 1;

        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $where = 'create_user_id=:uid and is_component=1 and create_user_is_deleted=0 and is_deleted=0';
        $args = [':uid'=>$loginUser->id];

        if ($type){
            $where .= ' and `page_type`=:type';
            $args[':type'] = $type;
        }
        if ($kind){
            $where .= ' and `component_end_kind`=:kind';
            $args[':kind'] = $kind;
        }
        if ($query){
            $where .= ' and `name` like :query';
            $args[':query'] = "%{$query}%";
        }
        $dba = YZE_DBAImpl::get_instance();
        $records = $dba->lookup_records('page_type', 'page', $where, $args);
        $types = [];
        foreach ($records as $record){
            $types[] = $record['type'];
        }

        $total = Page_Model::from()->where($where)->count('id', $args);
        $uicomponents = Page_Model::from()->where($where)->limit(($page - 1)*30, 30)->select($args);
        $this->set_view_data('uicomponents', $uicomponents);
        $this->set_view_data('total', $total);
        $this->set_view_data('currpage', $page);
        $this->set_view_data('types', array_unique($types));
        $this->set_view_data('yze_page_title', __('My UI Component'));
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
