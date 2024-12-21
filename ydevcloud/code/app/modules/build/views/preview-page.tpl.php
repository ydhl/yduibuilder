<?php
namespace app\build;
use app\modules\build\views\preview\Preview_View;
use app\vendor\Env;
use function yangzie\yze_merge_query_string;
use function yangzie\yze_module_css_bundle;

/**
 * 输出实际框架的预览代码
 */
$page = $this->get_data('page');
$project = $page->get_project();

$cssLib = [];
$jsLib = [];
$jsModule = [];
$project->fetch_css_js_libs('/', $cssLib, $jsLib, $jsModule);

$build = new Build_Model($this->controller, $page, 2);
$build->set_api_env($_GET['api_env']);
$build->set_need_mock(intval($_GET['mock']));
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
    <link rel="stylesheet" type="text/css" href="<?= yze_merge_query_string("/preview/page/{$page->uuid}.css", $_GET)?>" />
</head>
<body>
    <div id="ydecloud-app">
<?php
$view->output();
?>
    </div>
<?php
echo $view->indent(1, true).join("\n".$view->indent(1, true), $jsLib);
echo "\n";
if ($page->page_type == 'popup'){
?>
    <script>$("#<?= $page->uuid?>").modal('show')</script>
<?php
}
?>
    <script type="module">
        <?php
        foreach ((array)$jsModule as $file => $import) {
            echo "{$import} from '{$file}';".PHP_EOL;
        }
        ?>

        import ydecloudRun from "<?= '/preview/page/'.$page->uuid.'.js'?>";
        alpinejs_init_directive(Alpine);
        if(document.readyState === "complete" ||(document.readyState !== "loading" && !document.documentElement.doScroll)) {
            ydecloudRun(window['_inputConfig_<?= $page->uuid?>'])
        } else {
            document.addEventListener("DOMContentLoaded", ydecloudRun)
        }
    </script>
<?= $view->indent(1, true).'<script type="text/javascript" src="/vendor/mockjs@1.0.1/mock.js"></script>'?>
</body>
</html>

