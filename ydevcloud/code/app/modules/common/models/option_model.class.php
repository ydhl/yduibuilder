<?php
namespace app\common;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;

/**
 *
 *
 * @version $Id$
 * @package common
 */
class Option_Model extends YZE_Model{
    use Option_Model_Method;
    
    const TABLE= "option";
    const VERSION = 'modified_on';
    const MODULE_NAME = "common";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\common\Option_Model';
    /**
     * @see YZE_Model::$encrypt_columns 
     */
    public $encrypt_columns = array();
    
    /**
     * 
     * @var integer
     */
    const F_ID = "id";
    /**
     * 
     * @var date
     */
    const F_CREATED_ON = "created_on";
    /**
     * 
     * @var date
     */
    const F_MODIFIED_ON = "modified_on";
    /**
     * 
     * @var string
     */
    const F_UUID = "uuid";
    /**
     * 
     * @var string
     */
    const F_OPTION_NAME = "option_name";
    /**
     * 
     * @var string
     */
    const F_OPTION_VALUE = "option_value";
    /**
     * 
     * @var integer
     */
    const F_IS_DELETED = "is_deleted";
    public static $columns = [
    'id'         => ['type' => 'integer', 'null' => false,'length' => '11','default'	=> ''],
      'created_on' => ['type' => 'date', 'null' => true,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'modified_on' => ['type' => 'date', 'null' => true,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'uuid'       => ['type' => 'string', 'null' => true,'length' => '45','default'	=> ''],
      'option_name' => ['type' => 'string', 'null' => true,'length' => '45','default'	=> ''],
      'option_value' => ['type' => 'string', 'null' => true,'length' => '','default'	=> ''],
      'is_deleted' => ['type' => 'integer', 'null' => false,'length' => '1','default'	=> '0'],
    ];
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
);
    		
    
	
	
	/**
	 * 返回每个字段的具体的面向用户可读的含义，比如login_name表示登录名
	 * @param $column
	 * @return mixed
	 */
    public function get_column_mean($column){
    	switch ($column){
    	case self::F_ID: return "id";
		case self::F_CREATED_ON: return "created_on";
		case self::F_MODIFIED_ON: return "modified_on";
		case self::F_UUID: return "uuid";
		case self::F_OPTION_NAME: return "option_name";
		case self::F_OPTION_VALUE: return "option_value";
		case self::F_IS_DELETED: return "is_deleted";
    	default: return $column;
    	}
		return $column;
	}
}?>