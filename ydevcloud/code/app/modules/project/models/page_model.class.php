<?php
namespace app\project;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use \app\project\Function_Model;
use \app\project\Module_Model;
use \app\project\Project_Model;
use \app\project\User_Model;

/**
 *
 *
 * @version $Id$
 * @package project
 */
class Page_Model extends YZE_Model{
    use Page_Model_Method;
    
    const PAGE_TYPE_PAGE = 'page';
    const PAGE_TYPE_POPUP = 'popup';
    const PAGE_TYPE_MASTER = 'master';
    const PAGE_TYPE_SUBPAGE = 'subpage';
    const PAGE_TYPE_COMPONENT = 'component';
    const COMPONENT_END_KIND_PC = 'pc';
    const COMPONENT_END_KIND_MOBILE = 'mobile';
    const TABLE= "page";
    const VERSION = 'modified_on';
    const MODULE_NAME = "project";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\project\Page_Model';
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
     * 页面名称
     * @var string
     */
    const F_NAME = "name";
    /**
     * 页面的组成配置文件
     * @var string
     */
    const F_CONFIG = "config";
    /**
     * 截屏地址
     * @var string
     */
    const F_SCREEN = "screen";
    /**
     * 
     * @var integer
     */
    const F_MODULE_ID = "module_id";
    /**
     * 页面存储文件名
     * @var string
     */
    const F_FILE = "file";
    /**
     * 
     * @var string
     */
    const F_URL = "url";
    /**
     * 是否在生成预览图
     * @var integer
     */
    const F_IS_SNAPSHOTING = "is_snapshoting";
    /**
     * 
     * @var integer
     */
    const F_FUNCTION_ID = "function_id";
    /**
     * 最新的保存者
     * @var integer
     */
    const F_LAST_MEMBER_ID = "last_member_id";
    /**
     * 最新一个版本
     * @var integer
     */
    const F_LAST_VERSION_ID = "last_version_id";
    /**
     * 
     * @var enum
     */
    const F_PAGE_TYPE = "page_type";
    /**
     * 当前页面引用的目标页面id
     * @var integer
     */
    const F_REF_PAGE_ID = "ref_page_id";
    /**
     * 
     * @var integer
     */
    const F_CREATE_USER_ID = "create_user_id";
    /**
     * 
     * @var integer
     */
    const F_PROJECT_ID = "project_id";
    /**
     * 是否是组件
     * @var integer
     */
    const F_IS_COMPONENT = "is_component";
    /**
     * 跨项目共享的终端类型
     * @var enum
     */
    const F_COMPONENT_END_KIND = "component_end_kind";
    /**
     * 作为组件的根元素id
     * @var string
     */
    const F_COMPONENT_UIID = "component_uiid";
    /**
     * 作者是否删除
     * @var integer
     */
    const F_CREATE_USER_IS_DELETED = "create_user_is_deleted";
    public static $columns = [
    'id'         => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'created_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'modified_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'is_deleted' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'uuid'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'name'       => ['type' => 'string', 'null' => true,'length' => '45','default'	=> ''],
      'config'     => ['type' => 'string', 'null' => true,'length' => '','default'	=> ''],
      'screen'     => ['type' => 'string', 'null' => true,'length' => '145','default'	=> ''],
      'module_id'  => ['type' => 'integer', 'null' => true,'length' => '','default'	=> ''],
      'file'       => ['type' => 'string', 'null' => true,'length' => '45','default'	=> ''],
      'url'        => ['type' => 'string', 'null' => false,'length' => '145','default'	=> ''],
      'is_snapshoting' => ['type' => 'integer', 'null' => false,'length' => '1','default'	=> '0'],
      'function_id' => ['type' => 'integer', 'null' => true,'length' => '','default'	=> ''],
      'last_member_id' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '-1'],
      'last_version_id' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '-1'],
      'page_type'  => ['type' => 'enum', 'null' => false,'length' => '','default'	=> 'page'],
      'ref_page_id' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'create_user_id' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'project_id' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'is_component' => ['type' => 'integer', 'null' => false,'length' => '1','default'	=> '0'],
      'component_end_kind' => ['type' => 'enum', 'null' => false,'length' => '','default'	=> 'pc'],
      'component_uiid' => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'create_user_is_deleted' => ['type' => 'integer', 'null' => false,'length' => '1','default'	=> '0'],
    ];
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
  'module_id' => 'fk_page_module1_idx',
  'function_id' => 'fk_page_function1_idx',
  'project_id' => 'fk_page_project1_idx',
  'create_user_id' => 'fk_page_user1',
);
    		
    
	private $function;

	private $module;

	private $project;

	private $user;

	
	public function get_function(){
		if( ! $this->function){
			$this->function = Function_Model::find_by_id($this->get(self::F_FUNCTION_ID));
		}
		return $this->function;
	}
	
	/**
	 * @return Page_Model
	 */
	public function set_function(Function_Model $new){
		$this->function = $new;
		return $this;
	}

	public function get_module(){
		if( ! $this->module){
			$this->module = Module_Model::find_by_id($this->get(self::F_MODULE_ID));
		}
		return $this->module;
	}
	
	/**
	 * @return Page_Model
	 */
	public function set_module(Module_Model $new){
		$this->module = $new;
		return $this;
	}

	public function get_project(){
		if( ! $this->project){
			$this->project = Project_Model::find_by_id($this->get(self::F_PROJECT_ID));
		}
		return $this->project;
	}
	
	/**
	 * @return Page_Model
	 */
	public function set_project(Project_Model $new){
		$this->project = $new;
		return $this;
	}

	public function get_user(){
		if( ! $this->user){
			$this->user = User_Model::find_by_id($this->get(self::F_CREATE_USER_ID));
		}
		return $this->user;
	}
	
	/**
	 * @return Page_Model
	 */
	public function set_user(User_Model $new){
		$this->user = $new;
		return $this;
	}

	
	public function get_page_type(){
		return ['page','popup','master','subpage','component'];
	}
	public function get_component_end_kind(){
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
		case self::F_NAME: return "页面名称";
		case self::F_CONFIG: return "页面的组成配置文件";
		case self::F_SCREEN: return "截屏地址";
		case self::F_MODULE_ID: return "module_id";
		case self::F_FILE: return "页面存储文件名";
		case self::F_URL: return "url";
		case self::F_IS_SNAPSHOTING: return "是否在生成预览图";
		case self::F_FUNCTION_ID: return "function_id";
		case self::F_LAST_MEMBER_ID: return "最新的保存者";
		case self::F_LAST_VERSION_ID: return "最新一个版本";
		case self::F_PAGE_TYPE: return "page_type";
		case self::F_REF_PAGE_ID: return "当前页面引用的目标页面id";
		case self::F_CREATE_USER_ID: return "create_user_id";
		case self::F_PROJECT_ID: return "project_id";
		case self::F_IS_COMPONENT: return "是否是组件";
		case self::F_COMPONENT_END_KIND: return "跨项目共享的终端类型";
		case self::F_COMPONENT_UIID: return "作为组件的根元素id";
		case self::F_CREATE_USER_IS_DELETED: return "作者是否删除";
    	default: return $column;
    	}
		return $column;
	}
}?>