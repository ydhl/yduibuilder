<?php
namespace app\build;
use app\modules\build\views\preview\Preview_View;
use app\vendor\Env;
use function yangzie\yze_module_css_bundle;

/**
 * 输出实际框架的脚本代码
 */
$page = $this->get_data('page');
$project = $page->get_project();
$project_setting = $project->get_setting();
$this->layout = '';
header('Content-Type: application/javascript');

$packages = $project->get_front_project_packages();
$packages = array_merge($packages['system'], $packages['user']);

$jsModule = [];
foreach ($packages as $package){
    if (!file_exists(YZE_PUBLIC_HTML."vendor/{$package}/install.php")) continue;
    include_once YZE_PUBLIC_HTML."vendor/{$package}/install.php";
    list ($packageName) = explode('@', trim($package));
    $packageClass = "{$packageName}_install";


    foreach ($packageClass::jsForPreview() as $js=>$type) {
        if ($type!='iife') {
            $jsModule["/vendor/{$package}/{$js}"] = $type;
        }
    }
}
$build = new Build_Model($this->controller, $page);
$build->set_api_env($_GET['api_env']);

$ui = $project->get_setting_value(Env::UI);
$view = Preview_View::create_View($build);

$space = $view->indent(2, true);
foreach ((array)$jsModule as $file => $import) {
    echo $space."{$import} from '{$file}';\r\n";
}

$fragment = $view->build_code();
if ($ui=='layui'){
    $uses = $fragment->get_uses();
    $build->output_code("layui.use(".($uses ? json_encode($uses).', ': '')."function (){", 2);
    $build->output_code($fragment->get_codes(), 3);
    $build->output_code("})", 2);
}else {
//        var_dump($fragment->get_codes());
    $build->output_code("function loaded(){",2);
    $build->output_code($fragment->get_codes(),3);
    $build->output_code("}",2);
    $build->output_code('if (document.readyState === "complete" ||(document.readyState !== "loading" && !document.documentElement.doScroll)) {', 2);
    $build->output_code('loaded()',3);
    $build->output_code('} else {',2);
    $build->output_code('document.addEventListener("DOMContentLoaded", loaded);',3);
    $build->output_code('}',2);
}
?>
