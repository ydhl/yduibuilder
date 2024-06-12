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
class Code_Model extends YZE_Model{
    use Code_Model_Method;
    
    const TABLE= "code";
    const VERSION = 'modified_on';
    const MODULE_NAME = "common";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\common\Code_Model';
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
     * @var integer
     */
    const F_IS_DELETED = "is_deleted";
    /**
     * 
     * @var string
     */
    const F_UUID = "uuid";
    /**
     * 
     * @var string
     */
    const F_TARGET = "target";
    /**
     * 
     * @var string
     */
    const F_CODE = "code";
    /**
     * 
     * @var date
     */
    const F_EXPIREIN = "expirein";
    public static $columns = [
    'id'         => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'created_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'modified_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'is_deleted' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'uuid'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'target'     => ['type' => 'string', 'null' => true,'length' => '45','default'	=> ''],
      'code'       => ['type' => 'string', 'null' => true,'length' => '45','default'	=> ''],
      'expirein'   => ['type' => 'date', 'null' => true,'length' => '','default'	=> ''],
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
		case self::F_IS_DELETED: return "is_deleted";
		case self::F_UUID: return "uuid";
		case self::F_TARGET: return "target";
		case self::F_CODE: return "code";
		case self::F_EXPIREIN: return "expirein";
    	default: return $column;
    	}
		return $column;
	}
}?>