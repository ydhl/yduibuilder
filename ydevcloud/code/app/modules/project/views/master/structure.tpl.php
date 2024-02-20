<?php
namespace app\project;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;
use function yangzie\yze_merge_query_string;

$project = $this->get_data('project');
$this->master_view = 'master/project';
$modules = $project->get_modules();
$breadcrumbs = $this->get_data('breadcrumbs');
$request = YZE_Request::get_instance();
?>
<div class="d-flex justify-content-between">
    <h3 class="mb-3"><i class="iconfont icon-module fs-2"></i> <?= __('UI')?></h3>
    <div class="d-flex align-items-center justify-content-center">

        <a href="/project/<?= $project->uuid?>/recycle"
           class="btn me-2 btn-light <?= $request->controller_name(true)=='recycle' ? "active" : ""?>"><i class="iconfont icon-recycle"></i> <?= sprintf(__('Recycle %s'), '('.$project->recyclePageCount().')')?></a>
        <a href="/project/<?= $project->uuid?>/homepage"
           class="btn me-2 btn-light <?= $request->controller_name(true)=='homepage' ? "active" : ""?>"><i class="iconfont icon-homepage"></i> <?= __('Home Page')?></a>
    </div>
</div>
<?php
if (!$modules){
?>
    <div class="p-5 m-5 text-center">
        <p><?= __('please add module and function first')?></p>
        <a href="javascript:;" data-url="/project/<?= $project->uuid?>/addmodule" data-title="<?= __('Add Module')?>"
           data-size="sm" class="btn btn-primary yd-dialog"><?= __('Add Module')?></a>
    </div>
<?php
    return;
}
if ($breadcrumbs){
    $i = 0
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <?php foreach ($breadcrumbs as $url => $name){
            if (count($breadcrumbs) == ++$i){
            ?>
                <li class="breadcrumb-item active" aria-current="page"><?= $name?></li>
            <?php
            }else{
            ?>
                <li class="breadcrumb-item"><a href="<?= $url?>"><?= $name?></a></li>
            <?php
            }
            ?>
        <?php }?>
    </ol>
</nav>
<?php
}
?>
<div class="flex-fill">
    <?php echo $this->content_of_view()?>
</div>
