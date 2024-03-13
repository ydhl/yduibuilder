<?php
namespace app\build;

use app\modules\build\views\code\wxmp\weui_wxmp\Page_View;

/**
 * 小程序样式编译
 */
$page = $this->get_data('page');
$moudule = $page->get_module();
$project = $moudule->get_project();
$config = json_decode(html_entity_decode($page->config), true);
$rootPath = '';

$build = new Build_Model($this->controller, $page, 1, $rootPath);
$build->set_img_Asset_Path($rootPath."assets/images/");
$view = Page_View::create_View($build);

$styles = $view->build_style(false);
?>
page{
    display: flex;
    align-items: stretch;
    align-content: stretch;
    min-height: 100vh;
}
<?php
foreach ($styles as $selector=>$style){
echo "{$selector} {\r\n  ".$style."\r\n}\r\n";
}
