<?php

namespace app;

use app\common\Employee_Model;
use app\logs\Log_Column_Model;
use app\logs\Log_Model;
use yangzie\YZE_DBAImpl;
use yangzie\YZE_Hook;
use yangzie\YZE_Model;
use yangzie\YZE_Request;
use yangzie\YZE_Resource_Controller;
use yangzie\YZE_SQL;

/**
 * 日志记录方法：
 * 1. 在请求开始处理前，记录日志主表Log_Model，记录用户在通过什么client请求什么内容
 * 2. 在这次请求中所有model的CRUD通过响应的hook监听，并作为子表数据记录在Log_Column_Model中
 * 3. 通过Request的set get来共享这只请求的主表Log_Model
 */
YZE_Hook::add_hook ( YZE_HOOK_BEFORE_DISPATCH, function () {
    $request = YZE_Request::get_instance();
	$controller = $request->controller_instance();
    if($controller->has_Annotation($request->the_method(), "ignoreLog")) return $controller;

    $user = YZE_Hook::do_hook ( YZE_HOOK_GET_LOGIN_USER );
    if (!$user) return $controller;

    $log = new Log_Model();
    $log->set(Log_Model::F_CREATED_ON, date("Y-m-d H:i:s"))
        ->set(Log_Model::F_UUID, Log_Model::uuid())
        ->set(Log_Model::F_REQUEST_METHOD, $request->get_request_method())
        ->set(Log_Model::F_ACTION_NAME, $controller->get_Annotation($request->the_method(), 'actionName'))
        ->set(Log_Model::F_ACTION_TIME, date("Y-m-d H:i:s"))
        ->set(Log_Model::F_CLIENT_INFO, $request->get_from_server('HTTP_USER_AGENT'))
        ->set(Log_Model::F_CLIENT_IP, $request->get_from_server('REMOTE_ADDR'))
        ->set(Log_Model::F_REQUEST_URL, $request->the_full_uri())
        ->set(Log_Model::F_USER_ID, $user->get_key())
        ->set(Log_Model::F_USER_NAME, $user->name.$user->cellphone)
        ->save();

	$request->set("log", $log);
	return $controller;
});


YZE_Hook::add_hook ( YZE_HOOK_MODEL_INSERT, function ( $model ) {
	$request = YZE_Request::get_instance();
	$log = $request->get('log');
	if ( ! ( $model instanceof YZE_Model ) ) return $model;
	if (  $model instanceof Log_Model  ||  $model instanceof Log_Column_Model) return $model;
	if (  !$log ) return $model;

	$record = $model->get_records ();
	foreach ( $record as $key => $val ) {
		$item = new Log_Column_Model ();
		$item->set ( Log_Column_Model::F_CREATED_ON, date("Y-m-d H:i:s") );
		$item->set ( Log_Column_Model::F_UUID, Log_Column_Model::uuid() );
		$item->set ( Log_Column_Model::F_COLUMN, $key );
		$item->set ( Log_Column_Model::F_LOG_ID, $log->id );
		$item->set ( Log_Column_Model::F_NEW_VALUE, $val );
		$item->set ( Log_Column_Model::F_DB_TYPE, Log_Column_Model::DB_TYPE_C );
		$item->set ( Log_Column_Model::F_TABLE, $model->get_table() );
		$item->save ();
	}
	return $model;
} );


YZE_Hook::add_hook ( YZE_HOOK_MODEL_SELECT, function ( $models ) {
	$request = YZE_Request::get_instance();
	$log = $request->get('log');

	if (  !$log ) return $models;

	$tables = [];
	foreach ($models as $assoc_models){
		$arr = $assoc_models instanceof YZE_Model ? [$assoc_models] : (array)$assoc_models;
		foreach ($arr as $model){
			if (!$model)continue;
			$tables[$model->get_table()] = true;
		}
	}
	foreach ($tables as $table=>$flag){
		$item = new Log_Column_Model ();
		$item->set ( Log_Column_Model::F_CREATED_ON, date("Y-m-d H:i:s") );
		$item->set ( Log_Column_Model::F_LOG_ID, $log->id );
		$item->set ( Log_Column_Model::F_UUID, Log_Column_Model::uuid() );
		$item->set ( Log_Column_Model::F_DB_TYPE, Log_Column_Model::DB_TYPE_R );
		$item->set ( Log_Column_Model::F_TABLE, $table );
		$item->save ();
	}
	return $models;
} );

YZE_Hook::add_hook ( YZE_HOOK_MODEL_UPDATE, function ( $model ) {
	$request = YZE_Request::get_instance();
	$log = $request->get('log');
	if ( ! ( $model instanceof YZE_Model ) ) return $model;
	if (  $model instanceof Log_Model  ||  $model instanceof Log_Column_Model) return $model;
	if (  !$log ) return $model;

	$class_name = get_class ( $model );
	$old_model = $class_name::find_by_id ( $model->get_key() );
	$old_records = $old_model ? $old_model->get_records () : [];
	$new_records = $model->get_records ();

	foreach ( $new_records as $key => $val ) {
		if ($val==@$old_records[$key]) continue;
		$item = new Log_Column_Model ();
		$item->set ( Log_Column_Model::F_CREATED_ON, date("Y-m-d H:i:s") );
		$item->set ( Log_Column_Model::F_UUID, Log_Column_Model::uuid() );
		$item->set ( Log_Column_Model::F_LOG_ID, $log->id );
		$item->set ( Log_Column_Model::F_COLUMN, $key );
		$item->set ( Log_Column_Model::F_OLD_VALUE, @$old_records[$key] );
		$item->set ( Log_Column_Model::F_NEW_VALUE, $val );
		$item->set ( Log_Column_Model::F_DB_TYPE, Log_Column_Model::DB_TYPE_U );
		$item->set ( Log_Column_Model::F_TABLE, $model->get_table() );
		$item->save ();
	}
	return $model;
} );

YZE_Hook::add_hook ( YZE_HOOK_MODEL_DELETE, function ( $model ) {
	$request = YZE_Request::get_instance();
	$log = $request->get('log');
	if ( ! ( $model instanceof YZE_Model ) ) return $model;
	if (  $model instanceof Log_Model  ||  $model instanceof Log_Column_Model) return $model;
	if (  !$log ) return $model;

	$new_records = $model->get_records ();

	foreach ( $new_records as $key => $val ) {
		$item = new Log_Column_Model ();
		$item->set ( Log_Column_Model::F_CREATED_ON, date("Y-m-d H:i:s") );
		$item->set ( Log_Column_Model::F_UUID, Log_Column_Model::uuid() );
		$item->set ( Log_Column_Model::F_LOG_ID, $log->id );
		$item->set ( Log_Column_Model::F_COLUMN, $key );
		$item->set ( Log_Column_Model::F_OLD_VALUE, $val );
		$item->set ( Log_Column_Model::F_DB_TYPE, Log_Column_Model::DB_TYPE_D );
		$item->set ( Log_Column_Model::F_TABLE, $model->get_table() );
		$item->save ();
	}
	return $model;
} );

?>
