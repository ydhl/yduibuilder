<?php
namespace app\build;

use app\project\Page_Bind_Style_Model;
use app\project\Page_Model;
use app\project\Project_Model;
use app\project\Project_Setting_Model;
use app\project\Style_Model;
use app\vendor\Env;

/**
 * 编译一个web-html5项目的公共css
 */
$project = $this->get_data('project');
$api_env = $this->get_data('api_env');
$relativePath = '../../';//生成的代码css 文件放在【根目录/assets/css】下面

$build = new Build_Model($this->controller, null,0,$relativePath);
$build->set_project($project);
$build->set_img_Asset_Path($relativePath."assets/img/");
$build->set_api_env($api_env);

// 由于这里是编译公共的css，与具体的view无关，只是为了使用view中的style处理方法，所以采用Page_View,
$ui = $project->get_setting_value(Env::UI);
$frontedFramework = $project->get_setting_value(Env::FRONTEND_FRAMEWORK);
$viewFile = YZE_APP_PATH."modules/build/views/code/web/{$ui}_{$frontedFramework}/page.view.php";
include_once $viewFile;
$viewClass = "app\\modules\\build\\views\\code\\web\\{$ui}_{$frontedFramework}\\Page_View";
$view = new $viewClass([], $this->controller, $build);

$styles = [];
foreach (Page_Bind_Style_Model::from('bs')
     ->left_join(Style_Model::CLASS_NAME,'s', 's.id=bs.style_id')
     ->left_join(Page_Model::CLASS_NAME,'p', 'p.id=bs.page_id')
     ->where('p.project_id=:pid and bs.is_deleted=0 and s.is_deleted=0')
    ->select([':pid'=>$project->id], 's') as $style){

    $styleValues = $view->translate_style(json_decode(html_entity_decode($style->meta), true));
    $styles[".".$style->class_name] =  join(';'.PHP_EOL, $styleValues).';';
}

foreach ($styles as $selector=>$style){
    if (!trim($style)) continue;
    $build->output_code($selector.' {', 0);
    $build->output_code($style, 1);
    $build->output_code('}', 0);
}


$globalCssVariable = Project_Setting_Model::get_setting_value($project->id, 'customColors');
if ($globalCssVariable){
    $build->output_code(':root{', 0);
    foreach ($globalCssVariable as $variable) {
        $build->output_code("--{$variable['uuid']}:{$variable['color']};", 1);
        if ($variable['dark']){
            $build->output_code("--{$variable['uuid']}-dark:{$variable['dark']};", 1);
        }
    }
    $build->output_code('}', 0);
}
?>

