<?php
namespace app\project;
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
class Index_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'project');
        $this->layout = "admin";
    }

    /**
     * @actionname 访问项目列表
     */
    public function index(){
        $request = $this->request;
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        //$this->layout = 'tpl name';
        $this->set_view_data('yze_page_title', __('My Projects'));
        // 我参与的项目
        $members = [];
        foreach ( Project_Member_Model::from('pm')
                ->left_join(Project_Model::CLASS_NAME, 'p', 'pm.project_id = p.id')
                ->where('pm.user_id=:uid and p.is_deleted=0 and pm.is_deleted=0 and pm.is_invited=1')
                ->select([':uid'=>$loginUser->id]) as $obj){
            $obj['pm']->set_project($obj['p']);
            $members[] = $obj['pm'];
        }

        // 邀请我的项目
        $invite_members = [];
        foreach ( Project_Member_Model::from('pm')
                      ->left_join(Project_Model::CLASS_NAME, 'p', 'pm.project_id = p.id')
                      ->where('pm.user_id=:uid and p.is_deleted=0 and pm.is_deleted=0 and pm.is_invited=0')
                      ->select([':uid'=>$loginUser->id]) as $obj){
            $obj['pm']->set_project($obj['p']);
            $invite_members[] = $obj['pm'];
        }
        $this->set_View_Data('invite_members', $invite_members);
        $this->set_View_Data('members', $members);
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
