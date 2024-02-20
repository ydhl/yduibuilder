--TEST--
DBA new save Function Tester
--FILE--
<?php
namespace  yangzie;
ini_set("display_errors",1);
chdir(dirname(dirname(dirname(__FILE__)))."/app/public_html");
include "init.php";



//测试用的Model
class UserModel extends YZE_Model{
	const TABLE= "testusers3";
	const VERSION = 'modified_on';
	const MODULE_NAME = "test";
	const KEY_NAME = "id";
	const F_ID = "id";
	const CLASS_NAME = 'yangzie\UserModel';

	const F_TITLE = "title";
	const F_CREATED_ON = "created_on";
	const F_MODIFIED_ON = "modified_on";

	public static $columns = array(
			'id'         => array('type' => 'integer', 'null' => false,'length' => '11','default'	=> '',),
			'name'      => array('type' => 'string', 'null' => false,'length' => '201','default'	=> '',),
			'register_time' => array('type' => 'date', 'null' => false,'length' => '','default'	=> '',),
			'email' => array('type' => 'string', 'null' => false,'unique'=>true,'length' => '45','default'	=> '',),
			'created_on' => array('type' => 'date', 'null' => true,'length' => '','default'	=> '',),
			'modified_on' => array('type' => 'TIMESTAMP', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP',),
	);
}

/**
 * 初始化数据
 */

YZE_DBAImpl::get_instance()->migration(UserModel::CLASS_NAME);
UserModel::truncate();


/**
 * 这里的测试需要配置数据库连接
 */

$db   = \yangzie\YZE_DBAImpl::get_instance();
$user = new UserModel();

$user->set("name", "aa");
$user->set("email", "333");
$user->set("register_time", "2015-12-17 17:50:30");


$sql = new \yangzie\YZE_SQL();
$sql->clean()->insert('t',$user->get_records())->from(get_class($user),"t");
echo $sql,"\r\n";
$sql->clean()->insert('t',$user->get_records(), YZE_SQL::INSERT_ON_DUPLICATE_KEY_IGNORE)->from(get_class($user),"t");
echo $sql,"\r\n";
$sql->clean()->insert('t',$user->get_records(), YZE_SQL::INSERT_ON_DUPLICATE_KEY_REPLACE)->from(get_class($user),"t");
echo $sql,"\r\n";
$sql->clean()->insert('t',$user->get_records(), YZE_SQL::INSERT_EXIST)->from(get_class($user),"t")->where("t","id","=",1);
echo $sql,"\r\n";
$sql->clean()->insert('t',$user->get_records(), YZE_SQL::INSERT_NOT_EXIST)->from(get_class($user),"t")->where("t","id","=",1);
echo $sql,"\r\n";
$sql->clean()->insert('t',$user->get_records(), YZE_SQL::INSERT_ON_DUPLICATE_KEY_UPDATE, array("email"))->from(get_class($user),"t")->where("t","id","=",1);
echo $sql,"\r\n";
?>
--EXPECT--
INSERT INTO testusers3 (`name`,`email`,`register_time`) VALUES('aa','333','2015-12-17 17:50:30')
INSERT IGNORE INTO testusers3 (`name`,`email`,`register_time`) VALUES('aa','333','2015-12-17 17:50:30')
REPLACE INTO testusers3 SET `name`='aa',`email`='333',`register_time`='2015-12-17 17:50:30'
INSERT INTO testusers3 (`name`,`email`,`register_time`) SELECT 'aa','333','2015-12-17 17:50:30' FROM dual WHERE EXISTS (SELECT id FROM testusers3 WHERE `id` = 1)
INSERT INTO testusers3 (`name`,`email`,`register_time`) SELECT 'aa','333','2015-12-17 17:50:30' FROM dual WHERE NOT EXISTS (SELECT id FROM testusers3 WHERE `id` = 1)
INSERT INTO testusers3 (`name`,`email`,`register_time`) VALUES('aa','333','2015-12-17 17:50:30')  ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), `name`=VALUES(`name`),`register_time`=VALUES(`register_time`)
