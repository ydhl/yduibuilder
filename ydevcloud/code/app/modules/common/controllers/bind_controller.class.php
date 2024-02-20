<?php
namespace app\common;
use app\project\Project_Model;
use app\user\User_Model;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use \yangzie\YZE_JSON_View;
use function yangzie\__;

/**
*
* @version $Id$
* @package common
*/
class Bind_Controller extends YZE_Resource_Controller {
    /**
     * @actionname 访问了绑定界面
     * @return YZE_Redirect
     */
    public function index(){
        $request = $this->request;
        $this->set_view_data('yze_page_title', '绑定');
        if( ! $_SESSION["oauth_user"]){
            return new YZE_Redirect("/", $this);
        }
    }

    /**
     * @actionname 发送了短信
     * @return YZE_JSON_View
     */
    public function post_sms(){
        $request = $this->request;
        $this->layout = '';
        $cellphone = trim($request->get_from_post("cellphone"));
        $region = trim($request->get_from_post("region"));
        $cellphone = '+'.$region.$cellphone;

        $_SESSION["auth_cellphone"] = $cellphone;
        $_SESSION["expire"] = strtotime("+ 5 min");
        $_SESSION["sms"] = 123456;
        return YZE_JSON_View::success($this);
    }

    /**
     * @actionname 提交了绑定信息
     * @return YZE_JSON_View
     */
    public function post_index(){
        $request = $this->request;
        $this->layout = '';
        $cellphone = trim($request->get_from_post("cellphone"));
        $region = trim($request->get_from_post("region"));
        $sms = $request->get_from_post("sms");
        $auth_cellphone = $_SESSION["auth_cellphone"];
        $sended_sms = $_SESSION["sms"];
        if( $auth_cellphone != $region.$cellphone){
            return YZE_JSON_View::error($this, __("Cellphone is not correct"));
        }
        if( $sms != $sended_sms){
            return YZE_JSON_View::error($this, __("Code is not correct"));
        }
        $user = User_Model::from()
            ->where("cellphone=:phone and phone_region=:region")
            ->get_Single(array(":phone"=>$cellphone, ':region'=>$region));
        $newUser = false;
        if (!$user){
            $newUser = true;
        }

        $auth_user = $_SESSION['oauth_user'];
        $user = $user ?: new User_Model();
        $user->set("openid",    $auth_user->openid)
            ->set("fromsite",   $auth_user->fromSite)
            ->set("avatar",     $auth_user->avatar)
            ->set("nickname",   $auth_user->displayName)
            ->set("is_deleted",   0)
            ->set("uuid",       User_Model::uuid())
            ->set("cellphone",  $cellphone)
            ->set("phone_region",  $region)
            ->save();

        // 新用户创建一个默认的项目
        if ($newUser){
            Project_Model::createDemoProject($user->id);
        }

        YZE_Hook::do_hook(YZE_HOOK_SET_LOGIN_USER, $user);
        unset($_SESSION['oauth_user'], $_SESSION['auth_cellphone'], $_SESSION['sms'], $_SESSION['expire']);
        return YZE_JSON_View::success($this);
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
