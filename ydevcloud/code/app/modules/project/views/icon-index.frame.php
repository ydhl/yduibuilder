<?php
namespace app\project;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$project = $this->get_data('project');
$menu = $this->get_data('menu');
$icons = $project->get_icons();
$ui = $project->get_setting_value('ui');
$uiVersion = $project->get_setting_value('ui_version');
include_once YZE_PUBLIC_HTML."vendor/{$ui}@{$uiVersion}/install.php";
$uiFrameworkClass = $ui.'_install';
$uiIcons = $uiFrameworkClass::getIcons();
$type = strtolower(@$_GET['type']);
$this->layout = "empty";

if($type=='custom'){
?>
    <?php
    if ($icons){
    ?>
        <link rel="stylesheet" type="text/css" href="/upload/project/<?=$project->uuid?>/iconfont/iconfont.css" />
        <div class="d-flex flex-wrap">
    <?php
        foreach ($icons as $icon){
    ?>
        <div class="d-flex align-items-center flex-column m-2" style="width: 8rem">
            <i class="iconfont fs-1 icon-<?= $icon?>"></i>
            <div class="text-muted"><small><?= $icon?></small></div>
        </div>
    <?php }?>
        </div>
    <?php
    }else{
    ?>
        <div class="p-5 text-center bg-light"><?= __('No icons, you can upload now')?></div>
    <?php
    }
}
if (!$type || $type=='ui'){
?>
    <div class="d-flex flex-wrap">
    <?php
    if ($uiIcons){
        ?>
        <link rel="stylesheet" href="<?= "/vendor/{$ui}@{$uiVersion}/index.css"?>">
        <?php
        foreach ($uiIcons as $icon){
            ?>
            <div class="d-flex align-items-center flex-column m-2 p-3" style="width: 10rem">
                <i class="bi <?= $icon?> fs-1"></i>
                <div class="text-muted"><small><?= str_replace( 'bi-', '', $icon)?></small></div>
            </div>
            <?php
        }
    }
    ?>
    </div>
<?php }?>
