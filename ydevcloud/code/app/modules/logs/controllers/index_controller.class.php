<?php
namespace app\logs;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_Session_Context;
use \yangzie\YZE_RuntimeException;
use \yangzie\YZE_JSON_View;

/**
*
* @version $Id$
* @package logs
*/
class Index_Controller extends YZE_Resource_Controller {
    private function buildSQL() {
        $request = $this->request;
        $time_from = $request->get_from_request('time_from');
        $time_to = $request->get_from_request('time_to');
        $search = $request->get_from_request('search');
        $method = $request->get_from_request('method');
        $table = $request->get_from_request('table');
        $column = $request->get_from_request('column');

        $where = "l.is_deleted=0";
        $params = [];

        $query = Log_Model::from('l')
        ->left_join(Log_Column_Model::CLASS_NAME,"c", 'c.log_id = l.id');

        if ($time_from){
            $where .= " and l.action_time >= :from ";
            $params[":from"] = $time_from;
        }
        if ($time_to){
            $where .= " and l.action_time <= :to ";
            $params[":to"] = $time_to;
        }
        if ($search){
            $where .= " and (l.user_name like :search OR l.action_name like :search OR l.request_url like :search OR l.client_ip like :search 
            OR c.old_value like :search OR c.new_value like :search) ";
            $params[":search"] = "%{$search}%";
        }
        if ($method){
            $where .= " and l.request_method =:method ";
            $params[":method"] = $method;
        }

        if ($table){
            $where .= " and c.table like :table";
            $params[":table"] = "%{$table}%";
        }
        if ($column){
            $where .= " and c.column like :column";
            $params[":column"] = "%{$column}%";
        }
        $query->where($where)->order_By(Log_Model::F_ACTION_TIME, "DESC", 'l');
        return [$query, $params];
    }
    /**
     * @ignoreLog
     */
    public function index(){
        $request = $this->request;
        $page = $request->get_from_get('page');

        $pagesize = 10;//分页大小
        $page = $page<1 ? 1 : $page;
        $start = ($page-1) * $pagesize;
        list($query, $params) = $this->buildSQL();

        $this->set_view_data('yze_page_title', '操作日志');
        $this->set_view_data('total', $query->count('id', $params,'l'));
        $this->set_view_data('pagesize', $pagesize);
        $this->set_View_Data("logs", $query->limit($start, $pagesize)->select($params,'l'));
    }

    /**
     * @ignoreLog
     */
    public function detail(){
        $request = $this->request;
        $uuid = $request->get_var('uuid');

        $this->set_view_data('yze_page_title', '操作日志详情');
        $this->set_view_data('log', Log_Model::from()->where('uuid=:uuid and is_deleted=0')->get_Single([':uuid'=>$uuid]));
    }
    /**
     * @ignoreLog
     */
    public function post_remove(){
        $request = $this->request;
        $this->layout = "";
        list($query, $params) = $this->buildSQL();
        foreach ($query->select($params,'l') as $model){
            $model->remove();
        }
        return YZE_JSON_View::success($this);
    }
    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = 'error';
        if ($request->is_post()){
            $this->layout = '';
        }
        return YZE_JSON_View::error($this, $e->getMessage());
    }
}
?>
