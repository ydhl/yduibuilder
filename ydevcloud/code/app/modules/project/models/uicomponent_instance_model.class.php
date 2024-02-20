<?php
namespace app\project;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use \app\project\Page_Model;
use project;

/**
 *
 *
 * @version $Id$
 * @package project
 */
class Uicomponent_Instance_Model extends YZE_Model{
    use Uicomponent_Instance_Model_Method;

    const TABLE= "uicomponent_instance";
    const VERSION = 'modified_on';
    const MODULE_NAME = "project";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\project\Uicomponent_Instance_Model';
    /**
     * @see YZE_Model::$encrypt_columns
     */
    public $encrypt_columns = array();

    /**
     * Ui 组件实例
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
     * 实例页面
     * @var integer
     */
    const F_PAGE_ID = "page_id";
    /**
     * 组件
     * @var integer
     */
    const F_UICOMPONENT_PAGE_ID = "uicomponent_page_id";
    /**
     * 实例uiid
     * @var string
     */
    const F_INSTANCE_UUID = "instance_uuid";
    public static $columns = [
    'id'         => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'created_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'modified_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'is_deleted' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'uuid'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'page_id'    => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'uicomponent_page_id' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'instance_uuid' => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
    ];
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
  'page_id' => 'fk_uicomponent_instance_page1_idx',
  'uicomponent_page_id' => 'fk_uicomponent_instance_page2_idx',
);


	private $page;

	private $ui_page;


	public function get_page(){
		if( ! $this->page){
			$this->page = Page_Model::find_by_id($this->get(self::F_PAGE_ID));
		}
		return $this->page;
	}

	/**
	 * @return Uicomponent_Instance_Model
	 */
	public function set_page(Page_Model $new){
		$this->page = $new;
		return $this;
	}

	public function get_ui_component(){
		if( ! $this->ui_page){
			$this->ui_page = Page_Model::find_by_id($this->get(self::F_UICOMPONENT_PAGE_ID));
		}
		return $this->ui_page;
	}

	/**
	 * @return Uicomponent_Instance_Model
	 */
	public function set_ui_component(Page_Model $new){
		$this->ui_page = $new;
		return $this;
	}


	/**
	 * 返回每个字段的具体的面向用户可读的含义，比如login_name表示登录名
	 * @param $column
	 * @return mixed
	 */
    public function get_column_mean($column){
    	switch ($column){
    	case self::F_ID: return "Ui 组件实例";
		case self::F_CREATED_ON: return "created_on";
		case self::F_MODIFIED_ON: return "modified_on";
		case self::F_IS_DELETED: return "is_deleted";
		case self::F_UUID: return "uuid";
		case self::F_PAGE_ID: return "实例页面";
		case self::F_UICOMPONENT_PAGE_ID: return "组件";
		case self::F_INSTANCE_UUID: return "实例uiid";
    	default: return $column;
    	}
		return $column;
	}
}?>
