<?php
namespace app\project;
use \yangzie\YZE_Model;


/**
 *
 *
 * @version $Id$
 * @package project
 */
class Api_Folder_Model extends YZE_Model{
    use Api_Folder_Model_Method;

    const TABLE= "api_folder";
    const VERSION = 'modified_on';
    const MODULE_NAME = "project";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\project\Api_Folder_Model';
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
     * 目录名
     * @var string
     */
    const F_NAME = "name";
    /**
     * 上级目录
     * @var integer
     */
    const F_API_FOLDER_ID = "api_folder_id";
    /**
     * 备注
     * @var string
     */
    const F_COMMENT = "comment";
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
    public static $columns = [
    'id'         => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'created_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'modified_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'is_deleted' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'uuid'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'name'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'api_folder_id' => ['type' => 'integer', 'null' => true,'length' => '','default'	=> ''],
      'comment'    => ['type' => 'string', 'null' => true,'length' => '145','default'	=> ''],
      'project_id' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'index'      => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
    ];
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
  'api_folder_id' => 'fk_api_folder_api_folder1_idx',
  'project_id' => 'fk_api_folder_project1_idx',
);


	private $api_folder;

	private $project;


	public function get_api_folder(){
		if( ! $this->api_folder){
			$this->api_folder = Api_Folder_Model::find_by_id($this->get(self::F_API_FOLDER_ID));
		}
		return $this->api_folder;
	}

	/**
	 * @return Api_Folder_Model
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
	 * @return Api_Folder_Model
	 */
	public function set_project(Project_Model $new){
		$this->project = $new;
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
		case self::F_NAME: return "目录名";
		case self::F_API_FOLDER_ID: return "上级目录";
		case self::F_COMMENT: return "备注";
		case self::F_PROJECT_ID: return "project_id";
		case self::F_INDEX: return "index";
    	default: return $column;
    	}
		return $column;
	}
}?>
