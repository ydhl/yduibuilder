<?php

use app\user\User_Model;
use app\vendor\Jwt;
use yangzie\YZE_I18N;
use \yangzie\YZE_SQL;
use \app\project\Page_User_Model;
use \app\project\Project_Member_Model;
use function yangzie\__;
use function yangzie\load_textdomain;

$olddir = getcwd();
chdir(dirname(__FILE__).'/../app/public_html');
require 'init.php';
chdir($olddir);
include_once 'factory.php';

function addUser($pageId, Project_Member_Model $member, $fd){
    $checkSQL = new YZE_SQL();
    $checkSQL->from(Page_User_Model::CLASS_NAME, 'c')
        ->where('c', Page_User_Model::F_MEMBER_ID, YZE_SQL::EQ, $member->id)
        ->where('c', Page_User_Model::F_PAGE_ID, YZE_SQL::EQ, $pageId);

    $user = new Page_User_Model();
    $user->set(Page_User_Model::F_UUID, Page_User_Model::uuid())
        ->set(Page_User_Model::F_MEMBER_ID, $member->id)
        ->set(Page_User_Model::F_PAGE_ID, $pageId)
        ->set(Page_User_Model::F_FD, $fd)
        ->save(YZE_SQL::INSERT_NOT_EXIST_OR_UPDATE, $checkSQL);
    return $user;
}

/**
 * 编译项目
 * @param $server
 * @param $frame
 * @param $loginUser
 * @param $data
 */
function buildProject($server, $frame, $loginUser, $data) {
    $project = find_by_uuid(\app\project\Project_Model::CLASS_NAME, $data['uuid']);
    if (!$project || !$project->get_member($loginUser->id)) {
        $server->push($frame->fd, __('project not exist'));
        return;
    }

    // 访问builder得到代码
    try{
        $url = Base_Factory::get_factory($server, $frame, $project, $data['token'])->build();
    } catch (\Exception $e) {
        $server->push($frame->fd, $e->getMessage());
        return;
    }
    $server->push($frame->fd, sprintf(__('compile finished please: <a target="_blank" href="%s">download</a>'), $url));
    $server->push($frame->fd, "done");
}

/**
 * 推送pageid页面打开的用户列表
 * @param $server
 * @param $pageid
 */
function pushPageUsers($server, $pageid) {
    $userAndMembers = Page_User_Model::from('pu')
        ->left_join(Project_Member_Model::CLASS_NAME, 'pm', 'pm.id = pu.member_id')
        ->left_join(User_Model::CLASS_NAME, 'u', 'u.id = pm.user_id')
        ->where('pu.page_id=:pid')->select([':pid'=>$pageid]);
    $user_list = [];
    foreach ($userAndMembers as $userAndMember){
        $user_list[$userAndMember['pu']->fd] = [
            "name"=>$userAndMember['u']->nickname,
            "readonly"=>!$userAndMember['pm']->can_edit(),
            "avatar"=>$userAndMember['u']->avatar ?: '/logo2.svg'
        ];
    }
//    print_r($user_list);
    foreach ($userAndMembers as $userAndMember){
        $push = $user_list;
        unset($push[$userAndMember['pu']->fd]); // 推送给"自己"的用户列表中不用包含自己
        $server->push($userAndMember['pu']->fd, json_encode(['type'=>'userList', 'userList'=>array_values($push)]));
    }
}
/**
 * 打开UIBuilder
 * @param $server
 * @param $frame
 * @param $loginUser
 * @param $data
 */
function openUIBuilder($server, $frame, $loginUser, $data) {
    $leavePageId = $data['leavePageId']; // 离开的前一个页面pageid
    $leavePage = find_by_uuid(\app\project\Page_Model::CLASS_NAME, $leavePageId);
    if ($leavePage){
        Page_User_Model::from()->where('page_id=:pid and fd=:fd')->delete([':pid'=>$leavePage->id, ':fd'=>$frame->fd]);
    }
    $page = find_by_uuid(\app\project\Page_Model::CLASS_NAME, $data['uuid']);
    if (!$page) {
        $server->push($frame->fd, json_encode(['type'=>'error', 'msg'=>__('page not exist')]));
        return;
    }

    $project = $page->get_project();
    $member = $project->get_member($loginUser->id);
    if (!$member) {
        $server->push($frame->fd, json_encode(['type'=>'error', 'msg'=>__('project not exist')]));
        return;
    }

    // 同一个帐号，不能同时打开同一个page（两个浏览器同时登陆的情况）
    $existUser = Page_User_Model::from()->where('page_id=:pid and member_id=:mid and fd!=:fd')
        ->get_Single([':pid'=>$page->id, ':mid'=>$member->id, ':fd'=>$frame->fd]);
    if ($existUser){
        // 把之前的用户踢下线
        $server->push($existUser->fd, json_encode(['type'=>'error', 'msg'=>__('you have opened this page in another browser')]));
        $server->disconnect($existUser->fd, SWOOLE_WEBSOCKET_CLOSE_NORMAL, __('you have opened this page in another browser'));
        $existUser->remove();
    }

    addUser($page->id, $member, $frame->fd);
    pushPageUsers($server, $page->id);
}

/**
 * 向所有打开被删除页面都人广播该页面被删除了
 * @param $server
 * @param $frame
 * @param $loginUser
 * @param $data
 */
function broadcastDeletedPage($server, $frame, $loginUser, $data) {
    $page = \app\project\Page_Model::find_by_id($data['id']);
    $userAndMembers = Page_User_Model::from('pu')
        ->left_join(Project_Member_Model::CLASS_NAME, 'pm', 'pm.id = pu.member_id')
        ->left_join(User_Model::CLASS_NAME, 'u', 'u.id = pm.user_id')
        ->where('pu.page_id=:pid')->select([':pid'=>$data['id']]);

    foreach ($userAndMembers as $userAndMember){
        if ($frame->fd != $userAndMember['pu']->fd) {
            $server->push($userAndMember['pu']->fd, json_encode(['type'=>'deletedPage', 'user'=>$loginUser->nickname, 'pageid'=>$data['pageid']]));
        }else{
            $server->push($userAndMember['pu']->fd, json_encode(['type'=>'userList', 'userList'=>[]]));
        }
    }
    if ($page) {
        // 删除打开该页面的用户状态
        Page_User_Model::from()->where('page_id=:pid')->delete([':pid'=>$page->id]);
    }
}
/**
 * 向所有打开该页面的人广播该页面被修改了
 * @param $server
 * @param $frame
 * @param $loginUser
 * @param $data
 */
function broadcastModifiedPage($server, $frame, $loginUser, $data) {
    $page = find_by_uuid(\app\project\Page_Model::CLASS_NAME, $data['pageid']);
    if (!$page) return;
    $userAndMembers = Page_User_Model::from('pu')
        ->left_join(Project_Member_Model::CLASS_NAME, 'pm', 'pm.id = pu.member_id')
        ->left_join(User_Model::CLASS_NAME, 'u', 'u.id = pm.user_id')
        ->where('pu.page_id=:pid')->select([':pid'=>$page->id]);

    foreach ($userAndMembers as $userAndMember){
        if ($loginUser->id != $userAndMember['pm']->user_id) {
            $server->push($userAndMember['pu']->fd, json_encode(['type'=>'modifiedPage', 'pageid'=>$page->uuid, 'user'=>$loginUser->nickname]));
        }
    }
}
// 服务器
//$config = array(
//    'ssl_key_file'  => '/yde/ssl/ydecloud.yidianhulian.com.key',
//    'ssl_cert_file' => '/yde/ssl/ydecloud.yidianhulian.com.pem',
//);
//$server = new Swoole\Websocket\Server('0.0.0.0', 8888, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
//$server->set($config);
// 本地
$server = new Swoole\Websocket\Server('0.0.0.0', 8888);

$server->on('open', function($server, $req) {
    echo "connection open: {$req->fd}\n";
});

$server->on('message', function($server, $frame){
    try {
        \yangzie\YZE_DBAImpl::get_instance()->reset();// 每次都重启mysql连接，解决MySQL server has gone away
        $data = json_decode($frame->data, true);
//        echo $frame->data."\n";
        // 验证token
        if (!$data['token']) { // 设计器jwt登录
            $server->push($frame->fd, json_encode(['type'=>'error', 'msg'=>__('user auth fail, no token param')]));
            return;
        }
        $getPayload = Jwt::verifyToken($data['token']);
        if (!$getPayload) {
            $server->push($frame->fd, json_encode(['type'=>'error', 'msg'=>__('user auth fail, token is invalid')]));
            return;
        }
        $loginUser = find_by_uuid(User_Model::CLASS_NAME, $getPayload['sub']);
        if (!$loginUser) {
            $server->push($frame->fd, json_encode(['type'=>'error', 'msg'=>__('user auth fail')]));
            return;
        }

        YZE_I18N::get_instance()->clear();
        $locale = $data['lang'] ?: 'en';
        $mofile =  YZE_INSTALL_PATH."i18n/$locale.mo";
        load_textdomain('default', $mofile);
//        $server->push($frame->fd, $locale);

        switch ($data['action']){
            case 'build': return buildProject($server, $frame, $loginUser, $data);
            case 'uibuilder': return openUIBuilder($server, $frame, $loginUser, $data);
            case 'deletedPage': return broadcastDeletedPage($server, $frame, $loginUser, $data);
            case 'modifiedPage': return broadcastModifiedPage($server, $frame, $loginUser, $data);
        }

    }catch (\Exception $e) {
        $server->push($frame->fd, json_encode(['type'=>'error', 'msg'=>$e->getMessage()]));
    }
});

$server->on('close', function($server, $fd) {
    echo "connection close: {$fd}\n";
    // 如果是page 打开用户，则清空其记录
    $openedPages = Page_User_Model::from()->where('fd=:fd')->select([':fd'=>$fd]);
    foreach ((array)$openedPages as $openedPage) {
        $openedPage->remove();
        pushPageUsers($server, $openedPage->page_id);
    }
});

echo "waiting for client\r\n";


// 重启server后，重置页面用户在线记录
$sql = 'delete from`page_user` where id > 0';
\yangzie\YZE_DBAImpl::get_instance(true)->exec($sql);
$server->start();

