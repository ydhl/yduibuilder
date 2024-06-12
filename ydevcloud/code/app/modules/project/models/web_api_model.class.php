<?php
namespace app\project;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use \app\project\Api_Folder_Model;
use \app\project\Project_Model;
use \app\project\Project_Member_Model;

/**
 *
 *
 * @version $Id$
 * @package project
 */
class Web_Api_Model extends YZE_Model{
    use Web_Api_Model_Method;
    
    const STATUS_DEVELOP = 'develop';
    const STATUS_TEST = 'test';
    const STATUS_DEPRECATED = 'deprecated';
    const STATUS_RELEASED = 'released';
    const REQUESTBODYTYPE_NONE = 'none';
    const REQUESTBODYTYPE_FORM_DATA = 'form-data';
    const REQUESTBODYTYPE_X_WWW_FORM_URLENCODED = 'x-www-form-urlencoded';
    const REQUESTBODYTYPE_JSON = 'json';
    const REQUESTBODYTYPE_XML = 'xml';
    const REQUESTBODYTYPE_RAW = 'raw';
    const REQUESTBODYTYPE_BINARY = 'binary';
    const REQUESTBODYTYPE_GRAPHQL = 'GraphQL';
    const REQUESTBODYTYPE_MSGPACK = 'msgpack';
    const TABLE= "web_api";
    const VERSION = 'modified_on';
    const MODULE_NAME = "project";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\project\Web_Api_Model';
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
     * api名称
     * @var string
     */
    const F_NAME = "name";
    /**
     * api方法
     * @var string
     */
    const F_METHOD = "method";
    /**
     * 请求路径
     * @var string
     */
    const F_PATH = "path";
    /**
     * 状态
     * @var enum
     */
    const F_STATUS = "status";
    /**
     * 目录
     * @var integer
     */
    const F_API_FOLDER_ID = "api_folder_id";
    /**
     * 负责人
     * @var integer
     */
    const F_PROJECT_MEMBER_ID = "project_member_id";
    /**
     * 说明
     * @var string
     */
    const F_COMMENT = "comment";
    /**
     * 
     * @var enum
     */
    const F_REQUESTBODYTYPE = "requestBodyType";
    /**
     * 
     * @var integer
     */
    const F_PROJECT_ID = "project_id";
    /**
     * 
     * @var integer
     */
    const F_INDEX = "index";
    /**
     * 
     * @var string
     */
    const F_CONTENT = "content";
    /**
     * 
     * @var integer
     */
    const F_MAJOR = "major";
    /**
     * 
     * @var integer
     */
    const F_MINOR = "minor";
    /**
     * 
     * @var integer
     */
    const F_REVISION = "revision";
    /**
     * 
     * @var integer
     */
    const F_VERSION = "version";
    /**
     * 
     * @var string
     */
    const F_COMMIT_MSG = "commit_msg";
    public static $columns = [
    'id'         => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'created_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'modified_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'is_deleted' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'uuid'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'name'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'method'     => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'path'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'status'     => ['type' => 'enum', 'null' => true,'length' => '','default'	=> ''],
      'api_folder_id' => ['type' => 'integer', 'null' => true,'length' => '','default'	=> ''],
      'project_member_id' => ['type' => 'integer', 'null' => true,'length' => '','default'	=> ''],
      'comment'    => ['type' => 'string', 'null' => true,'length' => '','default'	=> ''],
      'requestBodyType' => ['type' => 'enum', 'null' => false,'length' => '','default'	=> 'form-data'],
      'project_id' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'index'      => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'content'    => ['type' => 'string', 'null' => true,'length' => '','default'	=> ''],
      'major'      => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'minor'      => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'revision'   => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '1'],
      'version'    => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '1'],
      'commit_msg' => ['type' => 'string', 'null' => true,'length' => '145','default'	=> ''],
    ];
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
  'api_folder_id' => 'fk_web_api_api_folder1_idx',
  'project_member_id' => 'fk_web_api_project_member1_idx',
  'project_id' => 'fk_web_api_project1_idx',
);
    		
    
	private $api_folder;

	private $project;

	private $project_member;

	
	public function get_api_folder(){
		if( ! $this->api_folder){
			$this->api_folder = Api_Folder_Model::find_by_id($this->get(self::F_API_FOLDER_ID));
		}
		return $this->api_folder;
	}
	
	/**
	 * @return Web_Api_Model
	 */
	public function set_api_folder(Api_Folder_Model $new){
		$this->api_folder = $new;
		return $this;
	}

	public function get_project(){
		if( ! $this->project){
			$this->project = Project_Model::find_by_id($this->get(self::F_PROJECT_ID));
		}
		return $this->project;
	}
	
	/**
	 * @return Web_Api_Model
	 */
	public function set_project(Project_Model $new){
		$this->project = $new;
		return $this;
	}

	public function get_project_member(){
		if( ! $this->project_member){
			$this->project_member = Project_Member_Model::find_by_id($this->get(self::F_PROJECT_MEMBER_ID));
		}
		return $this->project_member;
	}
	
	/**
	 * @return Web_Api_Model
	 */
	public function set_project_member(Project_Member_Model $new){
		$this->project_member = $new;
		return $this;
	}

	
	public function get_status(){
		return ['develop','test','deprecated','released'];
	}
	public function get_requestBodyType(){
		return ['none','form-data','x-www-form-urlencoded','json','xml','raw','binary','GraphQL','msgpack'];
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
		case self::F_NAME: return "api名称";
		case self::F_METHOD: return "api方法";
		case self::F_PATH: return "请求路径";
		case self::F_STATUS: return "状态";
		case self::F_API_FOLDER_ID: return "目录";
		case self::F_PROJECT_MEMBER_ID: return "负责人";
		case self::F_COMMENT: return "说明";
		case self::F_REQUESTBODYTYPE: return "requestBodyType";
		case self::F_PROJECT_ID: return "project_id";
		case self::F_INDEX: return "index";
		case self::F_CONTENT: return "content";
		case self::F_MAJOR: return "major";
		case self::F_MINOR: return "minor";
		case self::F_REVISION: return "revision";
		case self::F_VERSION: return "version";
		case self::F_COMMIT_MSG: return "commit_msg";
    	default: return $column;
    	}
		return $column;
	}
}?>