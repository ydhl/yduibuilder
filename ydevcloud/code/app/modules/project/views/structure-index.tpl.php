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
$loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
$this->master_view = 'master/structure';
$modules = $project->get_modules();
?>
<div class="d-flex align-items-center">
    <?php
        foreach ($modules as $module){
    ?>
    <div class="position-relative btn btn-light me-3">
        <a class="d-flex align-items-center btn p-0 justify-content-center flex-column" style="width: 120px"
           href="/project/<?= $project->uuid?>/func?uuid=<?= $module->uuid?>">
            <i class="iconfont icon-folder fs-1 d-block"></i>
            <div class="fs-7 text-center text-truncate w-100"><?= $module->name?><?= $module->folder ? "&nbsp;<span class='text-muted'>({$module->folder})</span>" : ""?></div>
            <div class="fs-7 text-center text-truncate text-muted w-100"><?= $module->desc?>&nbsp;</div>
        </a>
        <?php
        if ($project->can_edit($loginUser->id)){
            ?>
            <div class="dropdown" style="position: absolute; top: 0; right: 0">
                <i class="iconfont icon-more" type="button" data-bs-toggle="dropdown" aria-expanded="false"></i>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a href="/preview/<?= $project->uuid?>?module=<?= $module->uuid?>" class="dropdown-item"><i class="iconfont icon-run"></i> <?= __('Preview')?></a></li>
                    <li><a href="#" data-size="sm" data-url="/module/<?= $module->uuid?>/edit" class="dropdown-item yd-dialog"><i class="iconfont icon-edit"></i> <?= __('Edit')?></a></li>
                    <li><a href="#" class="dropdown-item text-danger yd-prompt" data-dialog-id="<?= $module->uuid?>" data-prompt-cb="delete_module_confirm" data-redirect="reload" data-type="text"
                           data-title="<?= sprintf(__('This action cannot be undone. You will lose this module\'s funciton, page, ui and api, please input the module name %s to delete'), "<code>{$module->name}</code>")?>"
                        ><i class="iconfont icon-remove"></i> <?= __('Delete Module')?></a></li>
                    <li><a href="#" data-url="/module/<?= $module->uuid?>/addfunction" data-title="<?= __('Add Function')?>" class="dropdown-item yd-dialog"><i class="iconfont icon-function"></i> <?= __('Add Function')?></a></li>
                </ul>
            </div>
        <?php }?>
    </div>
    <?php
        }
    ?>

    <div class="position-relative btn btn-light yd-dialog me-3" data-size="sm" data-url="/project/<?= $project->uuid?>/addmodule" data-title="<?= __('Add Module')?>">
        <a class="d-flex align-items-center btn p-0 justify-content-center flex-column" style="width: 120px"
           href="javascript:;">
            <i class="iconfont icon-plus fs-1 d-block"></i>
            <div class="fs-7 text-center text-truncate w-100"><?= __('Add Module')?></div>
            <div class="fs-7 text-center text-truncate text-muted w-100">&nbsp;</div>
        </a>
    </div>
</div>
