<?php
namespace app\project;
use app\common\Option_Model;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$project = $this->get_data("project");
$member  = $this->get_data("member");
$this->layout = '';
$regioins = getCellphoneRegionCode();
?>
<div class="container-fluid">
    <form method="post">
        <?php if (!$member){?>
        <div class="row mb-3">
            <label class="col-form-label"><?= __('Email Or Cellphone(+Region)')?></label>
            <div class="input-group">
                <input type="text" class="form-control" id="cellphone_email" name="cellphone_email" autocomplete="off">
                <select class="border" style="width: 80px" disabled id="region" name="region">
                    <option value=""><?= __('region:')?></option>
                    <?php foreach ($regioins as $item) {?>
                        <option value="<?= $item['prefix']?>"><?= '+'.$item['prefix'].' '.$item['en']?></option>
                    <?php }?>
                </select>
            </div>
        </div>
        <?php }?>
        <div class="row mb-3">
            <label class="col-form-label"><?= __('Role:')?></label>
            <div class="">
                <select class="form-control" name="role">
                    <?php foreach (Project_Member_Model::get_role() as $role){?>
                    <option value="<?= $role?>"><?= __($role)?></option>
                    <?php }?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <input type="hidden" name="uuid" value="<?= $member->uuid?>">
            <button type="submit" class="btn btn-primary yd-form-submit yd-spin-btn"
                    <?php if (!$member){?>
                    data-confirm-msg="<?= __('are you sure invite this user?')?>"
                    <?php }?>
                    data-redirect="/project/<?= $project->uuid?>/member"
                    data-url="/project/<?= $project->uuid?>/invite-member"><?= __("Invite")?></button>
        </div>
    </form>
</div>
<script>
    $(function () {
        $("#cellphone_email").keyup(function () {
            $("#region").attr('disabled', $("#cellphone_email").val().match(/@/))
        })
    })
</script>
