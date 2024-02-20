<?php
namespace app\parallax;
use app\project\Function_Model;
use app\project\Page_Model;
use app\vendor\css\Css_Factory;
use app\vendor\Env;
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
* @package parallax
*/
class Load_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    public function index(){
        $request = $this->request;
        $this->layout = '';
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $pageId = $request->get_from_get('pageId');
        $page = find_by_uuid(Page_Model::CLASS_NAME, $pageId);
        if (!$page) return YZE_JSON_View::error($this, __('Page Not Found'));

        $function = $page->get_function();

        if ($page->is_deleted) return YZE_JSON_View::error($this, __('Page has been deleted'));

        $frontendAndUI = Env::getFrontendAndFramework();
        $package = Env::package();
        $function = $function ?: $page->get_function();
        $module = $function->get_module();
        $project = $module->get_project();
        $project_setting = $project->get_setting();

        $member = $project->get_member($loginUser->id);
        if (!$member) return YZE_JSON_View::error($this, __('Page Or Function Not Found'));
        $project_setting['id'] = $project->uuid;
        $project_setting['frontend'] = $project_setting['frontend'] ? $frontendAndUI[$project_setting['frontend']]['name'] : '';
        $project_setting['framework'] = $project_setting['frontend_framework'] ? $package[$project_setting['frontend_framework']]['name'] : '';
        $project_setting['name'] = $project->name;
        $project_setting['endKind'] = $project->end_kind;

        $codeTypes = @$package[$project_setting['frontend_framework']]['codeType'];
        $state = [
            "frontend"=> $project->frontend,
            "project"=>$project_setting,
            "canEdit"=> $member->can_edit(),
            'userRole'=>$member->role,
            "versionId"=>$page->last_version_id ?: -1,
            "module"=>[
                'id'=>$module->uuid,
                'name'=>$module->name
            ],
            "function"=>[
                'id'=>$function->uuid,
                'name'=>$function->name
            ],
            'selectedPageId' => $pageId,
            "page" => []
        ];
        $currPage = $pageId ? find_by_uuid(Page_Model::CLASS_NAME, $pageId) : null;

        $state['page'] = $currPage ? json_decode(html_entity_decode($currPage->config)) : null;

        $user = [
            'avatar'  => $loginUser->avatar,
            'nickname'=> $loginUser->nickname
        ];

        return YZE_JSON_View::success($this, [
            'design'=>$state,
            'user'=>$user
        ]);
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
