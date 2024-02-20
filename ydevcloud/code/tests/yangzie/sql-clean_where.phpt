--TEST--
SQL clear_where Test
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
class OrderModel extends YZE_Model{
	const TABLE= "ordermodel";
	const VERSION = 'modified_on';
	const MODULE_NAME = "test";
	const KEY_NAME = "id";
	const F_ID = "id";
	const CLASS_NAME = 'yangzie\OrderModel';

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


$sql = new \yangzie\YZE_SQL();
$sql->clean()->from(UserModel::CLASS_NAME,"u")
        ->left_join(OrderModel::CLASS_NAME, "o", "o.id=u.id")
        ->where("u", "name", YZE_SQL::EQ,"user")
        ->where("o", "name", YZE_SQL::EQ,"order")
        ->where("u", "email", YZE_SQL::EQ,"user")
        ->where("o", "email", YZE_SQL::EQ,"order");
echo $sql,"\r\n\r\n";
$sql->clean_where("u", "name");
echo $sql,"\r\n\r\n";
$sql->clean_where("o");
echo $sql,"\r\n\r\n";
$sql->clean_where();
echo $sql,"\r\n\r\n";
?>
--EXPECT--
SELECT u.id AS u_id,u.name AS u_name,u.register_time AS u_register_time,u.email AS u_email,u.created_on AS u_created_on,u.modified_on AS u_modified_on,o.id AS o_id,o.name AS o_name,o.register_time AS o_register_time,o.email AS o_email,o.created_on AS o_created_on,o.modified_on AS o_modified_on 
FROM testusers3 AS u  LEFT JOIN ordermodel AS o ON o.id=u.id 
WHERE u.name = 'user' AND o.name = 'order' AND u.email = 'user' AND o.email = 'order'

SELECT u.id AS u_id,u.name AS u_name,u.register_time AS u_register_time,u.email AS u_email,u.created_on AS u_created_on,u.modified_on AS u_modified_on,o.id AS o_id,o.name AS o_name,o.register_time AS o_register_time,o.email AS o_email,o.created_on AS o_created_on,o.modified_on AS o_modified_on 
FROM testusers3 AS u  LEFT JOIN ordermodel AS o ON o.id=u.id 
WHERE o.name = 'order' AND u.email = 'user' AND o.email = 'order'

SELECT u.id AS u_id,u.name AS u_name,u.register_time AS u_register_time,u.email AS u_email,u.created_on AS u_created_on,u.modified_on AS u_modified_on,o.id AS o_id,o.name AS o_name,o.register_time AS o_register_time,o.email AS o_email,o.created_on AS o_created_on,o.modified_on AS o_modified_on 
FROM testusers3 AS u  LEFT JOIN ordermodel AS o ON o.id=u.id 
WHERE u.email = 'user'

SELECT u.id AS u_id,u.name AS u_name,u.register_time AS u_register_time,u.email AS u_email,u.created_on AS u_created_on,u.modified_on AS u_modified_on,o.id AS o_id,o.name AS o_name,o.register_time AS o_register_time,o.email AS o_email,o.created_on AS o_created_on,o.modified_on AS o_modified_on 
FROM testusers3 AS u  LEFT JOIN ordermodel AS o ON o.id=u.id 