<?php
namespace app\dashboard;
use app\common\Option_Model;
use app\project\Project_Model;
use app\vendor\Env;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$recent_pages = $this->get_data('recent_pages');
$invitedCount = $this->get_data('invitedCount');
$loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
$env = Env::package();
?>
<h4 class="border-bottom pb-2 text-muted"><?= __('Recently Projects')?></h4>
<div class="d-flex pt-2 flex-wrap">
    <?php if ($invitedCount){?>
        <div class="card me-3 mb-3 border-primary" style="width: 20rem">
            <div class="card-body" style="background-image: url('/img/cooperation.svg');background-size: cover">
                <?= sprintf(__('You Have %s Project Invitation'), $invitedCount)?>
            </div>
            <div class="card-footer p-0">
                <a href="/project" class="btn btn-light btn-sm"><i class="iconfont icon-detail"></i> <?= __('Detail')?></a>
            </div>
        </div>
    <?php
    }
    foreach ($recent_pages as $pageAndFunction){
        $page = $pageAndFunction['page'];
        $function = $pageAndFunction['function'];
        $module = $page->get_module();
        $project = $page->get_project();
        $frontendFramework = $project->get_setting_value(Env::FRONTEND_FRAMEWORK);
        $backendFramework = $project->get_setting_value(Env::FRAMEWORK);
    ?>
    <div class="card me-3 mb-3" style="width: 20rem">
        <a href="/project/<?= $project->uuid?>" class=" text-decoration-none"><div class="card-header text-truncate"><?= $project->name. '/' . $module->name . '/' . $function->name?></div></a>
        <div class="card-body overflow-hidden p-0 bgtransparent">
            <div class="page-preview" style="background-image: url(<?= $page->screen ? UPLOAD_SITE_URI.$page->screen : '/img/transparent.svg'?>)"></div>
        </div>
        <div class="card-footer p-0">
            <a href="/preview/<?= $project->uuid?>?module=<?= $module->uuid?>" class="btn btn-light btn-sm"><i class="iconfont icon-run"></i> <?= __('Preview')?></a>
            <button type="button" data-url="<?= Project_Model::get_ui_builder_url()?>" data-uuid="<?= $page->uuid?>" class="run-ui-builder btn btn-light btn-sm"><i class="iconfont icon-ui"></i> <?= __('Build UI')?></button>
            <?php
                if ($frontendFramework) echo "<small class='badge bg-success me-1'>{$env[$frontendFramework]['name']}</small>";
                if ($backendFramework) echo "<small class='badge bg-secondary'>{$backendFramework}</small>";
            ?>
        </div>
    </div>
    <?php }?>
    <div class="card me-3 mb-3 border" style="width: 20rem">
        <div class="card-body d-flex justify-content-center align-items-center">
            <button data-url="/project/add" type="button" data-title="<?= __('Add Project')?>" class="btn btn-primary yd-dialog"><?= __('Add Project')?></button>
        </div>
    </div>
</div>
