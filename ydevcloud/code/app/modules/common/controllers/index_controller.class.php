<?php
namespace app\common;
use app\project\Project_Model;
use app\user\User_Model;
use app\vendor\Smtp;
use app\vendor\ydsms\Ydsms;
use TencentCloud\Scf\V20180416\Models\Code;
use yangzie\YZE_DBAImpl;
use yangzie\YZE_FatalException;
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
class Index_Controller extends YZE_Resource_Controller {
    /**
     * @ignoreLog
     */
    public function index(){
        $request = $this->request;
        $loginUser =YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        if ($loginUser) return new YZE_Redirect('/dashboard', $this);
        //$this->layout = 'tpl name';
        $this->set_view_data('yze_page_title', __("Please Sign In"));
    }
    /**
     * @ignoreLog
     */
    public function signin(){
        $request = $this->request;
        $this->set_view_data('yze_page_title', __("Please Sign In"));
    }
    /**
     * @ignoreLog
     */
    public function post_signin(){
        $request = $this->request;
        $this->layout = '';
        $target = trim($request->get_from_post('email'));
        $code  = trim($request->get_from_post('code'));
        $region  = trim($request->get_from_post('region'));
        $password  = trim($request->get_from_post('pwd'));
        $cellphone = ''; $email='';
        $where = 'is_deleted=0';
        if (!preg_match("/@/", $target)){
            $where .= " and cellphone=:phone and phone_region=:region";
            $cellphone = $target;
            $params[":phone"] = $target;
            $params[":region"] = $region;
            $target = "+".$region.$target;
        }else{
            $where .= " and email=:email";
            $email = $target;
            $params[":email"] = $target;
        }
        if ($password) {
            $where .= ' and login_pwd=:pwd';
            $params[":pwd"] = md5($password);
            $user = User_Model::from()->where($where)->get_Single($params);
            if (!$user) {
                return YZE_JSON_View::error($this, __('login failed, please check your password and email/cellphone, cellphone need select region'));
            }
            YZE_Hook::do_hook(YZE_HOOK_SET_LOGIN_USER, $user);
            return YZE_JSON_View::success($this);
        }
        $codeModel = Code_Model::from()
            ->where('code=:code and target=:target and current_timestamp() <= expirein')->get_Single([':code'=>$code, ':target'=>$target]);
        if (!$codeModel) return YZE_JSON_View::error($this, __('Invalid Code, Please reSend again'));
        $user = User_Model::from()->where($where)->get_Single($params);
        if (!$user){
            throw new YZE_FatalException(__('New user registration is not supported for the time being'));
            $user = new User_Model();
            $user->set("is_deleted",   0)
                ->set("uuid",       User_Model::uuid())
                ->set("cellphone",  $cellphone)
                ->set("email",  $email)
                ->set("phone_region",  $region)
                ->save();

            // 新用户创建一个默认的项目
            Project_Model::createDemoProject($user->id);
        }
        YZE_Hook::do_hook(YZE_HOOK_SET_LOGIN_USER, $user);
        return YZE_JSON_View::success($this, $code);
    }
    /**
     * @ignoreLog
     */
    public function post_sendcode(){
        $request = $this->request;
        $this->layout = '';
        $email = trim($request->get_from_post('email'));
        $region  = trim($request->get_from_post('region'));
        $check  = trim($request->get_from_post('check'));// 已登陆用户改变邮箱或手机号时使用
        if (!$email) return YZE_JSON_View::error($this, __('Please Input Email Or Cellphone'));
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);

        if (preg_match("/@/", $email)){
            if ($check){
                if ($email == $loginUser->email) return YZE_JSON_View::error($this, __('please input new email'));
                $checkUser =  User_Model::from()
                    ->where('email=:email and is_deleted=0')
                    ->get_Single([':email'=>$email]);
                if ($checkUser && $checkUser->id != $loginUser->id) return YZE_JSON_View::error($this, __($email.' has been used'));
            }

            $code = $this->send_email_code($email);
        }else{
            if ($check){
                if ($email == $loginUser->cellphone) return YZE_JSON_View::error($this, __('please input new cellphone'));
                $checkUser = User_Model::from()
                    ->where('cellphone=:cellphone and phone_region=:region and is_deleted=0')
                    ->get_Single([':cellphone'=>$email,':region'=>$region]);
                if ($checkUser && $checkUser->id != $loginUser->id) return YZE_JSON_View::error($this, __($email.' has been used'));
            }

            $code = $this->send_cellphone_code($email, $region);
            $email = '+'.$region.$email;
        }
        YZE_DBAImpl::get_instance()->exec('delete from code where current_timestamp() > expirein');
        $codeModel = new Code_Model();
        $codeModel->set(Code_Model::F_CODE, $code)
            ->set(Code_Model::F_CREATED_ON, date("Y-m-d H:i:s"))
            ->set(Code_Model::F_EXPIREIN, date("Y-m-d H:i:s", strtotime('+5 min')))
            ->set(Code_Model::F_TARGET, $email)
            ->set(Code_Model::F_UUID, Code_Model::uuid())
            ->save();
        return YZE_JSON_View::success($this);
    }
    private function send_email_code($email) {
        $code = rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
        $mailto=$email;
        $mailsubject = __("YDECloud Sign In Code");
        $mailbody = sprintf(__('Your current sign in code is: %s, expire in 5 minutes'),$code);
        $smtpserver     = SMTP_SERVER;
        $smtpserverport = SMTP_PORT;
        $smtpusermail   = SMTP_FROM_EMAiL;
        $smtpuser       = SMTP_USER;
        $smtppass       = SMTP_PASSWORD;
        $mailsubject    = "=?UTF-8?B?" . base64_encode($mailsubject) . "?=";
        $mailtype       = "HTML";

        $smtp           = new Smtp($smtpserver, $smtpserverport, true, $smtpuser, $smtppass);
        $smtp->debug    = false;
//        $smtp->log_file = YZE_APP_PATH.'smtp-log.txt';
        $sender  = SMTP_SENDER;
        $rst = $smtp->sendmail($mailto,$smtpusermail, $mailsubject, $mailbody, $mailtype, '', '', '', $sender, '');
        if (!$rst) {
            throw new YZE_FatalException(__('Send Email Fail, Please Check Your Email'));
        }
        return $code;
    }
    private function send_cellphone_code($cellphone, $region) {
        $ydsms = new Ydsms();
        $code = rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
        if(!$ydsms->sendSms('+'.$region.$cellphone, SMS_SIGN_CODE_TEMPLATE_ID, [$code, 5])){
            throw new YZE_FatalException(__('Can not send sms, check your cellphone and region'));
        }
        return $code;
    }
    /**
     * @actionname 注销登录
     * @return YZE_Redirect
     */
    public function signout(){
        session_destroy();
        return new YZE_Redirect('/', $this);
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
