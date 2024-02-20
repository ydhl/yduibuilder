<?php
namespace app\user;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$user = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
$sidebar = $this->get_data('sidebar');
?>
<div class="d-flex">
    <div class="flex-grow-0 flex-shrink-0 p-3">
        <div class="list-group">
            <a href="/profile" class="list-group-item list-group-item-action <?=$sidebar=='profile' ? 'active': ''?>" <?=$sidebar=='profile' ? 'aria-current="true"': ''?>>
                <?= __('Profile')?>
            </a>
            <a href="/profile/signinpwd" class="list-group-item list-group-item-action <?=$sidebar=='password' ? 'active': ''?>" <?=$sidebar=='password' ? 'aria-current="true"': ''?>>
                <?= __('Sign In Password')?>
            </a>
            <a href="/profile/email" class="list-group-item list-group-item-action <?=$sidebar=='email' ? 'active': ''?>" <?=$sidebar=='email' ? 'aria-current="true"': ''?>>
                <?= __('Email')?>
            </a>
            <a href="/profile/cellphone" class="list-group-item list-group-item-action <?=$sidebar=='cellphone' ? 'active': ''?>" <?=$sidebar=='cellphone' ? 'aria-current="true"': ''?>>
                <?= __('Cellphone')?>
            </a>
        </div>
    </div>
    <div class="flex-grow-1 ms-3">
        <?php echo $this->content_of_view();?>
    </div>
</div>
