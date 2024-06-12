<?php
/**
 * @author leeboo@ydhl
 */

$cwd = dirname ( __FILE__ );

include_once "$cwd/user.class.php";
include_once "$cwd/YDOAuth.php";
include_once "$cwd/OAuthHttpClient.class.php";
include_once "$cwd/OAuthUtil.class.php";
include_once "$cwd/ydhooks.php";


/**
 * hook实现文件包含路径
 */
define("YDLOGIN_HOOK_DIR", dirname(__FILE__)."/hook");
/**
 * 网站域名
 * @var unknown
 */
define("YDLOGIN_SITE_URL",          "");

//豆瓣appkey http://developers.douban.com/
//回调地址填写你域名下的douban.php
//API权限选择豆瓣公共
define("YDLOGIN_DOUBAN_APPKEY",     "");
define("YDLOGIN_DOUBAN_SECRET",     "");

//微信appkey http://open.weixin.qq.com/
//回调域名填写你的域名
define("YDLOGIN_WEIXIN_APPKEY",     "");
define("YDLOGIN_WEIXIN_SECRET",     "");

//新浪微博appkey http://open.weibo.com/
//回调域名填写你的域名
define("YDLOGIN_SINA_APPKEY",     "");
define("YDLOGIN_SINA_SECRET",     "");


//qqappkey http://connect.qq.com/
//回调域名填写你域名下的qq.php
define("YDLOGIN_QQ_APPKEY",     "");//qq app id
define("YDLOGIN_QQ_SECRET",     "");//qq app key


define("YDLOGIN_YDHL_APPKEY",     "YDECloud");
define("YDLOGIN_YDHL_SECRET",     "jfdklfjdfjw5322sxcvbt32wqsxafda433truhfdsd");

if(YDLOGIN_HOOK_DIR){
    YDHook::include_files(YDLOGIN_HOOK_DIR);
}

session_start();
