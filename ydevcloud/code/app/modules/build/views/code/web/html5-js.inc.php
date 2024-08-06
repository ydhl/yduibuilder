<?php
namespace app\build;

use app\modules\build\views\preview\Html_Code_Fragment;
use app\vendor\Env;

/**
 * web-html5 代码结构
 */
$page = $this->get_data('page');
$api_env = $this->get_data('api_env');
$relativePath = '../../';// 生成的代码js文件放在【根目录/assets/js】下面
$project = $page->get_project();
$config = json_decode(html_entity_decode($page->config), true);

$cssLib = [];
$jsLib = [];
$jsModule = [];
$isSubpage = intval($_GET['subpage']);// 作为模块加载
$project->fetch_css_js_libs($relativePath, $cssLib, $jsLib, $jsModule);

$build = new Build_Model($this->controller, $page,1,$relativePath);
$build->set_img_Asset_Path($relativePath."assets/img/");
$build->set_mode('compile');
$build->set_api_env($api_env);
$build->set_is_subpage($isSubpage);

$ui = $project->get_setting_value(Env::UI);
$frontedFramework = $project->get_setting_value(Env::FRONTEND_FRAMEWORK);
$pageViewFile = YZE_APP_PATH."modules/build/views/code/web/{$ui}_{$frontedFramework}/page.view.php";
include_once $pageViewFile;
$pageView = "app\\modules\\build\\views\\code\\web\\{$ui}_{$frontedFramework}\\Page_View";
$view = $pageView::create_View($build);

$fragment = $view->build_code();

//  公共的库
foreach ((array)$jsModule as $file => $import) {
    echo "{$import} from '{$file}';\r\n";
}

//  包含的组件
foreach ((array)$fragment->get_subPage_modules() as $file => $import) {
    echo "import {$file} from '{$import['path']}';".PHP_EOL;
}
echo "import ydecloud from '../../ydecloud@1/ydecloud.es.js';".PHP_EOL;
// html js 代码都以module的方式使用
$build->output_code(PHP_EOL."export default function (inputConfig = null){", 0);
foreach ((array)$fragment->get_subPage_modules() as $file => $import) {
    $build->output_code($file."(".(json_encode($import['input']) ?: '').");", 1);
}

$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_BEGIN),1);
$build->output_code("Alpine.data('".$view->myid()."', () => ({",1);
$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_DATA_DEFINE),2);
$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_EVENT),2);
$build->output_code("...ydecloud(Alpine),",2);
$build->output_code('init(){',2);
$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_INIT),3);
$build->output_code('}',2);
$build->output_code("}))",1);
$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_END),1);
$build->output_code( "}",0);
?>
