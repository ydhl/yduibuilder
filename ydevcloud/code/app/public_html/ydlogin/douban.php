<?php
include_once 'config.php';

class Douban extends YDOAuth{
    public function formatAccessToken($access_token){
        var_dump($access_token);die;
        $info = json_decode($access_token, true);
        if( @ $info['access_token']){
            return $info;
        }
        $this->error = $info['msg'];
        return array();
    }
	public function get_oAuthUser_Info($oAuthInfo){
	    $this->http = new OAuthHttpClient("Bearer ".$oAuthInfo['access_token']);
	    
		return $this->format_user_info(
		        json_decode($this->http->get('https://api.douban.com/v2/user/~me'), true)
        );
	}
	private function format_user_info($user){
	    var_dump($user);
	    die();
	    if( ! @$user['id']){
	        $this->error = $user['msg'];
	        return array();
	    }
	    $info = new YDLoginUser();
	    $info->avatar      = $user['avatar'];
	    $info->displayName = $user['name'];
	    $info->fromSite    = "douban";
	    $info->openid      = $user['id'];
	    $info->origData    = $user;
	    return $info;
	}
	
	public function getOauthAccessTokenURL(){
	    return "https://www.douban.com/service/auth2/token";
	}
	public function getOauthAuthorizeURL(){
	    return "https://www.douban.com/service/auth2/auth";
	}
	public function getOauthScope(){
	    return "douban_basic_common";
	}
}

$douban = new Douban(YDLOGIN_DOUBAN_APPKEY, YDLOGIN_DOUBAN_SECRET);
$douban->doLogin("http://".rtrim($_SERVER['SERVER_NAME'],"/").$_SERVER['PHP_SELF']);
?>