<?php
namespace app\build;
use app\modules\build\views\preview\Preview_View;
use app\vendor\Env;
use function yangzie\yze_module_css_bundle;

/**
 * 用于在预览模式下openModal拉取弹窗
 */
$page = $this->get_data('page');

$build = new Build_Model($this->controller, $page, 0);
$build->set_api_env($_GET['api_env']);
$build->set_is_subpage(true);// popup 作为subpage处理
$view = Preview_View::create_View($build);
?>
<link rel="stylesheet" data-page-uuid="<?= $page->uuid?>" type="text/css" href="/preview/page/<?= $page->uuid?>.css" />
<?php $view->output(); ?>

<script type="module" data-page-uuid="<?=$page->uuid?>">
    import ydecloudRun from "<?='/preview/page/'.$page->uuid.'.js?'.http_build_query($_GET)?>";
    if(document.readyState === "complete" ||(document.readyState !== "loading" && !document.documentElement.doScroll)) {
        ydecloudRun(window['_inputConfig_<?= $page->uuid?>'])
    } else {
        document.addEventListener("DOMContentLoaded", ydecloudRun)
    }
</script>
