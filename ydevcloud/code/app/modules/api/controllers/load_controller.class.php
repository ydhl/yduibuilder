<?php
namespace app\api;
use app\project\Function_Model;
use app\project\Page_Model;
use app\project\Project_Model;
use app\project\Project_Setting_Model;
use app\vendor\css\Css_Factory;
use app\vendor\Env;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_JSON_View;
use function yangzie\__;

/**
*
* @version $Id$
* @package api
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

    /**
     * 加载页面或组件
     * @actionname 加载页面
     */
    public function index(){
        $request = $this->request;
        $this->layout = '';
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $pageId = $request->get_from_get('pageId');
        $justPage = intval($request->get_from_get('justPage'));
        $functionId = $request->get_from_get('functionId');// 在功能下创建新新页面
        $page = $pageId ? find_by_uuid(Page_Model::CLASS_NAME, $pageId) : null;
        if ($pageId && !$page) return YZE_JSON_View::error($this, __('Page Not Found'));

        $project = $page ? $page->get_project() : null;

        $function = $functionId ? find_by_uuid(Function_Model::CLASS_NAME, $functionId) : null;
        if ($functionId && !$function) return YZE_JSON_View::error($this, __('Function Not Found'));

        if (!$page && !$function && !$project) return YZE_JSON_View::error($this, __('Page Or Function Not Found'));
        if ($page->is_deleted) return YZE_JSON_View::error($this, __('Page has been deleted'));

        $frontendAndUI = Env::getFrontendAndFramework();
        $package = Env::package();
        // 页面必然属于某个moudule和function，组件或者popup则不属于某个module，function
        if ($page || $function){
            $function = $function ?: $page->get_function();
            $module = $function ? $function->get_module() : null;
            if (!$project){
                $project = $module ? $module->get_project() : null;
            }
        }

        if (!$project) return YZE_JSON_View::error($this, __('Project Not Found'));
        if ($justPage){
            $state = [ "versionId"=>$page->last_version_id ?: -1, "page" => []];
            if ($function){
                $state['module'] = ['id'=>$module->uuid, 'name'=>$module->name];
                $state['function'] = ['id'=>$function->uuid, 'name'=>$function->name];
            }

            $state['page'] = $page ? $page->get_config() : null;
            return YZE_JSON_View::success($this, [ 'design'=>$state ]);
        }

        $project_setting = $project->get_setting();

        $member = $project->get_member($loginUser->id);
        if (!$member) return YZE_JSON_View::error($this, __('Page Or Function Not Found'));
        $project_setting['id'] = $project->uuid;
        $project_setting['keyId'] = $project->id;
        $project_setting['frontend'] = $project_setting['frontend'] ? $frontendAndUI[$project_setting['frontend']]['name'] : '';
        $project_setting['framework'] = $project_setting['frontend_framework'] ? $package[$project_setting['frontend_framework']]['name'] : '';
        $project_setting['name'] = $project->name;
        $project_setting['endKind'] = $project->end_kind;

        $codeTypes = @$package[$project_setting['frontend_framework']]['codeType'];
        $state = [
            "frontend"=> $project->frontend,
            "project"=>$project_setting,
            "versionId"=>$page->last_version_id ?: -1,
            "canEdit"=> $member->can_edit(),
            'userRole'=>$member->role,
            "endKind"=> $project->end_kind,
            "rewrite"=> $package[$project_setting['frontend_framework']]['rewrite'], // 框架是否支持路径重载
            "codeTypes"=> $codeTypes?:null,
            "simulateModel"=>$project->end_kind === "mobile" ? "portrait" : "pc",
            "page" => []
        ];
        if ($function){
            $state['module'] = [
                'id'=>$module->uuid,
                'name'=>$module->name
            ];
            $state['function'] = [
                'id'=>$function->uuid,
                'name'=>$function->name
            ];
        }

        $state['page'] = $page ? $page->get_config() : null;

        $factory = Css_Factory::getFactory($project_setting['ui']);

        $user = [
            'avatar'  => $loginUser->avatar,
            'nickname'=> $loginUser->nickname
        ];

        return YZE_JSON_View::success($this, [
            'design'=>$state,
            'user'=>$user,
            'predefineCSS'=>Css_Factory::getDefine(),
            'cssTranslate'=>$factory->cssTranslat($project_setting['ui_version'])
        ]);
    }

    /**
     * @actionname 加载API
     * @deprecated
     */
    public function api(){
        $request = $this->request;
        $this->layout = '';
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $projectId = $request->get_from_get('projectId');

        $project = $projectId ? find_by_uuid(Project_Model::CLASS_NAME, $projectId) : null;
        if (!$project) return YZE_JSON_View::error($this, __('Project Not Found'));

        $frontendAndUI = Env::getFrontendAndFramework();
        $package = Env::package();

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
            "endKind"=> $project->end_kind,
            "codeTypes"=> $codeTypes?:null,
        ];

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
