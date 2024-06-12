<?php

/**
 * 定义一些系统回调，需要定义的回调有：
 * YZE_HOOK_GET_USER_ARO_NAME: 返回用户的aro，默认为/
 * YZE_HOOK_YZE_EXCEPTION: 扬子鳄处理过程中出现的异常回调
 *
 * @author leeboo
 *
 */

namespace app;

use yangzie\YZE_Redirect;
use \yangzie\YZE_Request;
use \yangzie\YZE_Hook;
use \yangzie\YZE_Need_Signin_Exception;

YZE_Hook::add_hook(YZE_HOOK_AUTO_LOAD_CLASS, function ( $class ) {
    //echo $class;
});
?>
