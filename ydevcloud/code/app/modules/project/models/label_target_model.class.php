<?php
namespace app\project;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use \app\project\Label_Model;

/**
 *
 *
 * @version $Id$
 * @package project
 */
class Label_Target_Model extends YZE_Model{
    use Label_Target_Model_Method;
    
    const TABLE= "label_target";
    const VERSION = 'modified_on';
    const MODULE_NAME = "project";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\project\Label_Target_Model';
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
    const F_LABEL_ID = "label_id";
    /**
     * 
     * @var string
     */
    const F_TARGET_CLASS = "target_class";
    /**
     * 
     * @var string
     */
    const F_TARGET_ID = "target_id";
    public static $columns = [
    'id'         => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'created_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'modified_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'is_deleted' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'uuid'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'label_id'   => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'target_class' => ['type' => 'string', 'null' => true,'length' => '45','default'	=> ''],
      'target_id'  => ['type' => 'string', 'null' => true,'length' => '45','default'	=> ''],
    ];
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
  'label_id' => 'fk_category2_label1_idx',
);
    		
    
	private $label;

	
	public function get_label(){
		if( ! $this->label){
			$this->label = Label_Model::find_by_id($this->get(self::F_LABEL_ID));
		}
		return $this->label;
	}
	
	/**
	 * @return Label_Target_Model
	 */
	public function set_label(Label_Model $new){
		$this->label = $new;
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
		case self::F_LABEL_ID: return "label_id";
		case self::F_TARGET_CLASS: return "target_class";
		case self::F_TARGET_ID: return "target_id";
    	default: return $column;
    	}
		return $column;
	}
}?>