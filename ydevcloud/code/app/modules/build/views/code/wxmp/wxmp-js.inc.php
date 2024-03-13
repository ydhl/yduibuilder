<?php
namespace app\build;

use app\modules\build\views\code\wxmp\weui_wxmp\Page_View;

/**
 * 小程序界面编译
 */
$page = $this->get_data('page');

$moudule = $page->get_module();
$project = $moudule->get_project();
$config = json_decode(html_entity_decode($page->config), true);
$rootPath = '';
$pageName = $page->file?:$page->name;

$build = new Build_Model($this->controller, $page, 1, $rootPath);
$build->set_img_Asset_Path($rootPath."assets/images/");
$view = Page_View::create_View($build);
// 在视图和弹窗的输出过程中，会产生响应的代码，所以这里也需要调用一下；
ob_start();
$view->output();
$view->build_popup_ui();
ob_clean();
$codeFragment = $view->build_code();


foreach ((array)@$codeFragment->get_require() as $varName => $path){
    echo "const $varName = require(\"{$path}\");\r\n";
}
?>
const app = getApp();
<?php
$build->output_code($codeFragment->get_global(), 0);
?>
Page({
<?php
if ($codeFragment->get_data()){
    echo "    data: \r\n";
    $build->output_code($view->formatWXMPJSON($codeFragment->get_data()), 1);
    echo ",\r\n";
}

?>
<?php
foreach ($codeFragment->get_function() as $function => $code){
    echo "    {$function} {\r\n";
    $build->output_code($code, 2);
    echo "    },\r\n";
}

foreach ($codeFragment->get_lifecycle() as $lifecycleName => $code){
    echo "    {$lifecycleName}: function (options) {\r\n";
    $build->output_code($code, 2);
    echo "    },\r\n";
}
?>
})
