<?php
namespace app\build;

use app\project\Project_Model;
use app\vendor\Env;

/**
 * web-html5 代码结构
 */

$page = $this->get_data('page');
$api_env = $this->get_data('api_env');
$relativePath = '../';
$project = $page->get_project();
$config = json_decode(html_entity_decode($page->config), true);
$project_setting = $project->get_setting();

$packages = $project->get_front_project_packages();
$packages = array_merge($packages['system'], $packages['user']);
$cssLib = [];
$jsLib = [];
$jsModule = [];
foreach ($packages as $package){
    if (! file_exists(YZE_PUBLIC_HTML."vendor/{$package}/install.php")) continue;
    include_once YZE_PUBLIC_HTML."vendor/{$package}/install.php";
    list ($packageName) = explode('@', trim($package));
    $packageClass = "{$packageName}_install";
    if (file_exists(YZE_PUBLIC_HTML."vendor/{$package}/index.css")){
        $cssLib[] = "<link rel='stylesheet' href='{$relativePath}vendor/{$package}/index.css'>";
    }

    foreach ($packageClass::jsForPreview() as $js=>$type) {
        if ($type!='iife') {
            $jsModule["{$relativePath}vendor/{$package}/{$js}"] = $type;
            continue;
        }
        $jsLib[] = "<script src='{$relativePath}vendor/{$package}/{$js}'></script>";
    }
}
$path = YZE_UPLOAD_PATH."project/{$project->uuid}/iconfont";
if (file_exists($path)){
    $cssLib[] = "<link rel='stylesheet' href='{$relativePath}vendor/iconfont/iconfont.css'>";
}

$build = new Build_Model($this->controller, $page,1,$relativePath);
$build->set_img_Asset_Path($relativePath."assets/img/");
$build->set_api_env($api_env);

$ui = $project->get_setting_value(Env::UI);
$frontedFramework = $project->get_setting_value(Env::FRONTEND_FRAMEWORK);
$pageViewFile = YZE_APP_PATH."modules/build/views/code/web/{$ui}_{$frontedFramework}/page.view.php";
include_once $pageViewFile;
$pageView = "app\\modules\\build\\views\\code\\web\\{$ui}_{$frontedFramework}\\Page_View";
$view = $pageView::create_View($build);

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $page->name?></title>
    <?php echo join("\n".$view->indent(0), $cssLib)?>

    <link rel="stylesheet" type="text/css" href="/asset/css/common.css" />
    <link rel="stylesheet" type="text/css" href="/asset/css/<?= $page->get_export_file_name('html')?>.css" />
</head>
<body>
<?php
$view->output();
echo $view->indent(1, true).join("\n".$view->indent(0), $jsLib);
echo "\n";
$view->build_popup_ui();
$build->output_code('<script type="module" src="/asset/js/'.$page->get_export_file_name('html').'.js"></script>', 1);
?>
</body>
</html>
