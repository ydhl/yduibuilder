<?php
namespace app\project;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;

/**
 *
 *
 * @version $Id$
 * @package project
 */
class User_Model extends YZE_Model{
    use User_Model_Method;
    
    const TABLE= "user";
    const VERSION = 'modified_on';
    const MODULE_NAME = "project";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\project\User_Model';
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
    const F_OPENID = "openid";
    /**
     * 
     * @var string
     */
    const F_FROMSITE = "fromsite";
    /**
     * 
     * @var string
     */
    const F_AVATAR = "avatar";
    /**
     * 
     * @var string
     */
    const F_NICKNAME = "nickname";
    /**
     * 
     * @var string
     */
    const F_CELLPHONE = "cellphone";
    /**
     * 
     * @var string
     */
    const F_SSO_TOKEN = "sso_token";
    /**
     * 
     * @var date
     */
    const F_SSO_TOKEN_EXPIRE = "sso_token_expire";
    /**
     * 
     * @var string
     */
    const F_PHONE_REGION = "phone_region";
    /**
     * 
     * @var string
     */
    const F_EMAIL = "email";
    /**
     * 
     * @var string
     */
    const F_LOGIN_PWD = "login_pwd";
    /**
     * 
     * @var string
     */
    const F_USER_TYPE = "user_type";
    /**
     * 到期时间，null表示不过期
     * @var date
     */
    const F_ACCOUNT_DUEDATE = "account_duedate";
    /**
     * 账户设置
     * @var string
     */
    const F_ACCOUNT_SETTING = "account_setting";
    /**
     * 
     * @var string
     */
    const F_ACCOUNT_TYPE = "account_type";
    public static $columns = [
    'id'         => ['type' => 'integer', 'null' => false,'length' => '','default'	=> ''],
      'created_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'modified_on' => ['type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP'],
      'is_deleted' => ['type' => 'integer', 'null' => false,'length' => '','default'	=> '0'],
      'uuid'       => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'openid'     => ['type' => 'string', 'null' => true,'length' => '45','default'	=> ''],
      'fromsite'   => ['type' => 'string', 'null' => true,'length' => '145','default'	=> ''],
      'avatar'     => ['type' => 'string', 'null' => true,'length' => '145','default'	=> ''],
      'nickname'   => ['type' => 'string', 'null' => true,'length' => '45','default'	=> ''],
      'cellphone'  => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'sso_token'  => ['type' => 'string', 'null' => true,'length' => '45','default'	=> ''],
      'sso_token_expire' => ['type' => 'date', 'null' => true,'length' => '','default'	=> ''],
      'phone_region' => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'email'      => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'login_pwd'  => ['type' => 'string', 'null' => false,'length' => '45','default'	=> ''],
      'user_type'  => ['type' => 'string', 'null' => false,'length' => '45','default'	=> 'individual'],
      'account_duedate' => ['type' => 'date', 'null' => true,'length' => '','default'	=> ''],
      'account_setting' => ['type' => 'string', 'null' => true,'length' => '1000','default'	=> ''],
      'account_type' => ['type' => 'string', 'null' => false,'length' => '45','default'	=> 'base'],
    ];
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
);
    		
    
	
	
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
		case self::F_OPENID: return "openid";
		case self::F_FROMSITE: return "fromsite";
		case self::F_AVATAR: return "avatar";
		case self::F_NICKNAME: return "nickname";
		case self::F_CELLPHONE: return "cellphone";
		case self::F_SSO_TOKEN: return "sso_token";
		case self::F_SSO_TOKEN_EXPIRE: return "sso_token_expire";
		case self::F_PHONE_REGION: return "phone_region";
		case self::F_EMAIL: return "email";
		case self::F_LOGIN_PWD: return "login_pwd";
		case self::F_USER_TYPE: return "user_type";
		case self::F_ACCOUNT_DUEDATE: return "到期时间，null表示不过期";
		case self::F_ACCOUNT_SETTING: return "账户设置";
		case self::F_ACCOUNT_TYPE: return "account_type";
    	default: return $column;
    	}
		return $column;
	}
}?>