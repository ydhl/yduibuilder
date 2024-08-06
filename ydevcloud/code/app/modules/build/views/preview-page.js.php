<?php
namespace app\build;
use app\modules\build\views\preview\Html_Code_Fragment;use app\modules\build\views\preview\Preview_View;
use app\vendor\Env;
use PhpOffice\PhpSpreadsheet\Helper\Html;use function yangzie\yze_get_abs_path;use function yangzie\yze_module_css_bundle;use function yangzie\yze_move_file;

/**
 * 输出实际框架的脚本代码
 */
$page = $this->get_data('page');
$project = $page->get_project();
$this->layout = '';
header('Content-Type: application/javascript');

$cssLib = [];
$jsLib = [];
$jsModule = [];
$isSubpage = intval($_GET['subpage']);// 作为模块加载

$project->fetch_css_js_libs('/', $cssLib, $jsLib, $jsModule);

$build = new Build_Model($this->controller, $page);
$build->set_need_mock(intval($_GET['mock']));
$build->set_api_env($_GET['api_env']);
$build->set_is_subpage($isSubpage);

$ui = $project->get_setting_value(Env::UI);
$pageView = "app\\modules\\build\\views\\preview\\{$ui}\\Page_View";
$topView = $pageView::create_View($build);

$fragment = $topView->build_code();

//  公共的库
foreach ((array)$jsModule as $file => $import) {
    echo "{$import} from '{$file}';".PHP_EOL;
}

//  包含的组件
foreach ((array)$fragment->get_subPage_modules() as $file => $import) {
    echo "import {$file} from '{$import['path']}';".PHP_EOL;
}
echo "import ydecloud from '/vendor/ydecloud@1/ydecloud.es.js';".PHP_EOL;
// html js 代码都以module的方式使用
$build->output_code(PHP_EOL.PHP_EOL."export default function (inputConfig = null){",0);
foreach ((array)$fragment->get_subPage_modules() as $file => $import) {
    $build->output_code($file."(".(json_encode($import['input']) ?: '').")", 1);
}

$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_BEGIN),1);
$build->output_code("Alpine.data('".$topView->myid()."', () => ({",1);
$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_DATA_DEFINE),2);
$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_EVENT),2);
$build->output_code("...ydecloud(Alpine),",2);
$build->output_code('init(){',2);
$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_INIT),3);
$build->output_code('}',2);
$build->output_code("}))",1);
$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_END),1);
$build->output_code(PHP_EOL."}",0);

?>
