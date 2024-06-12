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

$api_env_config = Project_Setting_Model::get_setting_value($project->id, 'api_env') ?: [];
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
<form>
<div class="card mb-3">
    <div class="card-header"><?= __('API URL')?></div>
    <table class="table">
        <thead>
        <tr><th><?= __('API Environment')?></th><th><?= __('Base URL')?></th></tr>
        </thead>
        <tbody id="apiurl">
            <?php foreach ($api_env_config as $name=>$url){?>
            <tr>
                <td><input type="text" name="name[]" onchange="addapiurl()" value="<?= $name?>" class="form-control api-name" placeholder="<?= __('Left blank will be deleted')?>"></td>
                <td><input type="text" name="url[]" value="<?= $url?>" class="form-control"></td>
            </tr>
            <?php }?>
            <tr>
                <td><input type="text" name="name[]" onchange="addapiurl()"  value="" class="form-control api-name" placeholder="<?= __('Left blank will be deleted')?>"></td>
                <td><input type="text" name="url[]" value="" class="form-control"></td>
            </tr>
        </tbody>
    </table>
    <div class="card-body">
        <button class="btn btn-secondary btn-sm yd-form-submit"  type="button" data-url="/project/<?= $project->uuid?>/apiurl"><?= __('Change')?></button>
    </div>
</div>
</form>
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
<script type="text/html" id="apitpl">
    <tr>
        <td><input type="text" name="name[]" onchange="addapiurl()"  value="" class="form-control api-name" placeholder="<?= __('Left blank will be deleted')?>"></td>
        <td><input type="text" name="url[]" value="" class="form-control"></td>
    </tr>
</script>
<script>
    function addapiurl(event) {
        if($('#apiurl tr:last-child .api-name').val()){
            $('#apiurl').append($("#apitpl").html())
        }
    }
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
