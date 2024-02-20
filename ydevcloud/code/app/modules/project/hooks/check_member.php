<?php

use app\project\Project_Model;
use yangzie\YZE_FatalException;
use yangzie\YZE_Hook;
use yangzie\YZE_Request;

YZE_Hook::add_hook(YZE_HOOK_BEFORE_DISPATCH, function (){
    $request = YZE_Request::get_instance();
    if ($request->module()!='project')return;
    $pid = $request->get_var('pid');
    if (!$pid) return;
    $project = find_by_uuid(Project_Model::CLASS_NAME, $pid);
    $loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
    if (!$project) throw new YZE_FatalException( \yangzie\__('Project not found'));
    if (!$project->get_member($loginUser->id)) throw new YZE_FatalException( \yangzie\__('you are not a project member'));
});
