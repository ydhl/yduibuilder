<?php
namespace app\build;

use app\modules\build\views\preview\Preview_View;
use app\project\Page_Model;
use app\project\Project_Model;
use app\vendor\Env;

/**
 * web-html5 代码结构
 */
$page = $this->get_data('page');
$api_env = $this->get_data('api_env');
$mode = $this->get_data('mode');// preview 预览代码(默认)，compile 编译代码模式
$relativePath = '../../';//生成的代码css 文件放在【根目录/assets/css】下面
$project = $page->get_project();

$subPageIds = [];
$page->fetchSubPageIds(null, $subPageIds);
$subPageIds = array_unique($subPageIds);
$pages = find_by_uuids(Page_Model::CLASS_NAME, $subPageIds);
$pages[] = $page;

$ui = $project->get_setting_value(Env::UI);
$frontedFramework = $project->get_setting_value(Env::FRONTEND_FRAMEWORK);
$pageViewFile = YZE_APP_PATH."modules/build/views/code/web/{$ui}_{$frontedFramework}/page.view.php";
include_once $pageViewFile;
$pageView = "app\\modules\\build\\views\\code\\web\\{$ui}_{$frontedFramework}\\Page_View";

// 当前页面及所有子页的公共样式
$commonStyles = [];
$styles = [];
foreach ($pages as $page){
    $build = new Build_Model($this->controller, $page,0,$relativePath);
    $build->set_img_Asset_Path($relativePath."assets/img/");
    $build->set_api_env($api_env);
    $build->set_mode($mode?:'preview');

    $view = $pageView::create_View($build);
    if ($mode!='compile') {// 在预览模式时，把公共css内容也输出出来
        $commonStyles = array_merge($commonStyles, $view->build_common_style());
    }
    $styles = array_merge($styles, $view->build_style(false));
}

foreach ($commonStyles as $selector=>$style){
    if (!trim($style)) continue;
    $build->output_code( $selector.' {',0);
    $build->output_code($style, 1);
    $build->output_code('}', 0);
}

foreach ($styles as $selector => $style){
    if (!trim($style)) continue;
    $build->output_code($selector.' {', 0);
    $build->output_code($style, 1);
    $build->output_code('}', 0);
}
?>

