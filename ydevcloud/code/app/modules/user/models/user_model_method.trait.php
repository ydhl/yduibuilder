<?php
namespace app\user;
use app\project\Project_Member_Model;
use app\project\Project_Model;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
/**
 *
 *
 * @version $Id$
 * @package user
 */
trait User_Model_Method{
    /**
     *
     * @param type $fromsite 那个网站
     * @param type $openid
     */
    public function findUserOfOpenid($fromsite, $openid){
        return self::from()->where("fromsite=:site and openid=:openid")->get_Single([":site"=>$fromsite, ":openid"=>$openid]);
    }

    /**
     * 自己参与的项目数量
     * @return int
     */
    public function getProjectCount(){
        return Project_Model::from('p')
            ->left_join(Project_Member_Model::CLASS_NAME, 'pm', 'pm.project_id = p.id')
            ->where("pm.user_id=:userid and p.is_deleted=0 and pm.is_deleted=0")
            ->count('id', [":userid"=>$this->id], 'p');
    }
    public function get_account_setting(){
        return json_decode(html_entity_decode($this->account_setting), true);
    }
    public function get_escape_cellphone(){
        return '****'.substr($this->cellphone,6);
    }
    public function name() {
        if ($this->nickname) return $this->nickname;
        if ($this->cellphone) return $this->get_escape_cellphone();
        if ($this->email) return $this->email;
        return $this->uuid;
    }
}?>
