<?php
abstract class YDOAuth{
    protected $appkey;
    protected $appsecret;
    protected $http;
    protected $error;
    protected $redirect_uri 	= "";
    
    public function setResponseFormat($format){
        $this->http->format = $format;
    }
    public function get_Authorize_URL($response_type, $scope=null, $state=null, $display=null){
        $params = array(
                'client_id' 	=> $this->appkey,
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
                'client_id' 		=> $this->appkey,
                'client_secret' 	=> $this->appsecret,
                'redirect_uri' 	=> $this->redirect_uri
        );
        return $this->formatAccessToken( $this->http->post($this->getOauthAccessTokenURL(),$params) );
    }
    
    public function get_Access_Token_From_Refresh_Token($refresh_token){
        $params = array(
                'grant_type' 	=> "refresh_token",
                'client_id' 		=> $this->appkey,
                'client_secret' 	=> $this->appsecret,
                'refresh_token' 	=> $refresh_token,
        );
        return $this->http->post($this->getOauthAccessTokenURL(),$params);
    }
    
    public function __construct($appkey, $appsecret){
        $this->appkey 		= $appkey;
        $this->appsecret 	= $appsecret;
        $this->http			= new OAuthHttpClient();
    }
    
	/**
	 * 取得用户信息
	 * 
	 * @param  $args
	 * 
	 * @return stdClass
	 */
	public abstract function get_oAuthUser_Info($oAuthInfo);
	public abstract function getOauthAccessTokenURL();
	public abstract function getOauthAuthorizeURL();
	public abstract function getOauthScope();
	public abstract function formatAccessToken($access_token);
	
	
	public function doLogin($backurl){
	    $this->redirect_uri = $backurl;
	    
	    $auth_code 	= @$_GET["code"];
        $state    	= @$_GET["state"];
        if(@$_GET['error']){
            $error     	= $_GET['error']." ".$_GET['error_description'];
        }
	    $redirect_uri= @$_GET["redirect_uri"];
	    if($redirect_uri){
	        $_SESSION['redirect_uri'] = urldecode($redirect_uri);
	    }
	    if(@$error){
	        $this->handleError($error);
	        die;
	    }
	    
	    //第一步申请code
	    if( ! $auth_code){
	        ob_clean();
	        header("Location: ".$this->get_Authorize_URL("code", $this->getOauthScope(), $state));
	        die;
	    }
	    
	    //第二步申请access token
	    $resp = $this->get_Access_Token_From_Code($auth_code);
	    if($resp){
	        $user = $this->get_oAuthUser_Info($resp);
	        if( ! $user){
                $this->handleError($this->error);
	        }else{
	            $this->handleSuccess($user);
	        }
	    }else{
	       $this->handleError( $this->error);
	    }
	}
	private function handleSuccess($user){
	    $redirect_uri = @$_SESSION['redirect_uri'];
	    if($redirect_uri){
	        ob_clean();
	        unset($_SESSION['redirect_uri']);
	        $user->origData = null;
	        header("Location: {$redirect_uri}?user=".urlencode(json_encode($user)));
	        die;
	    }
	    YDHook::do_hook(YDHook::HOOK_LOGIN_SUCCESS, $user);
	}
	private function handleError($error){
	    $redirect_uri = @$_SESSION['redirect_uri'];
        if($redirect_uri){
            ob_clean();
            unset($_SESSION['redirect_uri']);
            header("Location: {$redirect_uri}?error=".urlencode($error));
            die;
        }
	    YDHook::do_hook(YDHook::HOOK_LOGIN_FAIL, $error);
	}
}
?>