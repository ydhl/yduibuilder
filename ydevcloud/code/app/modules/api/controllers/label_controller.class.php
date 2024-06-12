<?php

namespace app\api;

use app\common\File_Model;
use app\project\Api_Folder_Model;
use app\project\Label_Model;
use app\project\Label_Target_Model;
use app\project\Page_Model;
use app\project\Web_Api_Model;
use app\vendor\Save_Model_Helper;
use app\vendor\Uploader;
use app\project\Project_Model;
use yangzie\YZE_DBAImpl;
use yangzie\YZE_FatalException;
use yangzie\YZE_Notpl_View;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_RuntimeException;
use yangzie\YZE_Hook;
use yangzie\YZE_JSON_View;
use yangzie\yze_remove_path;
use function yangzie\__;


class Label_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    /**
     * 加载目录结构
     *
     * @return YZE_JSON_View
     * @throws YZE_FatalException
     */
    public function index() {
        $request = $this->request;
        $this->layout = '';
        $uuid = trim($request->get_from_get("project_uuid"));
        $q = trim($request->get_from_get("q"));
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $project = find_by_uuid(Project_Model::CLASS_NAME, $uuid);
        if (!$project) throw new YZE_FatalException(__('Project not found'));
        $project_member = $project->get_member($loginUser->id);
        if (!$project_member) throw new YZE_FatalException(__('Project not found'));

        header("Content-Type: application/json; charset=utf-8");
        $data = [];
        foreach (Label_Model::from()
            ->where('is_deleted=0 and project_id=:tid and (name like :q or pinyin like :q or pinyin_sort like :q)')
            ->limit(0,10)
            ->select([":tid"=>$project->id,":q"=>"%{$q}%"]) as $l){
            $data[] = ['id'=>$l->uuid,'name'=>$l->name,'text'=>$l->name];
        }

        return YZE_JSON_View::success($this, $data);
    }

    public function exception(\Exception $e) {
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }

}

?>
