<?php
namespace app\project;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use \app\project\Page_Model;
use \app\project\Project_Member_Model;

/**
 *
 *
 * @version $Id$
 * @package project
 */
class Page_Version_Model extends YZE_Model{
    use Page_Version_Model_Method;
    
    const TABLE= "page_version";
    const VERSION = 'modified_on';
    const MODULE_NAME = "project";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\project\Page_Version_Model';
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
    const F_PAGE_ID = "page_id";
    /**
     * 
     * @var integer
     */
    const F_PROJECT_MEMBER_ID = "project_member_id";
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
    const F_INDEX = "index";
    /**
     * 
     * @var string
     */
    const F_MESSAGE = "message";
    public static $columns = [
    'id'         => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'created_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'modified_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'is_deleted' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'uuid'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'page_id'    => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'project_member_id' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'config'     => ['type' => 'string', 'null' => true,'length' => '','default'	=> ''],
      'screen'     => ['type' => 'string', 'null' => true,'length' => '145','default'	=> ''],
      'index'      => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'message'    => ['type' => 'string', 'null' => true,'length' => '999','default'	=> ''],
    ];
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
  'page_id' => 'fk_page_version_page1_idx',
  'project_member_id' => 'fk_page_version_project_member1_idx',
);
    		
    
	private $page;

	private $project_member;

	
	public function get_page(){
		if( ! $this->page){
			$this->page = Page_Model::find_by_id($this->get(self::F_PAGE_ID));
		}
		return $this->page;
	}
	
	/**
	 * @return Page_Version_Model
	 */
	public function set_page(Page_Model $new){
		$this->page = $new;
		return $this;
	}

	public function get_project_member(){
		if( ! $this->project_member){
			$this->project_member = Project_Member_Model::find_by_id($this->get(self::F_PROJECT_MEMBER_ID));
		}
		return $this->project_member;
	}
	
	/**
	 * @return Page_Version_Model
	 */
	public function set_project_member(Project_Member_Model $new){
		$this->project_member = $new;
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
		case self::F_PAGE_ID: return "page_id";
		case self::F_PROJECT_MEMBER_ID: return "project_member_id";
		case self::F_CONFIG: return "页面的组成配置文件";
		case self::F_SCREEN: return "截屏地址";
		case self::F_INDEX: return "index";
		case self::F_MESSAGE: return "message";
    	default: return $column;
    	}
		return $column;
	}
}?>