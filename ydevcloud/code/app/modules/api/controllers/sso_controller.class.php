<?php
namespace app\api;
use app\user\User_Model;
use app\vendor\Jwt;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use \yangzie\YZE_JSON_View;
use function yangzie\yze_merge_query_string;

/**
*
* @version $Id$
* @package api
*/
class Sso_Controller extends YZE_Resource_Controller {
    public function response_headers()
    {
        return [
            "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
            "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
            "Access-Control-Allow-Origin: *"
        ];
    }
    /**
     * @actionname 通过跨站登录临时token换取jwt token
     * @return YZE_JSON_View
     */
    public function post_index () {
        $request = $this->request;
        $this->layout = "";
        $token = $request->get_from_post('token');
        if (!$token) return YZE_JSON_View::error($this,"登录失败");
        // 根据get上的token，去找对应的用户，找到并且token没有过期，则返回用户登录成功后的token
        // 这里的token就是jwt，也就是后面所有请求都会带上的标识用户登录的令牌
        // 没有登录成功则返回YZE_JSON_View::error
        $user = User_Model::from()->where("is_deleted=0 AND sso_token=:sso_token")->get_Single([":sso_token"=>$token]);
        if (!$user) return YZE_JSON_View::error($this,"用户不存在");
        $sso_token_expire = $user->sso_token_expire;
        //删除临时token
        $user->set(User_Model::F_SSO_TOKEN,null);
        $user->set(User_Model::F_SSO_TOKEN_EXPIRE,null);
        $user->save();

        if (time() <= strtotime($sso_token_expire)){//当前时间小于临时token过期时间，成功返回
            $payload=array(
                'iss'=>'ydevcloud',
                'iat'=>time(),
                'nbf'=>time(),
                'exp'=> time() + 14400,
                'sub'=>$user->uuid,
                'jti'=>md5(uniqid('JWT').time()));
            $jwt_token = Jwt::getToken($payload);
            return YZE_JSON_View::success($this,['token'=>$jwt_token]);
        }else{//当前时间大于，临时token过期，返回error
            return YZE_JSON_View::error($this,"登录超时，请重新登录");
        }
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
