<?php
include_once 'config.php';

class Ydhl extends YDOAuth{
    private $url = "http://ydoa.local.com/oauth2";
    public function __construct($appkey, $appsecret) {
        parent::__construct($appkey, $appsecret);
    }
    public function formatAccessToken($access_token){
        $arr = json_decode($access_token,true);
        if( @$arr['error']){
            $this->error = @$arr['error']." ".@$arr['error_description'];
            return array();
        }
        return $arr;
    }
	public function get_oAuthUser_Info($oAuthInfo){
        //var_dump( trim($this->http->get($this->getUserInfoURL(), array("access_token"=>$oAuthInfo['access_token']))) );
		return $this->format_user_info(
		        json_decode( trim($this->http->get($this->getUserInfoURL(), array("access_token"=>$oAuthInfo['access_token']))), true)
        );
	}
	private function format_user_info($user){
	    if( @ ! $user['success']){
	        $this->error = $user['msg'];
	        return array();
	    }
	    $info = new YDLoginUser();
	    $info->avatar      = $user['data']['head_url'];
	    $info->displayName = $user['data']["nickname"];
	    $info->fromSite    = "oa.yidianhulian.com";
	    $info->openid      = $user['data']["openid"];
	    $info->origData    = $user;
	    return $info;
	}
	public function getUserInfoURL(){
	    return rtrim($this->url,"/")."/get_user_info";
	}
	public function getOauthAccessTokenURL(){
	    return rtrim($this->url,"/")."/token";
	}
	public function getOauthAuthorizeURL(){
	    return rtrim($this->url,"/")."/authorize";
	}
	public function getOauthScope(){
	    return "get_user_info";
	}
}

$client = new Ydhl( YDLOGIN_YDHL_APPKEY, YDLOGIN_YDHL_SECRET);
$client->doLogin("http://".rtrim($_SERVER['SERVER_NAME'],"/").$_SERVER['PHP_SELF']);
?>