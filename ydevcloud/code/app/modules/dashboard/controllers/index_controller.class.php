<?php
namespace app\dashboard;
use app\project\Function_Model;
use app\project\Module_Model;
use app\project\Page_Model;
use app\project\Project_Member_Model;
use app\project\Project_Model;
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
* @package dashboard
*/
class Index_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'dashboard');
        $this->layout = "admin";
    }

    /**
     * @actionname 访问控制台
     */
    public function index(){
        $request = $this->request;
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $recent_pages = [];
        //
        foreach (Page_Model::from('p')
            ->left_join(Function_Model::CLASS_NAME, 'f', 'f.id = p.function_id')
            ->left_join(Module_Model::CLASS_NAME, 'm', 'm.id = f.module_id')
            ->left_join(Project_Model::CLASS_NAME, 'pro', 'pro.id = m.project_id')
            ->left_join(Project_Member_Model::CLASS_NAME, 'pm', 'pro.id = pm.project_id')
            ->where('pm.user_id=:uid and pm.is_deleted=0 and p.is_deleted=0 and pro.is_deleted=0 and pm.is_invited=1 and ((p.id = pm.last_page_id and f.id = pm.last_function_id) or (p.id = pro.last_page_id and f.id = pro.last_function_id))')
            ->order_By('modified_on', 'desc','p')
            ->limit(0,5)->select([":uid"=>$loginUser->id]) as $objs){
            if ($objs['f']){
                $objs['m']->set_project($objs['pro']);
                $objs['f']->set_module($objs['m']);
            }
            $recent_pages[] = ['page'=>$objs['p'],'function'=>$objs['f']];
        }
        // 如果没有最近编辑的页面，则展示最新改动的项目
        if (!$recent_pages){
            foreach ( Project_Member_Model::from('pm')
              ->left_join(Project_Model::CLASS_NAME, 'p', 'pm.project_id = p.id')
              ->left_join(Page_Model::CLASS_NAME, 'page', 'page.id = p.last_page_id')
              ->left_join(Function_Model::CLASS_NAME, 'func', 'func.id = p.last_function_id')
              ->left_join(Module_Model::CLASS_NAME, 'm', 'm.id = func.module_id')
              ->where('pm.user_id=:uid and p.is_deleted=0 and page.is_deleted=0 and pm.is_deleted=0 and pm.is_invited=1 and page.id is not null')
              ->select([':uid'=>$loginUser->id]) as $objs){
                if ($objs['func']){
                    $objs['m']->set_project($objs['p']);
                    $objs['func']->set_module($objs['m']);
                }
                $recent_pages[] = ['page'=>$objs['page'],'function'=>$objs['func']];
            }
        }

        // 邀请我的项目
        $invitedCount= Project_Member_Model::from('pm')
                      ->left_join(Project_Model::CLASS_NAME, 'p', 'pm.project_id = p.id')
                      ->where('pm.user_id=:uid and p.is_deleted=0 and pm.is_deleted=0 and pm.is_invited=0')
                      ->count('id', [':uid'=>$loginUser->id], 'pm');
        $this->set_View_Data('invitedCount', $invitedCount);
        $this->set_view_data('recent_pages', $recent_pages);
        $this->set_view_data('yze_page_title', __('Dashboard'));
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
