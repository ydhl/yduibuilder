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
$curr_module = $this->get_data('curr_module');
$loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
$this->master_view = 'master/structure';
?>
<div class="d-flex align-items-center">
    <?php
        $functions = $curr_module->get_functions();
        foreach ($functions as $function){
            ?>
        <div class="position-relative btn btn-light me-3">
            <a class="d-flex me-3 align-items-center justify-content-center flex-column btn btn-light" style="width: 120px"
               href="/project/<?= $project->uuid?>/page?uuid=<?= $function->uuid?>">
                <i class="iconfont icon-folder fs-1 d-block"></i>
                <div class="fs-7 text-center text-truncate w-100"><?= $function->name?></div>
            </a>
            <?php
            if ($project->can_edit($loginUser->id)){
                ?>
                <div class="dropdown" style="position: absolute; top: 0; right: 0">
                    <i class="iconfont icon-more" type="button" data-bs-toggle="dropdown" aria-expanded="false"></i>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a href="javascript:;" data-url="/function/<?= $function->uuid?>/edit" class="dropdown-item yd-dialog"><i class="iconfont icon-edit"></i> <?= __('Edit Function')?></a></li>
                        <li><a href="javascript:;" class="dropdown-item text-danger yd-prompt" data-dialog-id="<?= $function->uuid?>" data-prompt-cb="delete_function_confirm" data-type="text"
                               data-title="<?= sprintf(__('This action cannot be undone.  please input the function name %s to delete'), "<code>{$function->name}</code>")?>"><i class="iconfont icon-remove"></i> <?= __('Delete Function')?></a></li>
                    </ul>
                </div>
            <?php }?>
        </div>
        <?php
        }
    ?>
    <div data-url="/module/<?= $curr_module->uuid?>/addfunction" data-title="<?= __('Add Function')?>"
         class="btn btn-light me-3 yd-dialog">
        <a href="javascript:;" class="d-flex me-3 align-items-center justify-content-center flex-column btn btn-light" style="width: 120px">
            <i class="iconfont icon-plus fs-1 d-block"></i>
            <div class="fs-7 text-center text-truncate w-100"><?= __('Add Function')?></div>
        </a>
    </div>
</div>
