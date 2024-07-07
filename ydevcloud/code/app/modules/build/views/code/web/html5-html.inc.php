<?php
namespace app\build;

use app\vendor\Env;

/**
 * web-html5 代码结构
 */

$page = $this->get_data('page');
$api_env = $this->get_data('api_env');
$relativePath = '../';//生成的代码html文件放在【根目录/模块】下面
$project = $page->get_project();
$config = json_decode(html_entity_decode($page->config), true);

$cssLib = [];
$jsLib = [];
$jsModule = [];
$project->fetch_css_js_libs($relativePath, $cssLib, $jsLib, $jsModule);

$path = YZE_UPLOAD_PATH."project/{$project->uuid}/iconfont";
if (file_exists($path)){
    $cssLib[] = "<link rel='stylesheet' href='{$relativePath}vendor/iconfont/iconfont.css'>";
}

$build = new Build_Model($this->controller, $page,2,$relativePath);
$build->set_img_Asset_Path($relativePath."assets/img/");
$build->set_api_env($api_env);
$build->set_mode('compile');

$ui = $project->get_setting_value(Env::UI);
$frontedFramework = $project->get_setting_value(Env::FRONTEND_FRAMEWORK);
$pageViewFile = YZE_APP_PATH."modules/build/views/code/web/{$ui}_{$frontedFramework}/page.view.php";
include_once $pageViewFile;
$pageView = "app\\modules\\build\\views\\code\\web\\{$ui}_{$frontedFramework}\\Page_View";
$view = $pageView::create_View($build);

if ($page->page_type=='popup'){
?>
<link rel="stylesheet"  data-page-uuid="<?=$page->uuid?>" type="text/css" href="<?= $relativePath?>assets/css/<?= $page->get_export_file_name('html')?>.css" />
<?php
$view->output();
echo "\n";
?>
<script type="module" data-page-uuid="<?=$page->uuid?>">
    import ydecloudRun from "<?= $relativePath.'assets/js/'.$page->get_export_file_name('html').'.js?'.http_build_query($_GET) ?>";
    if(document.readyState === "complete" ||(document.readyState !== "loading" && !document.documentElement.doScroll)) {
        ydecloudRun()
    } else {
        document.addEventListener("DOMContentLoaded", ydecloudRun)
    }
</script>
<?php
}else{
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $page->name?></title>
    <?php echo join(PHP_EOL.$view->indent(0), $cssLib).PHP_EOL?>
    <link rel="stylesheet" type="text/css" href="<?= $relativePath?>assets/css/common.css" />
    <link rel="stylesheet" type="text/css" href="<?= $relativePath?>assets/css/<?= $page->get_export_file_name('html')?>.css" />
</head>
<body>
<?php
echo $view->indent(1, true).'<div id="ydecloud-app">'.PHP_EOL;
$view->output();
echo $view->indent(1, true).'</div>'.PHP_EOL;
echo $view->indent(1, true).join("\n".$view->indent(1, true), $jsLib);
echo PHP_EOL;
?>
    <script type="module">
    <?php
    foreach ((array)$jsModule as $file => $import) {
        echo "{$import} from '{$file}';".PHP_EOL;
    }
    ?>
    import ydecloudRun from "<?=$relativePath.'assets/js/'.$page->get_export_file_name('html').'.js?api_env='.$_GET['api_env']?>";

    alpinejs_init_directive(Alpine);
    if(document.readyState === "complete" ||(document.readyState !== "loading" && !document.documentElement.doScroll)) {
        ydecloudRun()
    } else {
        document.addEventListener("DOMContentLoaded", ydecloudRun)
    }
    </script>
</body>
</html>
<?php
}
?>
