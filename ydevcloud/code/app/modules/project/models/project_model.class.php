<?php
namespace app\project;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;

/**
 *
 *
 * @version $Id$
 * @package project
 */
class Project_Model extends YZE_Model{
    use Project_Model_Method;
    
    const END_KIND_PC = 'pc';
    const END_KIND_MOBILE = 'mobile';
    const TABLE= "project";
    const VERSION = 'modified_on';
    const MODULE_NAME = "project";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\project\Project_Model';
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
    const F_NAME = "name";
    /**
     * 最近编辑的页面id
     * @var integer
     */
    const F_LAST_PAGE_ID = "last_page_id";
    /**
     * 最近编辑的功能
     * @var integer
     */
    const F_LAST_FUNCTION_ID = "last_function_id";
    /**
     * 简要描述
     * @var string
     */
    const F_DESC = "desc";
    /**
     * 默认主页id
     * @var integer
     */
    const F_HOME_PAGE_ID = "home_page_id";
    /**
     * 终端类型
     * @var enum
     */
    const F_END_KIND = "end_kind";
    public static $columns = [
    'id'         => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'created_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'modified_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'is_deleted' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'uuid'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'name'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'last_page_id' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'last_function_id' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'desc'       => ['type' => 'string', 'null' => true,'length' => '145','default'	=> ''],
      'home_page_id' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'end_kind'   => ['type' => 'enum', 'null' => false,'length' => '','default'	=> 'pc'],
    ];
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
);
    		
    
	
	
	public function get_end_kind(){
		return ['pc','mobile'];
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
		case self::F_NAME: return "name";
		case self::F_LAST_PAGE_ID: return "最近编辑的页面id";
		case self::F_LAST_FUNCTION_ID: return "最近编辑的功能";
		case self::F_DESC: return "简要描述";
		case self::F_HOME_PAGE_ID: return "默认主页id";
		case self::F_END_KIND: return "终端类型";
    	default: return $column;
    	}
		return $column;
	}
}?>