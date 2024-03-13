<?php
namespace app\build;

use app\modules\build\views\code\wxmp\weui_wxmp\Page_View;

/**
 * 小程序页面设置编译
 */
$page = $this->get_data('page');

$moudule = $page->get_module();
$project = $moudule->get_project();
$config = json_decode(html_entity_decode($page->config), true);
$rootPath = '';

$build = new Build_Model($this->controller, $page, 1, $rootPath);
$build->set_img_Asset_Path($rootPath."assets/images/");
$view = Page_View::create_View($build);
// 在视图和弹窗的输出过程中，会产生响应的代码，所以这里也需要调用一下；
ob_start();
$view->output();
$view->build_popup_ui();
ob_clean();
$codeFragment = $view->build_code();
?>
{
    "component": <?= strtolower($page->page_type) == 'popup' ? 'true' : 'false'?>,
    "usingComponents": {
<?php
    $codes = [];
    foreach ($codeFragment->get_components() as $comp => $file){
        $codes[] = '        "'.$comp.'": "'.$file.'"';
    }
    echo join(",\r\n", $codes);
?>

    }
}
