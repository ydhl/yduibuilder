--TEST--
测试Model的各种查询方法
--FILE--
<?php
namespace  yangzie;
ini_set("display_errors",1);
chdir(dirname(dirname(dirname(__FILE__)))."/app/public_html");
include "init.php";

/**
 * 这里的测试需要配置数据库连接
 */


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
class TestModel extends YZE_Model{
	const TABLE= "testtests";
	const VERSION = 'modified_on';
	const MODULE_NAME = "test";
	const KEY_NAME = "id";
	const F_ID = "id";
	const CLASS_NAME = 'yangzie\TestModel';

	const F_TITLE = "title";
	const F_USER_ID = "user_id";
	const F_CREATED_ON = "created_on";
	const F_MODIFIED_ON = "modified_on";

	public static $columns = array(
			'id'         => array('type' => 'integer', 'null' => false,'length' => '11','default'	=> '',),
			'title'      => array('type' => 'string', 'null' => false,'length' => '201','default'	=> '',),
			'user_id'      => array('type' => 'integer', 'null' => false,'length' => '11','default'	=> '',),
			'created_on' => array('type' => 'date', 'null' => false,'length' => '','default'	=> '',),
			'modified_on' => array('type' => 'TIMESTAMP', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP',),
	);
}
class OrderModel extends YZE_Model{
	const TABLE= "testorders";
	const VERSION = 'modified_on';
	const MODULE_NAME = "order";
	const KEY_NAME = "id";
	const F_ID = "id";
	const CLASS_NAME = 'yangzie\OrderModel';

	const F_ORDER_ID = "order_id";
	const F_USER_ID = "user_id";
	const F_CREATED_ON = "created_on";
	const F_MODIFIED_ON = "modified_on";

	public static $columns = array(
			'id'         => array('type' => 'integer', 'null' => false,'length' => '11','default'	=> '',),
			'order_id'      => array('type' => 'string', 'null' => false,'length' => '201','default'	=> '',),
			'user_id'      => array('type' => 'integer', 'null' => false,'length' => '11','default'	=> '',),
			'created_on' => array('type' => 'date', 'null' => false,'length' => '','default'	=> '',),
			'modified_on' => array('type' => 'TIMESTAMP', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP',),
	);
}

/**
 * 初始化数据
 */

YZE_DBAImpl::get_instance()->migration(TestModel::CLASS_NAME, true);
YZE_DBAImpl::get_instance()->migration(OrderModel::CLASS_NAME, true);
YZE_DBAImpl::get_instance()->migration(UserModel::CLASS_NAME, true);
TestModel::truncate();
OrderModel::truncate();
UserModel::truncate();

$user = new UserModel();
$user->set(UserModel::F_TITLE, "test user")
->set(UserModel::F_CREATED_ON, date("Y-m-d H:i:s"))
->save();

$test = new TestModel();
$test->set(TestModel::F_TITLE, "test title")
->set(UserModel::F_CREATED_ON, date("Y-m-d H:i:s"))
->set(TestModel::F_USER_ID, 1)
->save();

$order = new OrderModel();
$order->set(OrderModel::F_ORDER_ID, "112233")
->set(UserModel::F_CREATED_ON, date("Y-m-d H:i:s"))
->set(OrderModel::F_USER_ID, 1)
->save();

echo "单表查询:\r\n";

#where调用
var_dump( TestModel::where("title=:title and (id=3)")->get_Single(array(":title"=>"tes2121t title")));

echo TestModel::where("title=:title and (id=1)")->get_Single(array(":title"=>"test title"))->get(TestModel::F_TITLE);
#调用方法4
echo "\r\n";
echo TestModel::where("t.title=:title")
	->left_Join("t", UserModel::CLASS_NAME, "u", "t.user_id=u.id")
	->get_Single(array(":title"=>"test title"),"t")->get(TestModel::F_TITLE);
echo "\r\n";
echo( TestModel::where("t.title=:title")
	->left_Join("t", UserModel::CLASS_NAME, "u", "t.user_id=u.id")
	->get_Single(array(":title"=>"test title"),"u")->get(UserModel::F_TITLE));
echo "\r\n";
$rsts = TestModel::where("t.title=:title")
	->left_Join("t", UserModel::CLASS_NAME, "u", "t.user_id=u.id")
	->limit(5)
	->select(array(":title"=>"test title"),"u");
echo count($rsts);
echo "\r\n";
echo $rsts[0]->get(UserModel::F_TITLE);
echo "\r\n";


$rsts = TestModel::where("t.title=:title")
	->left_Join("t", UserModel::CLASS_NAME, "u", "t.user_id=u.id")
	->limit(5)->group_by(TestModel::F_TITLE,"t")
	->select(array(":title"=>"test title"),"u");
echo count($rsts);
echo "\r\n";
echo $rsts[0]->get(UserModel::F_TITLE);
echo "\r\n";

$rsts = TestModel::where("t.title=:title")
	->left_Join("t", UserModel::CLASS_NAME, "u", "t.user_id=u.id")
	->limit(5)->order_by(TestModel::F_TITLE,"asc","t")
	->select(array(":title"=>"test title"),"u");
echo count($rsts);
echo "\r\n";
echo $rsts[0]->get(UserModel::F_TITLE);
echo "\r\n";

$rsts = TestModel::where("t.title=:title")
	->left_Join("t", UserModel::CLASS_NAME, "u", "t.user_id=u.id")
	->limit(5)->order_by(TestModel::F_TITLE,"asc","u")->group_by(TestModel::F_TITLE,"u")
	->select(array(":title"=>"test title"),"u");
echo count($rsts);
echo "\r\n";
echo $rsts[0]->get(UserModel::F_TITLE);
echo "\r\n";

$rsts = TestModel::where("t.title=:title")
	->left_Join("t", UserModel::CLASS_NAME, "u", "t.user_id=u.id")
	->Join("t", OrderModel::CLASS_NAME, "o", "o.user_id=u.id")
	->limit(5)->order_by(TestModel::F_TITLE,"asc","u")->group_by(TestModel::F_TITLE,"u")
	->select(array(":title"=>"test title"),"o");

echo count($rsts);
echo "\r\n";
echo $rsts[0]->get(OrderModel::F_ORDER_ID);
echo "\r\n";


$rsts = TestModel::where("t.title=:title")
->left_Join("t", UserModel::CLASS_NAME, "u", "t.user_id=u.id")
->Join("t", OrderModel::CLASS_NAME, "o", "o.user_id=u.id")
->limit(5)->order_by(TestModel::F_TITLE,"asc","u")->group_by(TestModel::F_TITLE,"u")
->select(array(":title"=>"test title"));

echo count($rsts);
echo "\r\n";
echo count($rsts[0]);
echo "\r\n";
echo $rsts[0]['o']->get(OrderModel::F_ORDER_ID);
echo "\r\n";
echo $rsts[0]['u']->get(UserModel::F_TITLE);
echo "\r\n";
echo $rsts[0]['t']->get(TestModel::F_TITLE);
echo "\r\n";

?>
--EXPECT--
单表查询:
NULL
test title
test title
test user
1
test user
1
test user
1
test user
1
test user
1
112233
1
3
112233
test user
test title
