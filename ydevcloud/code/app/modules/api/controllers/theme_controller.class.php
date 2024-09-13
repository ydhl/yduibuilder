<?php

namespace app\api;

use app\common\File_Model;
use app\project\Page_Model;
use app\project\Project_Setting_Model;
use app\vendor\Save_Model_Helper;
use app\vendor\Uploader;
use app\project\Project_Model;
use yangzie\YZE_FatalException;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_RuntimeException;
use yangzie\YZE_Hook;
use yangzie\YZE_JSON_View;
use yangzie\yze_remove_path;
use function yangzie\__;


class Theme_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    public function post_index() {
        $request = $this->request;
        $this->layout = '';
        $project_uuid = trim($request->get_from_post("project_uuid"));
        $project = find_by_uuid(Project_Model::CLASS_NAME, $project_uuid);
        if (!$project) throw new YZE_FatalException(__('project not found'));
        $colors = $request->get_from_post("color");
        $colorDarks = $request->get_from_post("colorDark");
        $supportDarkMode = trim($request->get_from_post("supportDarkMode"));
        $defaultFontSize = floatval($request->get_from_post("defaultFontSize"));
        $defaultSpacer = floatval($request->get_from_post("defaultSpacer"));
        $fontFaces = $request->get_from_post("fontFace");
        $customColors = $request->get_from_post("customColors");

        Project_Setting_Model::from()
            ->where("project_id=:pid and name in ('color','colorDark','customColors','supportDarkMode','defaultFontSize','defaultSpacer','fontFace')")
            ->delete([':pid'=>$project->id]);
        $colorMode = new Project_Setting_Model();
        $colorMode->set('uuid', Project_Setting_Model::uuid())
            ->set('project_id', $project->id)->set('name', 'color')
            ->set('value', json_encode($colors))->save();

        $colorMode = new Project_Setting_Model();
        $colorMode->set('uuid', Project_Setting_Model::uuid())
            ->set('project_id', $project->id)->set('name', 'colorDark')
            ->set('value', json_encode($colorDarks))->save();

        $colorMode = new Project_Setting_Model();
        $colorMode->set('uuid', Project_Setting_Model::uuid())
            ->set('project_id', $project->id)->set('name', 'supportDarkMode')
            ->set('value', $supportDarkMode ? 1 : 0)->save();

        $colorMode = new Project_Setting_Model();
        $colorMode->set('uuid', Project_Setting_Model::uuid())
            ->set('project_id', $project->id)->set('name', 'defaultFontSize')
            ->set('value', $defaultFontSize)->save();

        $colorMode = new Project_Setting_Model();
        $colorMode->set('uuid', Project_Setting_Model::uuid())
            ->set('project_id', $project->id)->set('name', 'defaultSpacer')
            ->set('value', $defaultSpacer)->save();

        $colorMode = new Project_Setting_Model();
        $colorMode->set('uuid', Project_Setting_Model::uuid())
            ->set('project_id', $project->id)->set('name', 'fontFace')
            ->set('value', json_encode($fontFaces))->save();

        $_ = [];
        foreach ($customColors as $key => $customColor){
            $_[$key] = json_decode(html_entity_decode($customColor));
        }
        $customColorSetting = new Project_Setting_Model();
        $customColorSetting->set('uuid', Project_Setting_Model::uuid())
            ->set('project_id', $project->id)->set('name', 'customColors')
            ->set('value', json_encode($_))->save();

        return YZE_JSON_View::success($this);
    }

    public function post_item() {
        $request = $this->request;
        $this->layout = '';
        $data = json_decode(file_get_contents("php://input"), true);

        $project_uuid = trim($data["project_uuid"]);
        $project = find_by_uuid(Project_Model::CLASS_NAME, $project_uuid);
        if (!$project) throw new YZE_FatalException(__('project not found'));


        Project_Setting_Model::from()
            ->where("project_id=:pid and name=:name")
            ->delete([':pid'=>$project->id,':name'=>$data['name']]);


        $setting = new Project_Setting_Model();
        $setting->set('uuid', Project_Setting_Model::uuid())
            ->set('project_id', $project->id)->set('name', $data['name'])
            ->set('value', json_encode($data['value']))->save();

        return YZE_JSON_View::success($this);
    }
    public function exception(\Exception $e) {
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }

}

?>
