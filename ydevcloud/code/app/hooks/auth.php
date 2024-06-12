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

use app\user\User_Model;
use app\vendor\Jwt;
use yangzie\YZE_FatalException;
use yangzie\YZE_Redirect;

use \yangzie\YZE_Request;
use \yangzie\YZE_Hook;
use \yangzie\YZE_Need_Signin_Exception;
use app\sp\Service_Provider_Model;
use function yangzie\__;

YZE_Hook::add_hook(YZE_HOOK_GET_LOCALE, function () {
    if (@$_GET['lang']){
        $_SESSION['lang'] = $_GET['lang'];
        setcookie('lang', $_GET['lang'], 0, '/');
    }
    return $_SESSION['lang'] ?: strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0 , strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], ',')) ?: '');
});

YZE_Hook::add_hook ( YZE_HOOK_GET_LOGIN_USER, function  ( $datas ) {
//    $token = $_SERVER['HTTP_TOKEN'];
//    if ($token){ // 设计器jwt登录
//        $getPayload = Jwt::verifyToken($token);
//        if (!$getPayload) throw new YZE_FatalException(__("Please Signin"),1001);
//        $loginUser = find_by_uuid(User_Model::CLASS_NAME,$getPayload['sub']);
//        if (!$loginUser) throw new YZE_FatalException(__("Please Signin"),1001);
//        return $loginUser;
//    }

	$loginUser = User_Model::find_by_id(1);
	if( ! $loginUser)return null;

	return $loginUser;
} );

YZE_Hook::add_hook ( YZE_HOOK_SET_LOGIN_USER, function  ( $data ) {
	$_SESSION [ 'admin' ] = $data;
} );

YZE_Hook::add_hook ( YZE_HOOK_GET_USER_ARO_NAME, function  ( $data ) {
    $token = $_SERVER['HTTP_TOKEN'];
    if ($token){
        $getPayload = Jwt::verifyToken($token);
        if (!$getPayload) return "";
//        $loginUser = find_by_uuid(User_Model::CLASS_NAME,$getPayload['sub']);
        return "/";
    }

	if ( !@$_SESSION [ 'admin' ] )return "/";
	return "TODO your ARO NAME";
} );


YZE_Hook::add_hook(YZE_HOOK_YZE_EXCEPTION, function ($datas){
    //如果array("exception"=>$e, "controller"=>$controller, "response"=>$response)
    // 把signin替换成自己的登录url

    $request = YZE_Request::get_instance();
    if(! is_a($datas['exception'], "\\yangzie\\YZE_Need_Signin_Exception")) return $datas;

    $datas['response'] = new YZE_Redirect("/", $datas['controller']);
    return $datas;
});
?>
