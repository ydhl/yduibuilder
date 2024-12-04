<?php
namespace app\api;
use app\user\User_Model;
use app\vendor\Jwt;
use yangzie\YZE_FatalException;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use \yangzie\YZE_JSON_View;
use function yangzie\__;
use function yangzie\yze_merge_query_string;

/**
*
* @version $Id$
* @package api
*/
class Token_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    /**
     * @actionname 刷新jwt token
     * @return YZE_JSON_View
     */
    public function post_index () {
        $request = $this->request;
        $this->layout = "";

        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        if (!$loginUser) return YZE_JSON_View::error($this, "");

        $payload=array(
            'iss'=>'ydevcloud',
            'iat'=>time(),
            'nbf'=>time(),
            'exp'=> time() + 14400,
            'sub'=>$loginUser->uuid,
            'jti'=>md5(uniqid('JWT').time()));
        $jwt_token = Jwt::getToken($payload);
        return YZE_JSON_View::success($this,['token'=>$jwt_token]);
    }

    /**
     * @actionname 生成跨站请求token
     * @return YZE_JSON_View
     */
    public function post_token () {
        $request = $this->request;
        $this->layout = "";
        // 获取当前的登录用户，给当前的登录用户生成一个sso_token, 设置sso_token_expire 为一分钟后过期， token要唯一，生成方式md5(用户uuid-当前时间戳)
        // 这里的token是临时登录的令牌
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $token = md5($loginUser->uuid."-".strtotime(date("Y-m-d H:i:s",time())));
        $loginUser->set(User_Model::F_SSO_TOKEN,$token);
        $loginUser->set(User_Model::F_SSO_TOKEN_EXPIRE,date("Y-m-d H:i:s",strtotime("+60 seconds")));
        $loginUser->save();

        $user_info = [
            "name"=>$loginUser->name
        ];
        return YZE_JSON_View::success($this,['token'=>$token, 'user'=>$user_info]);
    }

    /**
     *
     * 生成跨站请求token, 并重定向到指定的url，请求对query参数也一并传过去
     * 和post_token的区别是，get方法会直接重定向
     *
     * @return YZE_JSON_View
     */
    public function token () {
        $request = $this->request;
        $this->layout = "";
        // 获取当前的登录用户，给当前的登录用户生成一个sso_token, 设置sso_token_expire 为一分钟后过期， token要唯一，生成方式md5(用户uuid-当前时间戳)
        // 这里的token是临时登录的令牌
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $token = md5($loginUser->uuid."-".strtotime(date("Y-m-d H:i:s",time())));
        $loginUser->set(User_Model::F_SSO_TOKEN,$token);
        $loginUser->set(User_Model::F_SSO_TOKEN_EXPIRE,date("Y-m-d H:i:s",strtotime("+60 seconds")));
        $loginUser->save();
        $url = trim($request->get_from_get("url"));
        $args = $_GET;
        $args['token'] = $token;
        unset($args[array_search('url', $args)]);
        return new YZE_Redirect($url.'?'.http_build_query($args), $this);
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = 'error';
        //Post 请求或者返回json接口时，出错返回json错误结果
        $format = $request->get_output_format();
        if (!$request->is_get() || strcasecmp ( $format, "json" )==0){
        	$this->layout = '';
        	return YZE_JSON_View::error($this, $e->getMessage());
        }
    }
}
?>
