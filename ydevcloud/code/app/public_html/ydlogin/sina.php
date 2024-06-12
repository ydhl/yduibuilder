<?php
include_once 'config.php';

class Sina extends YDOAuth{
    public function formatAccessToken($access_token){
        $info = json_decode($access_token, true);
        if( @ $info['access_token']){
            return $info;
        }
        $this->error = $info['error'];
        return array();
    }
	public function get_oAuthUser_Info($oAuthInfo){
	    $params = array(
	            'access_token' => $oAuthInfo['access_token'],
	            'uid' => $oAuthInfo['uid']
	    );
	    
		return $this->format_user_info(
		        $this->http->get("https://api.weibo.com/2/users/show.json",$params)
        );
	}
	private function format_user_info($user){
	    $user = json_decode($user, true);
	    if(@$user['error']){
	        $this->error = $user['error'];
	        return array();
	    }
	    
	    $info = new YDLoginUser();
	    $info->avatar      = $user['profile_image_url'];
	    $info->displayName = $user['screen_name'];
	    $info->fromSite    = "sina";
	    $info->openid      = $user['id'];
	    $info->origData    = $user;
	    return $info;
	}
	
	public function getOauthAccessTokenURL(){
	    return "https://api.weibo.com/oauth2/access_token";
	}
	public function getOauthAuthorizeURL(){
	    return "https://api.weibo.com/oauth2/authorize";
	}
	public function getOauthScope(){
	    return "";
	}
}

$client = new Sina(YDLOGIN_SINA_APPKEY, YDLOGIN_SINA_SECRET);
$client->doLogin("http://".rtrim($_SERVER['SERVER_NAME'],"/").$_SERVER['PHP_SELF']);
?>