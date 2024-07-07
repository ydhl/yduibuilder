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

// html js 代码都以module的方式使用
$build->output_code(PHP_EOL."export default function (inputConfig = null){", 0);
foreach ((array)$fragment->get_subPage_modules() as $file => $import) {
    $build->output_code($file."(".(json_encode($import['input']) ?: '').");", 1);
}
$commonCode = <<< COMMON_CODE
alpinejs_checked_name(el, values, valueName){
    const name = alpinejs_get_input_data_name(el, valueName);
    const checkValue =  Alpine.evaluate(el, `\${name}`);
    return alpinejs_update_checked_name(values, checkValue);
},
alpinejs_get_value(el, valueName){
    const name = alpinejs_get_input_data_name(el, valueName);
    return Alpine.evaluate(el, `\${name}`) || undefined;
},
alpinejs_in_array(el, valueName, check){
    const name = alpinejs_get_input_data_name(el, valueName);
    const value = Alpine.evaluate(el, `\${name}`) || undefined;

    if (value == undefined) return false
    if (value instanceof Array) return value.indexOf(check) !== -1
    if (value instanceof String) return value.split(',').indexOf(check) !== -1
    return value == check
},
alpinejs_get_index(el, prefix='', suffix=''){
    if (!el) return `\${prefix}\${suffix}`;
    const index = alpinejs_find_index(el);
    if (!index) return `\${prefix}\${suffix}`;
    return prefix + index.join("-") + suffix;
},
alpinejs_set_value(el, valueName, value){
    let name = valueName;
    if (valueName.match(/\[-1\]/)) {
        const index = alpinejs_find_index(el)
        name = valueName.replace(/\[-1\]$/, '')
        if (index.length > 0){
            for(const idx of index){
                name += '[' + idx + ']';
                Alpine.evaluate(el, `if (\${name} == undefined) \${name} = []`);
            }
        }
    }
    Alpine.evaluate(el, `\${name} = \${typeof value=="string" ? '"'+value+'"' : value}`)
},
COMMON_CODE;
$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_BEGIN),1);
$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_DATA_DEFINE),2);
$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_EVENT),2);
$build->output_code($commonCode,2);
$build->output_code('init(){',2);
$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_INIT),3);
$build->output_code('}',2);
$build->output_code($fragment->get_section_codes(Html_Code_Fragment::SECTION_END),1);
$build->output_code( "}",0);
?>
