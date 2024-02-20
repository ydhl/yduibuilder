<?php
namespace app\api;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$project = $this->get_data('project');
$icons = $project->get_icons();
$ui = $project->get_setting_value('ui');
$uiVersion = $project->get_setting_value('ui_version');
include_once YZE_PUBLIC_HTML."vendor/{$ui}@{$uiVersion}/install.php";
$uiFrameworkClass = $ui.'_install';
$icons = array_map(function ($item){
    return 'iconfont icon-'.$item;
}, $icons);
$icons = array_merge($icons, $uiFrameworkClass::getIcons());
$currIcon = @$_GET['icon'];
$queryIcon = @$_GET['q'];
$icons = array_filter($icons, function($item) use ($queryIcon){
    if ($queryIcon){
        return preg_match("/{$queryIcon}/", $item) ? true : false;
    }
    return true;
});
if (!$icons){
    ?>
    <div class="m-5 p-5 d-flex justify-content-center align-content-center">
        <?= sprintf(__("The %s has no icons, you can upload iconfont from here"), $ui)?>
    </div>
    <?php
    return;
}
?>
<form class="fixed-top pe-3" method="get">
    <input type="hidden" name="pid" value="<?= $project->uuid?>">
    <input type="hidden" name="icon" value="<?= $currIcon?>">
    <div class="input-group">
        <input type="text" name="q" value="<?= $queryIcon?>" autocomplete="off" class="form-control" placeholder="<?= __('search icons...')?>">
        <button type="submit" class="btn btn-primary"><?= __('Search')?></button>
    </div>
</form>
<div class="d-flex flex-wrap mt-5">
    <link rel="stylesheet" type="text/css" href="/upload/project/<?=$project->uuid?>/iconfont/iconfont.css" />
    <link rel="stylesheet" type="text/css" href="<?= "/vendor/{$ui}@{$uiVersion}/index.css"?>">
    <?php
if ($icons){
    ?>
        <?php
        foreach ($icons as $icon){
            ?>
            <div onclick="select(this, '<?= $icon?>')" class="d-flex cursor align-items-center align-content-center flex-column m-3" style="width: 7rem">
                <i class="fs-1 bg-light border <?= $currIcon==$icon ? 'border-primary text-primary' : ''?> icon p-5 <?= $icon?>"></i>
                <div class="text-muted text-center" style="font-size: 11px"><?= $icon?></div>
            </div>
        <?php
        }
}
?>
</div>
<script>
    function select(obj, icon) {
        $('.icon').removeClass('text-primary border-primary');
        $(obj).find('.icon').addClass('text-primary border-primary');
        parent.postMessage({type:'icon',icon: icon}, '*')
    }
</script>
