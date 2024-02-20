<?php
namespace app\user;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$user = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
$this->master_view = 'master/profile-master'
?>
<div class="w-50">
    <h3 class="mb-3"><?= __('Profile')?></h3>
    <div class="mb-3">
        <label for="nickname" class="form-label"><?= __('nickname')?></label>
        <input type="text" autocomplete="off" class="form-control" id="nickname" value="<?= $user->nickname?>">
    </div>

    <div class="mb-3">
        <label for="avatar" class="form-label"><?= __('avatar')?></label>
        <input type="hidden" id="avatar" value="">
        <div>
            <div class="yd-upload btn btn-secondary" data-uploaded-callback="avatar_callback" data-upload-mime="image/*" data-url="/common/upload">
                <?= __('Upload')?>
            </div>
        </div>
        <img id="avatar-img" src='<?= $user->avatar?>' class="mt-1 <?= $user->avatar ? '' : 'd-none'?>" style='width: 50px'>
    </div>

    <button type="button" class="btn btn-primary yd-spin-btn" id="save-profile"><?= __('Save')?></button>
</div>