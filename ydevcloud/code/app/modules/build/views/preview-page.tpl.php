<?php
namespace app\build;
use app\modules\build\views\preview\Preview_View;
use app\vendor\Env;
use function yangzie\yze_module_css_bundle;

/**
 * 输出实际框架的预览代码
 */
$page = $this->get_data('page');
$project = $page->get_project();
$project_setting = $project->get_setting();

$packages = $project->get_front_project_packages();
$packages = array_merge($packages['system'], $packages['user']);
$cssLib = [];
$jsLib = [];
$jsModule = [];
foreach ($packages as $package){
    if (!file_exists(YZE_PUBLIC_HTML."vendor/{$package}/install.php")) continue;
    include_once YZE_PUBLIC_HTML."vendor/{$package}/install.php";
    list ($packageName) = explode('@', trim($package));
    $packageClass = "{$packageName}_install";
    if (file_exists(YZE_PUBLIC_HTML."vendor/{$package}/index.css")){
        $cssLib[] = "<link rel='stylesheet' href='/vendor/{$package}/index.css'>";
    }

    foreach ($packageClass::jsForPreview() as $js=>$type) {
        if ($type!='iife') {
            $jsModule["/vendor/{$package}/{$js}"] = $type;
            continue;
        }
        $jsLib[] = "<script src='/vendor/{$package}/{$js}'></script>";
    }
}

$build = new Build_Model($this->controller, $page);
$build->set_api_env($_GET['api_env']);
$view = Preview_View::create_View($build);

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php echo join("\r\n", $cssLib);
    if (file_exists(YZE_PUBLIC_HTML."upload/project/{$project->uuid}/iconfont/iconfont.css")){?>
    <link rel="stylesheet" type="text/css" href="/upload/project/<?=$project->uuid?>/iconfont/iconfont.css" />
    <?php }?>
    <title><?= $page->name?></title>
    <link rel="stylesheet" type="text/css" href="/preview/page/<?= $page->uuid?>.css" />
</head>
<body>
<div id="ydecloud-app">
<?php
//单独查看弹窗
if ($page->page_type == 'popup'){
    echo "<style>.modal{display: block !important;}</style>";
    $style = 'overflow: hidden;height: 100vh;width: 100vw;'
        .'display: flex !important;align-items: center !important;'
        .'align-content: center !important;'
        .'justify-content: center !important;'
        .'background: linear-gradient(315deg, #dfdfdf, transparent);';
    echo '<div class="popup-background" style="'.$style.'">';
}

$view->output();

if ($page->page_type == 'popup'){
    echo '</div>';
}
?>
</div>

<?php
echo $view->indent(1, true).join("\n".$view->indent(0), $jsLib);
echo "\n";
?>
<?php
$view->build_popup_ui();
?>
<?= $view->indent(1, true).'<script type="module" src="/preview/page/'.$page->uuid.'.js?api_env='.$_GET['api_env'].'"></script>'?>
</body>
</html>

