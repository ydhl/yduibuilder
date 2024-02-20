<?php
namespace app\project;
use app\vendor\Env;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$project = $this->get_data('project');
$menu = $this->get_data('menu');
$loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
$project_member = $project->get_member($loginUser->id);
$setting = $project->get_setting();
$packages = Env::package();
?>
<div class="d-flex align-items-center pb-2">
    <?php if ($setting['logo']){?>
        <img src="<?= UPLOAD_SITE_URI.$setting['logo']?>" class="rounded-circle me-2" style="width: 50px;height: 50px;object-fit: cover">
    <?php }?>
    <h4>
        <?= $project->name?><small class="text-muted ms-2 fs-8"><?= $project->desc?></small>
        <div class="fs-7 d-flex align-items-center">
            <div class="me-2">
                <small class="text-muted"><?= __('Type')?>:</small>
                <?= $project->end_kind?>
            </div>
            <?php if ($setting['frontend']){?>
                <div class="me-2">
                    <small class="text-muted"><?= __('Front')?>:</small>
                    <?= $setting['frontend']?>
                </div>
            <?php }?>
            <?php if ($setting['frontend_framework_version']){?>
                <div class="me-2">
                    <small class="text-muted"><?= __('Front Framework')?>:</small>
                    <?= $packages[$setting['frontend_framework']]['name']. ' ('. $setting['frontend_framework_version'].')'?>
                </div>
            <?php }?>
            <?php if ($setting['ui']){?>
                <div class="me-2">
                    <small class="text-muted"><?= __('UI')?>:</small>
                    <?= $packages[$setting['ui']]['name']. ($setting['ui_version'] ? ' ('. $setting['ui_version'].')' : '')?>
                </div>
            <?php }?>
            <?php if ($setting['frontend_language_version']){?>
                <div class="me-2">
                    <small class="text-muted"><?= __('Frontend Language')?>:</small>
                    <?= $setting['frontend_language']. ' ('. $setting['frontend_language_version'].')'?>
                </div>
            <?php }?>
            <?php if ($setting['backend']){?>
                <div class="me-2">
                    <small class="text-muted"><?= __('Backend Tech.')?>:</small>
                    <?= $setting['backend']?>
                </div>
            <?php }?>
            <?php if ($setting['framework_version']){?>
                <div class="me-2">
                    <small class="text-muted"><?= __('Framework')?>:</small>
                    <?= $setting['framework']. ' ('. $setting['framework_version'].')'?>
                </div>
            <?php }?>
            <?php if ($setting['backend_language_version']){?>
                <div class="me-2">
                    <small class="text-muted"><?= __('Backend Language')?>:</small>
                    <?= $setting['backend_language']. ' ('. $setting['backend_language_version'].')'?>
                </div>
            <?php }?>
            <div class="me-2">
                <small class="text-muted"><?= __('Role')?>:</small>
                <?php echo $project_member->get_role_desc();
                if ($project_member->is_creater){
                    echo ' <span class="badge bg-secondary">'.__('Creater').'</span>';
                }
                ?>
            </div>
        </div>
    </h4>
</div>
<div class="d-flex mt-3">
    <div style="width: 180px" class="border rounded bg-light flex-shrink-0 flex-grow-0">
        <div class="list-group list-group-flush rounded-top border-bottom">
            <a href="/project/<?= $project->uuid?>" class="list-group-item list-group-item-action <?= !$menu ? "active" : ""?>">
                <i class="iconfont icon-activity"></i>
                <?= __("Activity")?>
            </a>
            <div class="list-group-item p-1 bg-light"></div>
            <a href="/project/<?= $project->uuid?>/structure" class="list-group-item list-group-item-action <?= $menu == 'structure' ? "active" : ""?>">
                <i class="iconfont icon-module"></i>
                <?= __("UI")?>
            </a>

            <a href="/project/<?= $project->uuid?>/icon" class="list-group-item list-group-item-action <?= $menu == 'icon' ? "active" : ""?>">
                <i class="iconfont icon-image"></i>
                <?= __("Icon")?>
            </a>
            <a href="/project/<?= $project->uuid?>/lib" class="list-group-item list-group-item-action <?= $menu == 'lib' ? "active" : ""?>">
                <i class="iconfont icon-library"></i>
                <?= __("Libraries")?>
            </a>
            <a href="/project/<?= $project->uuid?>/uicomponent" class="list-group-item list-group-item-action <?= $menu == 'uicomponent' ? "active" : ""?>">
                <i class="iconfont icon-ui"></i>
                <?= __("UI Component")?>
            </a>
            <div class="list-group-item p-1 bg-light"></div>
            <a href="/project/<?= $project->uuid?>/member" class="list-group-item list-group-item-action <?= $menu == 'member' ? "active" : ""?>">
                <i class="iconfont icon-members"></i>
                <?= __("Member")?>
            </a>
            <?php if ($project_member->role == Project_Member_Model::ROLE_ADMIN || $project_member->is_creater){?>
            <a href="/project/<?= $project->uuid?>/setting" class="list-group-item border-bottom list-group-item-action <?= $menu == 'setting' ? "active" : ""?>">
                <i class="iconfont icon-setting"></i>
                <?= __("Setting")?>
            </a>
            <?php }?>
            <div class="list-group-item p-1 bg-light"></div>
            <a href="/project/<?= $project->uuid?>/asset" class="list-group-item list-group-item-action <?= $menu == 'asset' ? "active" : ""?>">
                <i class="iconfont icon-folder"></i>
                <?= __("Asset")?>
            </a>
            <div class="list-group-item p-1 bg-light"></div>
            <a href="/project/<?= $project->uuid?>/build" class="list-group-item list-group-item-action <?= $menu == 'build' ? "active" : ""?>">
                <i class="iconfont icon-download"></i>
                <?= __("Build")?>
            </a>
        </div>
    </div>
    <div class="ps-3 flex-fill">
        <?php echo $this->content_of_view()?>
    </div>
</div>
