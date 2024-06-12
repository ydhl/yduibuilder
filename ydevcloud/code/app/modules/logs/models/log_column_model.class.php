<?php
namespace app\logs;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use \app\logs\Log_Model;

/**
*
*
* @version $Id$
* @package logs
*/
class Log_Column_Model extends YZE_Model{

    const DB_TYPE_C = 'C';
    const DB_TYPE_R = 'R';
    const DB_TYPE_U = 'U';
    const DB_TYPE_D = 'D';
    const TABLE= "log_column";
    const VERSION = 'modified_on';
    const MODULE_NAME = "logs";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\logs\Log_Column_Model';

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
     * 字段名
     * @var string
     */
    const F_COLUMN = "column";
    /**
     * 原值
     * @var string
     */
    const F_OLD_VALUE = "old_value";
    /**
     *
     * @var string
     */
    const F_NEW_VALUE = "new_value";
    /**
     * 数据库操作类型
     * @var enum
     */
    const F_DB_TYPE = "db_type";
    /**
     *
     * @var string
     */
    const F_TABLE = "table";
    /**
     *
     * @var integer
     */
    const F_LOG_ID = "log_id";
    public static $columns = array(
               'id'         => array('type' => 'integer', 'null' => false,'length' => '11','default'	=> '',),
       'created_on' => array('type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP',),
       'modified_on' => array('type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP',),
       'is_deleted' => array('type' => 'integer', 'null' => false,'length' => '4','default'	=> '0',),
       'uuid'       => array('type' => 'string', 'null' => false,'length' => '45','default'	=> '',),
       'column'     => array('type' => 'string', 'null' => true,'length' => '45','default'	=> '',),
       'old_value'  => array('type' => 'string', 'null' => true,'length' => '','default'	=> '',),
       'new_value'  => array('type' => 'string', 'null' => true,'length' => '','default'	=> '',),
       'db_type'    => array('type' => 'enum', 'null' => true,'length' => '','default'	=> 'R',),
       'table'      => array('type' => 'string', 'null' => true,'length' => '45','default'	=> '',),
       'log_id'     => array('type' => 'integer', 'null' => false,'length' => '11','default'	=> '',),

    );
    //array('attr'=>array('from'=>'id','to'=>'id','class'=>'','type'=>'one-one||one-many') )
    //$this->attr
    protected $objects = array();
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
  'log_id' => 'fk_log_column_log1_idx',
);


	private $log;


	public function get_log(){
		if( ! $this->log){
			$this->log = Log_Model::find_by_id($this->get(self::F_LOG_ID));
		}
		return $this->log;
	}

	/**
	 * @return Log_Column_Model
	 */
	public function set_log(Log_Model $new){
		$this->log = $new;
		return $this;
	}

    public function get_db_type(){
        return [self::DB_TYPE_C,self::DB_TYPE_R,self::DB_TYPE_U,self::DB_TYPE_D];
    }

	public function get_db_type_string(){
	    switch ($this->db_type){
            case self::DB_TYPE_C: return '插入';
            case self::DB_TYPE_R: return '查询';
            case self::DB_TYPE_U: return '更新';
            case self::DB_TYPE_D: return '删除';
        }
        return $this->db_type;
    }

}?>
