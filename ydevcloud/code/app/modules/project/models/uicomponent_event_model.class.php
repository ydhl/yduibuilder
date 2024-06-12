<?php
namespace app\project;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use \app\project\Page_Model;
/**
 *
 *
 * @version $Id$
 * @package project
 */
class Uicomponent_Event_Model extends YZE_Model{
    use Uicomponent_Event_Model_Method;
    
    const TABLE= "uicomponent_event";
    const MODULE_NAME = "project";
    const KEY_NAME = "id";
	const UUID_NAME = "uuid";
    const CLASS_NAME = 'app\project\Uicomponent_Event_Model';
    /**
	 * model 所在的数据库名
	 */
	const DB_NAME = "ydecloud";
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
     * 事件名
     * @var string
     */
    const F_NAME = "name";
    /**
     * 输入参数
     * @var string
     */
    const F_ARGS = "args";
    /**
     * 
     * @var string
     */
    const F_DESC = "desc";
    public static $columns = [
    'id'         => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'created_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'modified_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'is_deleted' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'uuid'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'page_id'    => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'name'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'args'       => ['type' => 'string', 'null' => true,'length' => '','default'	=> ''],
      'desc'       => ['type' => 'string', 'null' => true,'length' => '145','default'	=> ''],
    ];
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
  'page_id' => 'fk_uicomponent_event_page1_idx',
);

    /**
     * @see YZE_Model::$relation_column
     */
    protected $relation_column = array (
  'page_id' => 
  array (
    'graphql_field' => 'page',
    'target_class' => '\\app\\project\\Page_Model',
    'target_column' => 'id',
  ),
);
    		
    
	private $page;
	
	public function get_page($suffix=null){
		if( ! $this->page){
			$this->page = Page_Model::find_by_id($this->get(self::F_PAGE_ID), $this->db, $suffix);
		}
		return $this->page;
	}

	/**
	 * @return Uicomponent_Event_Model
	 */
	public function set_page(Page_Model $new){
		$this->page = $new;
		return $this;
	}

	

}?>