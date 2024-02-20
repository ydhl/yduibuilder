<?php
namespace app\build;

use app\project\Project_Model;
use app\vendor\Env;

/**
 * web-html5 代码结构
 */
$page = $this->get_data('page');
$api_env = $this->get_data('api_env');
$relativePath = '../';
$project = $page->get_project();
$config = json_decode(html_entity_decode($page->config), true);
$project_setting = $project->get_setting();

$packages = $project->get_front_project_packages();
$packages = array_merge($packages['system'], $packages['user']);

$jsModule = [];
foreach ($packages as $package){
    if (! file_exists(YZE_PUBLIC_HTML."vendor/{$package}/install.php")) continue;
    include_once YZE_PUBLIC_HTML."vendor/{$package}/install.php";
    list ($packageName) = explode('@', trim($package));
    $packageClass = "{$packageName}_install";

    foreach ($packageClass::jsForPreview() as $js=>$type) {
        if ($type!='iife') {
            $jsModule["{$relativePath}vendor/{$package}/{$js}"] = $type;
        }
    }
}
$build = new Build_Model($this->controller, $page,1,$relativePath);
$build->set_img_Asset_Path($relativePath."assets/img/");
$build->set_api_env($api_env);

$ui = $project->get_setting_value(Env::UI);
$frontedFramework = $project->get_setting_value(Env::FRONTEND_FRAMEWORK);
$pageViewFile = YZE_APP_PATH."modules/build/views/code/web/{$ui}_{$frontedFramework}/page.view.php";
include_once $pageViewFile;
$pageView = "app\\modules\\build\\views\\code\\web\\{$ui}_{$frontedFramework}\\Page_View";
$view = $pageView::create_View($build);

foreach ((array)$jsModule as $file => $import) {
    echo "{$import} from '{$file}';\r\n";
}

$fragment = $view->build_code();
if ($ui=='layui'){
    $uses = $fragment->get_uses();
    $build->output_code( "layui.use(".($uses ? json_encode($uses).', ': '')."function (){", 0);
    foreach ($fragment->get_codes() as $codes){
        $build->output_code( $codes, 1);
    }
    $build->output_code( "})", 0);
}else {
    $build->output_code("function loaded(){", 0);
    $build->output_code($fragment->get_codes(), 1);
    $build->output_code( "}",0);
    $build->output_code( 'if (document.readyState === "complete" ||(document.readyState !== "loading" && !document.documentElement.doScroll)) {', 0);
    $build->output_code('loaded()', 1);
    $build->output_code('} else {', 0);
    $build->output_code('document.addEventListener("DOMContentLoaded", loaded);', 1);
    $build->output_code('}', 0);
}
?>
