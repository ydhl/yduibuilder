<?php
namespace app\project;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use \app\project\Project_Model;
use \app\user\User_Model;

/**
 *
 *
 * @version $Id$
 * @package project
 */
class Project_Member_Model extends YZE_Model{
    use Project_Member_Model_Method;
    
    const ROLE_ADMIN = 'admin';
    const ROLE_DEVELOPER = 'developer';
    const ROLE_REPORTER = 'reporter';
    const ROLE_GUEST = 'guest';
    const TABLE= "project_member";
    const VERSION = 'modified_on';
    const MODULE_NAME = "project";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\project\Project_Member_Model';
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
    const F_USER_ID = "user_id";
    /**
     * 
     * @var integer
     */
    const F_PROJECT_ID = "project_id";
    /**
     * 角色
     * @var enum
     */
    const F_ROLE = "role";
    /**
     * 
     * @var integer
     */
    const F_IS_CREATER = "is_creater";
    /**
     * 
     * @var integer
     */
    const F_LAST_PAGE_ID = "last_page_id";
    /**
     * 最近编辑的功能
     * @var integer
     */
    const F_LAST_FUNCTION_ID = "last_function_id";
    /**
     * 是否正在邀请
     * @var integer
     */
    const F_IS_INVITED = "is_invited";
    public static $columns = [
    'id'         => ['type' => 'integer', 'null' => false,'length' => '11','default'	=> ''],
      'created_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'modified_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'is_deleted' => ['type' => 'integer', 'null' => false,'length' => '4','default'	=> '0'],
      'uuid'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'user_id'    => ['type' => 'integer', 'null' => false,'length' => '11','default'	=> ''],
      'project_id' => ['type' => 'integer', 'null' => false,'length' => '11','default'	=> ''],
      'role'       => ['type' => 'enum', 'null' => false,'length' => '','default'	=> ''],
      'is_creater' => ['type' => 'integer', 'null' => false,'length' => '1','default'	=> '0'],
      'last_page_id' => ['type' => 'integer', 'null' => false,'length' => '11','default'	=> '0'],
      'last_function_id' => ['type' => 'integer', 'null' => false,'length' => '11','default'	=> '0'],
      'is_invited' => ['type' => 'integer', 'null' => false,'length' => '1','default'	=> '1'],
    ];
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
  'user_id' => 'fk_project_member_user1_idx',
  'project_id' => 'fk_project_member_project1_idx',
);
    		
    
	private $project;

	private $user;

	
	public function get_project(){
		if( ! $this->project){
			$this->project = Project_Model::find_by_id($this->get(self::F_PROJECT_ID));
		}
		return $this->project;
	}
	
	/**
	 * @return Project_Member_Model
	 */
	public function set_project(Project_Model $new){
		$this->project = $new;
		return $this;
	}

	public function get_user(){
		if( ! $this->user){
			$this->user = User_Model::find_by_id($this->get(self::F_USER_ID));
		}
		return $this->user;
	}
	
	/**
	 * @return Project_Member_Model
	 */
	public function set_user(User_Model $new){
		$this->user = $new;
		return $this;
	}

	
	public function get_role(){
		return ['admin','developer','reporter','guest'];
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
		case self::F_USER_ID: return "user_id";
		case self::F_PROJECT_ID: return "project_id";
		case self::F_ROLE: return "角色";
		case self::F_IS_CREATER: return "is_creater";
		case self::F_LAST_PAGE_ID: return "last_page_id";
		case self::F_LAST_FUNCTION_ID: return "最近编辑的功能";
		case self::F_IS_INVITED: return "是否正在邀请";
    	default: return $column;
    	}
		return $column;
	}
}?>