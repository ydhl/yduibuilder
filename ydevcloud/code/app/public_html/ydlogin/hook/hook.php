<?php

use app\user\User_Model;
use yangzie\YZE_Hook;
$old_cwd = getcwd();
chdir( realpath(dirname(__FILE__)."/../../") );
include_once 'init.php';
chdir($old_cwd);

//默认的处理
YDHook::add_hook(YDHook::HOOK_LOGIN_SUCCESS, function (YDLoginUser $info) {
    //判断是否已经验证,是则登录,否则要求用户手机验证
    $user = User_Model::findUserOfOpenid($info->fromSite, $info->openid);
    if( $user ){
        YZE_Hook::do_hook(YZE_HOOK_SET_LOGIN_USER, $user);
        ob_clean(); //清空缓存区，避免header不能输出
        header("Location:/dashboard");
        die();
    }

    $_SESSION['oauth_user'] = $info;
    ob_clean(); //清空缓存区，避免header不能输出
    header("Location:/bind");
    die();

});
YDHook::add_hook(YDHook::HOOK_LOGIN_FAIL, function ($error) {
    ob_clean();
    header("Location: /?error={$error}");
    die();
});
