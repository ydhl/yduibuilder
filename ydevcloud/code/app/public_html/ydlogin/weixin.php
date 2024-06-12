<?php
include_once 'config.php';

class Weixin extends YDOAuth{
    public function get_Authorize_URL($response_type, $scope=null, $state=null, $display=null){
        $params = array(
                'appid' 	=> $this->appkey,
                'response_type' => $response_type,
                'redirect_uri' 	=> $this->redirect_uri,
        );
        if(!empty($scope))	$params['scope'] = $scope;
        if(!empty($state))	$params['state'] = $state;
        if(!empty($display))	$params['display'] = $display;
        $query = OAuthUtil::build_http_query($params);
        $authorizeURL = $this->getOauthAuthorizeURL();
        return strrpos($authorizeURL, "?")!==FALSE ? $authorizeURL."&{$query}"  : $authorizeURL."?{$query}";
    }
    
    public function get_Access_Token_From_Code($code){
        $params = array(
                'grant_type' 	=> "authorization_code",
                'code' 			=> $code,
                'appid' 		=> $this->appkey,
                'secret' 	=> $this->appsecret,
        );
        return $this->formatAccessToken($this->http->post($this->getOauthAccessTokenURL(),$params));
    }
    
    public function get_Access_Token_From_Refresh_Token($refresh_token){
        $params = array(
                'grant_type' 	=> "refresh_token",
                'appid' 		=> $this->appkey,
                'secret' 	=> $this->appsecret,
                'refresh_token' 	=> $refresh_token,
        );
        return $this->http->post($this->getOauthAccessTokenURL(),$params);
    }
	public function get_oAuthUser_Info($oAuthInfo){
		return $this->format_user_info(
		        json_decode($this->http->get("https://api.weixin.qq.com/sns/userinfo?access_token="
		                .$oAuthInfo['access_token']."&openid=".$oAuthInfo['openid']."&lang=zh_CN"), true)
        );
	}
	private function format_user_info($user){
	    if( ! @$user['openid']){
	        $this->error = @$user['errmsg'];
	        return array();
	    }
	    $info = new YDLoginUser();
	    $info->openid = $user['openid'];
	    $info->avatar = $user['headimgurl'];
	    $info->displayName = $user['nickname'];
	    $info->fromSite = "weixin";
	    $info->origData = $user;
	    return $info;
	}
	public function formatAccessToken($access_token){
	    $resp = json_decode($access_token, true);
	    if(@$resp['access_token']){
	        return $resp;
	    }
	    $this->error = @$resp['errmsg'] ? $resp['errmsg'] : "无法获取accesstoken：$access_token";
	    return array();
	}
	
	public function getOauthAccessTokenURL(){
	    return "https://api.weixin.qq.com/sns/oauth2/access_token";
	}
	public function getOauthAuthorizeURL(){
	    return "https://open.weixin.qq.com/connect/qrconnect";
	}
	public function getOauthScope(){
	    return "snsapi_login";
	}
}

$weixin = new Weixin(YDLOGIN_WEIXIN_APPKEY, YDLOGIN_WEIXIN_SECRET);
$weixin->doLogin("http://".rtrim($_SERVER['SERVER_NAME'],"/").$_SERVER['PHP_SELF']);
?>