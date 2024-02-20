<?php
namespace app\user;
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
* @package user
*/
class Profile_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->layout = "admin";
    }
    public function index(){
        $request = $this->request;
        $this->set_view_data('sidebar', 'profile');
        $this->set_view_data('yze_page_title', 'Profile');
    }
    public function post_index(){
        $request = $this->request;
        $this->layout = "";
        $nickname = trim($request->get_from_post('nickname'));
        $avatar   = trim($request->get_from_post('avatar'));
        $user = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $user->set(User_Model::F_NICKNAME, $nickname)->set(User_Model::F_AVATAR, $avatar)->save();
        YZE_Hook::do_hook(YZE_HOOK_SET_LOGIN_USER, $user);
        return YZE_JSON_View::success($this);
    }
    public function signinpwd(){
        $request = $this->request;
        $this->set_view_data('sidebar', 'password');
        $this->set_view_data('yze_page_title', 'Sign In Password');
    }
    public function post_signinpwd(){
        $request = $this->request;
        $this->layout = "";
        $old_password = trim($request->get_from_post('old_password'));
        $new_password = trim($request->get_from_post('new_password'));
        $new_password1= trim($request->get_from_post('new_password1'));
        $user = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        if ($user->login_pwd && $user->login_pwd != md5($old_password)){
            return YZE_JSON_View::error($this, __('Old Password Is Incorrect'));
        }
        if (!$new_password || strlen($new_password) < 8){
            return YZE_JSON_View::error($this, __('New password at least 8 characters'));
        }
        if ($new_password != $new_password1){
            return YZE_JSON_View::error($this, __('New Password Is Not Match'));
        }
        $user->set(User_Model::F_LOGIN_PWD, md5($new_password))->save();
        YZE_Hook::do_hook(YZE_HOOK_SET_LOGIN_USER, $user);
        return YZE_JSON_View::success($this);
    }
    public function cellphone(){
        $request = $this->request;
        $this->set_view_data('sidebar', 'cellphone');
        $this->set_view_data('yze_page_title', 'Cellphone');
    }
    public function post_cellphone(){
        $request = $this->request;
        $this->layout = "";
        $cellphone = trim($request->get_from_post('cellphone'));
        $region = trim($request->get_from_post('region'));
        $user = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $checkUser = User_Model::from()->where('cellphone=:cellphone and phone_region=:region and is_deleted=0')
            ->get_Single([':cellphone'=>$cellphone,':region'=>$region]);
        if ($checkUser) return YZE_JSON_View::error($this, __($cellphone.' has been used'));

        $user->set(User_Model::F_CELLPHONE, $cellphone)->save();
        YZE_Hook::do_hook(YZE_HOOK_SET_LOGIN_USER, $user);
        return YZE_JSON_View::success($this);
    }
    public function email(){
        $request = $this->request;
        $this->set_view_data('sidebar', 'email');
        $this->set_view_data('yze_page_title', 'Email');
    }

    public function post_email(){
        $request = $this->request;
        $this->layout = "";
        $email = trim($request->get_from_post('email'));
        $user = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $checkUser = User_Model::from()->where('email=:email and is_deleted=0')->get_Single([':email'=>$email]);
        if ($checkUser) return YZE_JSON_View::error($this, __($email.' has been used'));

        $user->set(User_Model::F_EMAIL, $email)->save();
        YZE_Hook::do_hook(YZE_HOOK_SET_LOGIN_USER, $user);
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
