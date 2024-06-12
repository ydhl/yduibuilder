<?php
include_once 'config.php';

class QQ extends YDOAuth{
    public function formatAccessToken($access_token){
        $arr = array();
        parse_str($access_token, $arr);
        if( @$arr['msg']){
            $this->error = $arr['msg'];
            return array();
        }
        return $arr;
    }
	public function get_oAuthUser_Info($oAuthInfo){
	    preg_match("/(?P<json>{.+})/", 
	       $this->http->get('https://graph.qq.com/oauth2.0/me', array("access_token"=>$oAuthInfo['access_token'])),
	       $info
        );
        $openids = json_decode($info['json'], true);
	    if( ! @$openids['openid']){
	        $this->error = $openids['error_description'];
	        return array();
	    }
		return $this->format_user_info($openids['openid'],
		        json_decode($this->http->get('https://graph.qq.com/user/get_user_info', 
		                array("access_token"=>$oAuthInfo['access_token'],
		                        "oauth_consumer_key"=>YDLOGIN_QQ_APPKEY,"openid"=>$openids['openid'])), true)
        );
	}
	private function format_user_info($openid, $user){
	    if( @$user['ret']){
	        $this->error = $user['msg'];
	        return array();
	    }
	    $info = new YDLoginUser();
	    $info->avatar      = $user['figureurl_2'];
	    $info->displayName = $user["nickname"];
	    $info->fromSite    = "qq";
	    $info->openid      = $openid;
	    $info->origData    = $user;
	    return $info;
	}
	
	public function getOauthAccessTokenURL(){
	    return "https://graph.qq.com/oauth2.0/token";
	}
	public function getOauthAuthorizeURL(){
	    return "https://graph.qq.com/oauth2.0/authorize";
	}
	public function getOauthScope(){
	    return "get_user_info";
	}
}

$client = new QQ(YDLOGIN_QQ_APPKEY, YDLOGIN_QQ_SECRET);
$client->doLogin("http://".rtrim($_SERVER['SERVER_NAME'],"/").$_SERVER['PHP_SELF']);
?>