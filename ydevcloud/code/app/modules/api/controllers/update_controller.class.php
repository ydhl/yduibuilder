<?php

namespace app\api;

use app\common\File_Model;
use app\project\Page_Model;
use app\vendor\Uploader;
use app\project\Project_Model;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_RuntimeException;
use yangzie\YZE_Hook;
use yangzie\YZE_JSON_View;
use yangzie\yze_remove_path;
use function yangzie\__;


class Update_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    public function post_homepage() {
        $request = $this->request;
        $this->layout = '';
        $id = $request->get_from_post('pageid');
        $projectid = $request->get_from_post('projectid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $projectid);
        $page = find_by_uuid(Page_Model::CLASS_NAME, $id);
        if (!$project) return YZE_JSON_View::error($this, __('project not found'));
        if (!$page) return YZE_JSON_View::error($this, __('Page Not Found'));
        $project->set(Project_Model::F_HOME_PAGE_ID, $page ? $page->id : 0)->save();
        return YZE_JSON_View::success($this);
    }
    public function exception(\Exception $e) {
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }

}

?>
