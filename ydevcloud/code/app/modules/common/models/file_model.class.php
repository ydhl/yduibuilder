<?php
namespace app\common;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use \app\project\Project_Model;

/**
 *
 *
 * @version $Id$
 * @package common
 */
class File_Model extends YZE_Model{
    use File_Model_Method;

    const TABLE= "file";
    const VERSION = 'modified_on';
    const MODULE_NAME = "common";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\common\File_Model';
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
    const F_FILE_NAME = "file_name";
    /**
     *
     * @var string
     */
    const F_URL = "url";
    /**
     *
     * @var integer
     */
    const F_FILE_SIZE = "file_size";
    /**
     *
     * @var date
     */
    const F_UPLOAD_DATE = "upload_date";
    /**
     *
     * @var string
     */
    const F_TYPE = "type";
    /**
     *
     * @var integer
     */
    const F_PROJECT_ID = "project_id";
    public static $columns = [
    'id'         => ['type' => 'integer', 'null' => false,'length' => '11','default'	=> ''],
      'created_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'modified_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'is_deleted' => ['type' => 'integer', 'null' => false,'length' => '4','default'	=> '0'],
      'uuid'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'file_name'  => ['type' => 'string', 'null' => true,'length' => '145','default'	=> ''],
      'url'        => ['type' => 'string', 'null' => true,'length' => '545','default'	=> ''],
      'file_size'  => ['type' => 'integer', 'null' => true,'length' => '11','default'	=> ''],
      'upload_date' => ['type' => 'date', 'null' => true,'length' => '','default'	=> ''],
      'type'       => ['type' => 'string', 'null' => true,'length' => '45','default'	=> ''],
      'project_id' => ['type' => 'integer', 'null' => false,'length' => '11','default'	=> ''],
    ];
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
  'project_id' => 'fk_file_project1_idx',
);


	private $project;


	public function get_project(){
		if( ! $this->project){
			$this->project = Project_Model::find_by_id($this->get(self::F_PROJECT_ID));
		}
		return $this->project;
	}

	/**
	 * @return File_Model
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
		case self::F_FILE_NAME: return "file_name";
		case self::F_URL: return "url";
		case self::F_FILE_SIZE: return "file_size";
		case self::F_UPLOAD_DATE: return "upload_date";
		case self::F_TYPE: return "type";
		case self::F_PROJECT_ID: return "project_id";
    	default: return $column;
    	}
		return $column;
	}
}?>
