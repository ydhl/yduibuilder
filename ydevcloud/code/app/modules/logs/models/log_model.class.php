<?php
namespace app\logs;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;

/**
*
*
* @version $Id$
* @package logs
*/
class Log_Model extends YZE_Model{

    const TABLE= "log";
    const VERSION = 'modified_on';
    const MODULE_NAME = "logs";
    const KEY_NAME = "id";
    const CLASS_NAME = 'app\logs\Log_Model';

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
     * 用户名
     * @var string
     */
    const F_USER_NAME = "user_name";
    /**
     * 系统中的用户id
     * @var integer
     */
    const F_USER_ID = "user_id";
    /**
     * 操作时间
     * @var date
     */
    const F_ACTION_TIME = "action_time";
    /**
     * 操作, 如新增用户
     * @var string
     */
    const F_ACTION_NAME = "action_name";
    /**
     * 请求的方法，如post,get
     * @var string
     */
    const F_REQUEST_METHOD = "request_method";
    /**
     * 访问地址
     * @var string
     */
    const F_REQUEST_URL = "request_url";
    /**
     * 终端信息，如浏览器，操作系统
     * @var string
     */
    const F_CLIENT_INFO = "client_info";
    /**
     * 终端ip
     * @var string
     */
    const F_CLIENT_IP = "client_ip";
    public static $columns = array(
               'id'         => array('type' => 'integer', 'null' => false,'length' => '11','default'	=> '',),
       'created_on' => array('type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP',),
       'modified_on' => array('type' => 'date', 'null' => false,'length' => '','default'	=> 'CURRENT_TIMESTAMP',),
       'is_deleted' => array('type' => 'integer', 'null' => false,'length' => '4','default'	=> '0',),
       'uuid'       => array('type' => 'string', 'null' => false,'length' => '45','default'	=> '',),
       'user_name'  => array('type' => 'string', 'null' => true,'length' => '45','default'	=> '',),
       'user_id'    => array('type' => 'integer', 'null' => true,'length' => '11','default'	=> '',),
       'action_time' => array('type' => 'date', 'null' => true,'length' => '','default'	=> '',),
       'action_name' => array('type' => 'string', 'null' => true,'length' => '45','default'	=> '',),
       'request_method' => array('type' => 'string', 'null' => true,'length' => '45','default'	=> '',),
       'request_url' => array('type' => 'string', 'null' => true,'length' => '245','default'	=> '',),
       'client_info' => array('type' => 'string', 'null' => true,'length' => '245','default'	=> '',),
       'client_ip'  => array('type' => 'string', 'null' => true,'length' => '45','default'	=> '',),

    );
    //array('attr'=>array('from'=>'id','to'=>'id','class'=>'','type'=>'one-one||one-many') )
    //$this->attr
    protected $objects = array();
    /**
     * @see YZE_Model::$unique_key
     */
    protected $unique_key = array (
  'id' => 'PRIMARY',
);

    public function get_affected_table(){
        $sql = "select count(`table`) as c, db_type from log_column where log_id=".intval($this->id)." group by db_type, `table`";
        $rst = YZE_DBAImpl::get_instance()->native_Query($sql);
        $data = [];
        $map = [
            Log_Column_Model::DB_TYPE_C => '插入',
            Log_Column_Model::DB_TYPE_U => '更新',
            Log_Column_Model::DB_TYPE_D => '删除',
            Log_Column_Model::DB_TYPE_R => '查询',
        ];
        while ($rst->next()){
            $data[$map[$rst->f("db_type")]] += $rst->f("c");
        }
        $str = '';
        foreach ($data as $action => $number){
            $str .= $action.$number."项 ";
        }
        return $str;
    }

    private function get_broswer()
    {
        $sys = $this->client_info;
        if (stripos($sys, "Firefox/") > 0) {
            preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
            $exp[0] = "Firefox";
            $exp[1] = $b[1];    //获取火狐浏览器的版本号
        } elseif (stripos($sys, "Maxthon") > 0) {
            preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);
            $exp[0] = "傲游";
            $exp[1] = $aoyou[1];
        } elseif (stripos($sys, "MSIE") > 0) {
            preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
            $exp[0] = "IE";
            $exp[1] = $ie[1];  //获取IE的版本号
        } elseif (stripos($sys, "OPR") > 0) {
            preg_match("/OPR\/([\d\.]+)/", $sys, $opera);
            $exp[0] = "Opera";
            $exp[1] = $opera[1];
        } elseif (stripos($sys, "Edg") > 0) {
            //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
            preg_match("/Edge|Edg\/([\d\.]+)/", $sys, $Edge);
            $exp[0] = "Edge";
            $exp[1] = $Edge[1];
        } elseif (stripos($sys, "Chrome") > 0) {
            preg_match("/Chrome\/([\d\.]+)/", $sys, $google);
            $exp[0] = "Chrome";
            $exp[1] = $google[1];  //获取google chrome的版本号
        } elseif (stripos($sys, 'rv:') > 0 && stripos($sys, 'Gecko') > 0) {
            preg_match("/rv:([\d\.]+)/", $sys, $IE);
            $exp[0] = "IE";
            $exp[1] = $IE[1];
        } else {
            $exp[0] = "未知浏览器";
            $exp[1] = "";
        }
        return $exp[0] . '(' . $exp[1] . ')';
    }
    private function get_os()
    {
        $agent = $this->client_info;
        if (preg_match('/win/i', $agent) && strpos($agent, '95')) {
            return 'Windows 95';
        }
        if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')) {
            return 'Windows ME';
        }
        if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)) {
            return 'Windows 98';
        }
        if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent)) {
            return 'Windows Vista';
        }
        if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent)) {
            return 'Windows 7';
        }
        if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent)) {
            return 'Windows 8';
        }
        if (preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent)) {
            return 'Windows 10';#添加win10判断
        }
        if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)) {
            return 'Windows XP';
        }
        if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)) {
            return 'Windows 2000';
        }
        if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)) {
            return 'Windows NT';
        }
        if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)) {
            return 'Windows 32';
        }
        if (preg_match('/linux/i', $agent)) {
            return 'Linux';
        }
        if (preg_match('/unix/i', $agent)) {
            return 'Unix';
        }
        if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)) {
            return 'SunOS';
        }
        if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)) {
            return 'IBM OS/2';
        }
        if (preg_match('/Mac/i', $agent)) {
            return 'Mac';
        }
        if (preg_match('/PowerPC/i', $agent)) {
            return 'PowerPC';
        }
        if (preg_match('/AIX/i', $agent)) {
            return 'AIX';
        }
        if (preg_match('/HPUX/i', $agent)) {
            return 'HPUX';
        }
        if (preg_match('/NetBSD/i', $agent)) {
            return 'NetBSD';
        }
        if (preg_match('/BSD/i', $agent)) {
            return 'BSD';
        }
        if (preg_match('/OSF1/i', $agent)) {
            return 'OSF1';
        }
        if (preg_match('/IRIX/i', $agent)) {
            return 'IRIX';
        }
        if (preg_match('/FreeBSD/i', $agent)) {
            return 'FreeBSD';
        }
        if (preg_match('/teleport/i', $agent)) {
            return 'teleport';
        }
        if (preg_match('/flashget/i', $agent)) {
            return 'flashget';
        }
        if (preg_match('/webzip/i', $agent)) {
            return 'webzip';
        }
        if (preg_match('/offline/i', $agent)) {
            return 'offline';
        }
        return '未知操作系统';
    }
    public function get_client_info(){
        return $this->get_os()." ".$this->get_broswer();
    }

    public function get_items() {
        return Log_Column_Model::from()->where("log_id=:id and is_deleted=0")->order_By("id", "ASC")->select([":id"=>$this->id]);
    }

    public function remove()
    {
        Log_Column_Model::from()->where('log_id=:id')->delete([':id'=>$this->id]);
        return parent::remove();
    }
}?>
