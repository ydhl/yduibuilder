<?php
namespace app\api;
use app\common\File_Model;
use yangzie\YZE_JSON_View;
use function yangzie\__;
use function yangzie\yze_isimage;

$project = $this->get_data('project');
$files = File_Model::get_files($_GET['q'], $project->id, $_GET['type'],$_GET['page']);
$this->layout = '';
$datas = [];
foreach ($files as $file){
    $data = $file->get_records();
    $data['id'] = $data['uuid'];
    $data['name'] = $data['file_name'];
    if (yze_isimage($data['url'])){
        $data['url'] = UPLOAD_SITE_URI.$data['url'];
    }
    unset($data['uuid'],$data['project_id'],$data['file_name']);
    $datas[] = $data;
}

YZE_JSON_View::success($this->controller, $datas)->output();
