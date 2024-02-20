<?php
namespace app\project;
use app\user\User_Model;
use app\vendor\Save_Model_Helper;
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
* @package project
*/
class Member_Controller extends YZE_Resource_Controller {
    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->set_View_Data('top_menu', 'project');
        $this->layout = "admin";
    }

    /**
     * @actionname 访问项目成员列表
     */
    public function index(){
        $request = $this->request;
        $pid = $request->get_var('pid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $this->set_View_Data('project', $project);
        $this->set_View_Data('menu', 'member');
        $this->set_view_data('yze_page_title', __('Member'));
    }
    /**
     * @actionname 打开邀请成员界面
     */
    public function invite(){
        $request = $this->request;
        $pid = $request->get_var('pid');
        $uuid = $request->get_from_get('uuid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $this->set_View_Data('project', $project);
        $this->set_View_Data('member', find_by_uuid(Project_Member_Model::CLASS_NAME, $uuid));
        $this->set_View_Data('menu', 'member');
        $this->set_view_data('yze_page_title', __('Member'));
    }
    /**
     * @actionname 成员同意加入
     */
    public function post_join()
    {
        $request = $this->request;
        $pid = $request->get_var('pid');
        $this->layout = '';
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        if (!$project){
            return YZE_JSON_View::error($this, __('Project Not Found'));
        }
        $member = $project->get_member($loginUser->id);
        if (!$member){
            return YZE_JSON_View::error($this, __('Project Not Found'));
        }
        $member->set('is_invited', 1)->save();
        return YZE_JSON_View::success($this);
    }
    /**
     * @actionname 成员不同意加入
     */
    public function post_disagree()
    {
        $request = $this->request;
        $pid = $request->get_var('pid');
        $this->layout = '';
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        if (!$project){
            return YZE_JSON_View::error($this, __('Project Not Found'));
        }
        $member = $project->get_member($loginUser->id);
        if (!$member){
            return YZE_JSON_View::error($this, __('Project Not Found'));
        }
        $member->remove();
        return YZE_JSON_View::success($this);
    }
    /**
     * @actionname 移除成员
     */
    public function post_remove(){
        $request = $this->request;
        $this->layout = '';
        $pid = $request->get_var('pid');
        $uuid = $request->get_from_get('uuid');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $myMember = $project->get_member($loginUser->id);
        if (!$project || $myMember->role != Project_Member_Model::ROLE_ADMIN){
            return YZE_JSON_View::error($this, __('Please Contact Admin To Remove'));
        }

        $removeMember = find_by_uuid(Project_Member_Model::CLASS_NAME, $uuid);
        if (!$removeMember) return YZE_JSON_View::error($this, __('Member not exist'));
        if ($removeMember->role == Project_Member_Model::ROLE_ADMIN){
            $dba = YZE_DBAImpl::get_instance();
            $otherAdminCount = $dba->lookup('count(id)'
            , 'project_member'
            , "role='admin' and is_deleted=0 and id!=:id and project_id=:pid"
            ,[':id'=>$removeMember->id, ':pid'=>$project->id]);
            if (!$otherAdminCount){
                return YZE_JSON_View::error($this, __('There must be at least one admin member for the project'));
            }
        }
        $data = [
            'project_id'=>$project->id,
            'member_id'=>$myMember->id,
            'content'=> vsprintf(__('Remove Project Member: %s'), $removeMember->get_user()->nickname),
            'type'=>'member'];
        YZE_Hook::do_hook(YDE_CLOUD_PROJECT_ACTIVITY, $data);
        $removeMember->set('is_deleted', 1)->save();
        return YZE_JSON_View::success($this);
    }
    /**
     * @actionname 提交成员邀请
     */
    public function post_invite(){
        $request = $this->request;
        $this->layout = '';
        $pid = $request->get_var('pid');
        $uuid = $request->get_from_post('uuid');
        $region = $request->get_from_post('region');
        $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
        $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
        $cellphone_email = trim($request->get_from_post('cellphone_email'));
        $role = trim($request->get_from_post('role'));
        $project_member = $project->get_member($loginUser->id);

        if (!$project || !$project_member->can_edit()){
            return YZE_JSON_View::error($this, __('Please Contact Admin To Invite'));
        }

        if ($uuid) {// 修改的情况，只能修改role
            $data = [
                'role'=>$role
            ];
            $save_helper = new Save_Model_Helper();
            $save_helper->alias_classes = Project_Member_Model::CLASS_NAME;
            $save_helper->fetch_modify_model = function () use ($uuid){
                return find_by_uuid(Project_Member_Model::CLASS_NAME, $uuid);
            };
            $save_helper->before_save = function ($model) {
                if (!$model->id) throw new YZE_FatalException(__('member not exist'));
                return $model;
            };
            $save_helper->save($data);
            return YZE_JSON_View::success($this);
        }

        if (!strlen($cellphone_email)){
            return YZE_JSON_View::error($this, __('Please Input Email Or Cellphone'));
        }

        $cellphone = '';
        $email = '';
        $where = 'is_deleted=0';
        if (!preg_match("/@/", $cellphone_email)){
            $where .= " and cellphone=:phone and phone_region=:region";
            if (!$region) throw new YZE_FatalException('Please select region');
            $cellphone = $cellphone_email;
            $params[":phone"] = $cellphone_email;
            $params[":region"] = $region;
        }else{
            $where .= " and email=:email";
            $email = $cellphone_email;
            $params[":email"] = $cellphone_email;
        }

        $user = User_Model::from()->where($where)->get_Single($params);
        /**
         * 用户不存在则先创建一个用户，等该用户注册时在和这条记录绑定
         */
        if (!$user){
            $user = new User_Model();
            $user->set('uuid', User_Model::uuid())
                ->set('cellphone', $cellphone)
                ->set('email', $email)
                ->set('phone_region', $region)
                ->save();
        }
        if ($project->get_member($user->id)){
            throw new YZE_FatalException(vsprintf(__('%s is a member already'), $cellphone_email));
        }

        $data = [
            'role'=>$role,
            'project_id'=>$project->id,
            'user_id'=>$user->id,
            'is_invited'=>0,
            'uuid'=>Project_Member_Model::uuid()
        ];
        $save_helper = new Save_Model_Helper();
        $save_helper->alias_classes = Project_Member_Model::CLASS_NAME;
        $save_helper->save($data);
        $data = [
            'project_id'=>$project->id,
            'member_id'=>$project_member->id,
            'content'=>sprintf(__('Invite User %s'), $user->nickname?:$user->get_escape_cellphone()),
            'type'=>'member'];
        YZE_Hook::do_hook(YDE_CLOUD_PROJECT_ACTIVITY, $data);
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
