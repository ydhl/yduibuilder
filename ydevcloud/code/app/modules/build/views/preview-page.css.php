<?php
namespace app\build;
use app\modules\build\views\preview\Preview_View;
use app\project\Page_Model;

/**
 * 输出实际框架的Css代码
 */
$this->layout = '';
header('Content-Type: text/css');
$page = $this->get_data('page');
$subPageIds = [];
$page->fetchSubPageIds(null, $subPageIds);
$subPageIds = array_unique($subPageIds);
$pages = find_by_uuids(Page_Model::CLASS_NAME, $subPageIds);
$pages[] = $page;

// 当前页面及所有子页的公共样式
$commonStyles = [];
$styles = [];
$pageViews = [];
foreach ($pages as $page){
    $build = new Build_Model($this->controller, $page);
    $pageView = Preview_View::create_View($build);
    $pageViews[] = $pageView;
    $commonStyles = array_merge($commonStyles, $pageView->build_common_style());
    $styles = array_merge($styles, $pageView->build_style(false));
}
foreach ($commonStyles as $selector => $style){
    $build->output_code($selector.' {', 0);
    $build->output_code($style, 1);
    $build->output_code('}', 0);
}

// 当前页面及子页的元素样式
foreach ($styles as $selector => $style){
    $build->output_code($selector.' {', 0);
    $build->output_code($style, 1);
    $build->output_code('}', 0);
}


