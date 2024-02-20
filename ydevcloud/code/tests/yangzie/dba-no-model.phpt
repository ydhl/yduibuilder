--TEST--
DBA Tester
--FILE--
<?php
namespace  yangzie;
ini_set("display_errors",1);
chdir(dirname(dirname(dirname(__FILE__)))."/app/public_html");
include "init.php";


//测试用的Model
class UserModel extends YZE_Model{
	const TABLE= "testusers";
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
			'title'      => array('type' => 'string', 'null' => false,'length' => '201','default'	=> '',),
			'created_on' => array('type' => 'date', 'null' => false,'length' => '','default'	=> '',),
			'modified_on' => array('type' => 'TIMESTAMP', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP',),
	);
}

/**
 * 初始化数据
 */

YZE_DBAImpl::get_instance()->migration(UserModel::CLASS_NAME);
UserModel::truncate();

$user = new UserModel();
$user->set(UserModel::F_TITLE, "test user")
->set(UserModel::F_CREATED_ON, date("Y-m-d H:i:s"))
->save();

$user1 = new UserModel();
$user1->set(UserModel::F_TITLE, "test user 1")
->set(UserModel::F_CREATED_ON, date("Y-m-d H:i:s"))
->save();

/**
 * 这里的测试需要配置数据库连接
 */

$db = YZE_DBAImpl::get_instance();

echo $db->lookup("id","testusers","id >=:id", array(":id"=>"1")),"\r\n";
print_r( $db->lookup_record("id,title","testusers","id =:id", array(":id"=>"1")) );
print_r( $db->lookup_record("id,title","testusers") );
print_r( $db->lookup_records("id,title","testusers","id in (1,2)") );
print_r( $db->update("testusers","title=:title", "id=:id", array(":id"=>"1",":title"=>"test user")) );
echo "\r\n";
?>
--EXPECT--
1
Array
(
    [id] => 1
    [title] => test user
)
Array
(
    [id] => 1
    [title] => test user
)
Array
(
    [0] => Array
        (
            [id] => 1
            [title] => test user
        )

    [1] => Array
        (
            [id] => 2
            [title] => test user 1
        )

)
1
