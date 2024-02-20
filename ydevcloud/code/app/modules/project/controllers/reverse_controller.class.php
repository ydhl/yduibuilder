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
* @package project
*/
class Reverse_Controller extends YZE_Resource_Controller {
    public function post_index(){
        $request = $this->request;
        $this->layout = '';
        $versionid = $request->get_var('versionid');
        $pageVersion = find_by_uuid(Page_Version_Model::CLASS_NAME, $versionid);
        if (!$pageVersion) return YZE_JSON_View::error($this, __('Page Not Found'));
        $page = $pageVersion->get_page();
        $module = $page->get_module();
        $project = $module->get_project();
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $project_member = $project->get_member($loginUser->id);

        if (!$project || !$project_member->can_edit()){
            return YZE_JSON_View::error($this, __('Page Not Found'));
        }
        $page->set(Page_Model::F_CONFIG, $pageVersion->config)
            ->set(Page_Model::F_SCREEN, $pageVersion->screen)
            ->set(Page_Model::F_LAST_VERSION_ID, $pageVersion->id)
            ->save();
        $sql = "delete from page_version where page_id={$page->id} and id > {$pageVersion->id}";
        YZE_DBAImpl::get_instance()->exec($sql);
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
