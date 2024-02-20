<?php

namespace app\project;

use yangzie\YZE_FatalException;
use yangzie\YZE_Hook;
use yangzie\YZE_JSON_View;
use yangzie\YZE_Request;

/**
 * 传入参数 ['project_id'=>'', 'member_id'=>'', 'content'=>'', 'type'=>'']
 */
define('YDE_CLOUD_PROJECT_ACTIVITY', 'YDE_CLOUD_PROJECT_ACTIVITY');

YZE_Hook::add_hook ( YDE_CLOUD_PROJECT_ACTIVITY, function ( $data ) {
//	try{
		list ('project_id'=>$project_id, 'member_id'=>$member_id, 'content'=>$content, 'type'=>$type) = $data;

		$activity = new Activity_Model();
		$activity->set(Activity_Model::F_UUID, Activity_Model::uuid())
		->set(Activity_Model::F_PROJECT_ID, $project_id)
		->set(Activity_Model::F_CONTENT, $content)
		->set(Activity_Model::F_CREATED_ON, date("Y-m-d H:i:s"))
		->set(Activity_Model::F_PROJECT_MEMBER_ID, $member_id)
		->set(Activity_Model::F_TYPE, $type)
		->save();
//	}catch (\Exception $e){
		// igonre
//	}
} );

?>
