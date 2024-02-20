<?php
namespace app\project;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use \app\project\Module_Model;

/**
 *
 *
 * @version $Id$
 * @package project
 */
class Function_Model extends YZE_Model{
    use Function_Model_Method;
    
    const TABLE= "function";
    const VERSION = 'modified_on';
    const MODULE_NAME = "project";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\project\Function_Model';
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
     * @var integer
     */
    const F_MODULE_ID = "module_id";
    /**
     * 
     * @var string
     */
    const F_NAME = "name";
    /**
     * 
     * @var string
     */
    const F_DESC = "desc";
    /**
     * 
     * @var string
     */
    const F_SCREEN = "screen";
    public static $columns = [
    'id'         => ['type' => 'integer', 'null' => false,'length' => '11','default'	=> ''],
      'created_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'modified_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'is_deleted' => ['type' => 'integer', 'null' => false,'length' => '4','default'	=> '0'],
      'uuid'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'module_id'  => ['type' => 'integer', 'null' => false,'length' => '11','default'	=> ''],
      'name'       => ['type' => 'string', 'null' => true,'length' => '45','default'	=> ''],
      'desc'       => ['type' => 'string', 'null' => true,'length' => '245','default'	=> ''],
      'screen'     => ['type' => 'string', 'null' => true,'length' => '145','default'	=> ''],
    ];
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
  'module_id' => 'fk_function_module1_idx',
);
    		
    
	private $module;

	
	public function get_module(){
		if( ! $this->module){
			$this->module = Module_Model::find_by_id($this->get(self::F_MODULE_ID));
		}
		return $this->module;
	}
	
	/**
	 * @return Function_Model
	 */
	public function set_module(Module_Model $new){
		$this->module = $new;
		return $this;
	}

	
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
		case self::F_MODULE_ID: return "module_id";
		case self::F_NAME: return "name";
		case self::F_DESC: return "desc";
		case self::F_SCREEN: return "screen";
    	default: return $column;
    	}
		return $column;
	}
}?>