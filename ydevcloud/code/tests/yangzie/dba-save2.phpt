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
	const TABLE= "testusers2";
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
$user->set("email", "333333");
$user->set("register_time", "2015-12-17 17:50:30");
$user->save();
//测试插入新数据
$insert_key = $user->get_key();
echo $insert_key>=1 ? "insert true" : "insert false";

try{
    $user->set("id", null);
    $user->save();
}catch(\Exception $e){
    //测试插入冲突
    echo "\r\n";
    echo $e->getMessage();
}


    //测试忽略冲突
$user->save(YZE_SQL::INSERT_ON_DUPLICATE_KEY_IGNORE);
echo "\r\n";
echo $user->get_key() ? "ignore false" : "ignore true";

//测试冲突是更新
$user->set("name", "aaaa");
$user->save(YZE_SQL::INSERT_ON_DUPLICATE_KEY_UPDATE);
echo "\r\n";
echo $insert_key  ==$user->get_key() ? "UPDATE true" : "UPDATE false";
echo "\r\n";
echo $user->get("name")  =="aaaa" ? "UPDATE true" : "UPDATE false";

//测试删除原来的，插入新的
$user->save(YZE_SQL::INSERT_ON_DUPLICATE_KEY_REPLACE);
echo "\r\n";
echo $insert_key + 1 ==$user->get_key() ? "REPLACE true" : "REPLACE false";
$insert_key +=1;

//测试存在时添加
$user1 = new UserModel();
$user1->set("name", "aa");
$user1->set("register_time", "2015-12-17 17:50:30");
$sql = new \yangzie\YZE_SQL();
$sql->from("\\yangzie\\UserModel", "u")->where("u","name",\yangzie\YZE_SQL::EQ, "aaaa");
$user1->set("email","1234");
$user1->save(YZE_SQL::INSERT_EXIST, $sql);
echo "\r\n";
echo $user1->get_key() && $user1->Get("email")=="1234" ? "INSERT_EXIST true" : "INSERT_EXIST false";
$user1->remove();

//测试不存在时添加
$user2 = new UserModel();
$user2->set("name", "aa");
$user2->set("register_time", "2015-12-17 17:50:30");
$sql = new \yangzie\YZE_SQL();
$sql->from("\\yangzie\\UserModel", "u")->where("u","name",\yangzie\YZE_SQL::EQ, "aaaaa");
$user2->set("email","12345");
$user2->save(YZE_SQL::INSERT_NOT_EXIST, $sql);
echo "\r\n";
echo $user2->get_key() && $user2->Get("email")=="1234" ? "INSERT_NOT_EXIST true" : "INSERT_NOT_EXIST false";
$user2->remove();

//测试不存在时添加，存在更新
$user3 = new UserModel();
$user3->set("name", "aa");
$user3->set("register_time", "2015-12-17 17:50:30");
$sql = new \yangzie\YZE_SQL();
$sql->from("\\yangzie\\UserModel", "u")->where("u","name",\yangzie\YZE_SQL::EQ, "aaaaa");
$user3->set("email","123456");
$user3->save(YZE_SQL::INSERT_NOT_EXIST_OR_UPDATE, $sql);
echo "\r\n";
echo $user3->get_key() && $user3->Get("email")=="123456" ? "INSERT_NOT_EXIST_OR_UPDATE true" : "INSERT_NOT_EXIST_OR_UPDATE false";

//测试不存在时添加，存在更新
$user3 = new UserModel();
$user3->set("name", "aa");
$user3->set("register_time", "2015-12-17 17:50:30");
$sql = new \yangzie\YZE_SQL();
$sql->from("\\yangzie\\UserModel", "u")->where("u","name",\yangzie\YZE_SQL::EQ, "aaaaaaaa");
$user3->set("email","1234567");
$user3->save(YZE_SQL::INSERT_NOT_EXIST_OR_UPDATE, $sql);
echo "\r\n";
echo $user3->get_key() && $user3->Get("email")=="1234567" ? "INSERT_NOT_EXIST_OR_UPDATE true" : "INSERT_NOT_EXIST_OR_UPDATE false";

//删除
$user->remove();
echo "\r\n";
echo $user->get_key()>0 ? "remove false" : "remove true";
?>
--EXPECT--
insert true
23000, 1062, Duplicate entry '333333' for key 'email_UNIQUE'
ignore true
UPDATE true
UPDATE true
REPLACE true
INSERT_EXIST true
INSERT_NOT_EXIST true
INSERT_NOT_EXIST_OR_UPDATE true
INSERT_NOT_EXIST_OR_UPDATE true
remove true
