<?php
namespace app\project;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$project = $this->get_data('project');
$menu = $this->get_data('menu');
$this->master_view = 'master/project';
$loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
$project_member = $project->get_member($loginUser->id);
?>
<h3 class="mb-3"><i class="iconfont icon-setting fs-2"></i> <?= __('Setting')?></h3>

<?php if ($project_member->role == Project_Member_Model::ROLE_ADMIN){?>
    <div class="card mb-3">
        <div class="card-header"><?= __('Edit Project')?></div>
        <div class="card-body"><p>
                <?= __("Change base project info, such as name, brief, logo and so on")?>
            </p>
            <button class="btn btn-secondary btn-sm yd-dialog"  data-title="<?= __("Edit Project")?>" data-url="/project/<?= $project->uuid?>/edit"><?= __('Edit')?></button>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><?= __('Change Tech.')?></div>
        <div class="card-body">
            <p>
                <?= __("Change Frontend, backend tech. such as framework, language, ui and so on")?>
            </p>
            <button class="btn btn-secondary btn-sm yd-dialog"  data-title="<?= __("Change Tech.")?>" type="button" data-url="/project/<?= $project->uuid?>/tech"><?= __('Change')?></button>
        </div>
    </div>
<?php }?>
<?php if ($project_member->is_creater){?>
    <div class="card mb-3">
        <div class="card-header"><?= __('Transfer Project')?></div>
        <div class="card-body">
            <p>
                <?= __("Transfer the project to others")?>
            </p>
            <button class="btn btn-secondary btn-sm yd-dialog" data-size="large"
                    data-primary-button-label="<?=__("Transfer")?>"
                    data-primary-button-click="transfer"
                    data-title="<?= __("Transfer the project to others")?>"
                    type="button"
                    data-url="/project/<?= $project->uuid?>/transfer"><?= __('Transfer')?></button>
        </div>
    </div>

    <div class="card text-danger">
        <div class="card-header"><?= __('Delete Project')?></div>
        <div class="card-body">
            <?= __('Deleting the project will delete all related resources including ui pages, databases etc.Deleted projects cannot be restored!')?>
            <hr/>
            <button type="button" class="btn btn-danger btn-sm yd-prompt"  data-dialog-id="<?= $project->uuid?>"
                    data-prompt-cb="delete_project_confirm" data-type="text"
                    data-title="<?= sprintf(__('Deleted projects cannot be restored.  please input the project name %s to delete'), "<code>{$project->name}</code>")?>"><?= __('Delete Project')?></button>
        </div>
    </div>

<script>
    function transfer(dialogid) {
        if (!transferToId){
            YDJS.hide_dialog(dialogid);
            return;
        }
        $.post('/project/<?= $project->uuid?>/transfer', { to: transferToId }, function(rst) {
            if (rst && rst.success){
                YDJS.hide_dialog(dialogid);
                YDJS.toast("<?= __("Transfer success")?>", YDJS.ICON_SUCCESS);
            }else{
                YDJS.toast(rst.msg, YDJS.ICON_ERROR);
            }
        },'json')
    }
</script>
<?php }?>
