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
    <h3><?= __('Sign In Password')?></h3>
    <div class="text-muted mb-3"><?= __("If you want sign in by password, you need set sign in password and email/cellphone")?></div>
    <?php if ($user->login_pwd){?>
        <div class="mb-3">
            <label for="old_password" class="form-label"><?= __('Old Password')?></label>
            <input type="password" autocomplete="off" class="form-control" id="old_password">
        </div>
    <?php }?>

    <div class="mb-3">
        <label for="new_password" class="form-label"><?= __('New Password')?></label>
        <input type="password" autocomplete="off" class="form-control" id="new_password">
        <small class="text-muted"><?= __('at least 8 characters')?></small>
    </div>
    <div class="mb-3">
        <label for="new_password1" class="form-label"><?= __('Confirm New Password')?></label>
        <input type="password" autocomplete="off" class="form-control" id="new_password1">
    </div>
    <button type="button" class="btn btn-primary yd-spin-btn" id="btn-singinpwd"><?= __('Save')?></button>
</div>