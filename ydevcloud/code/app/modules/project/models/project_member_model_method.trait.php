<?php
namespace app\project;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use function yangzie\__;

/**
 *
 *
 * @version $Id$
 * @package project
 */
trait Project_Member_Model_Method{
    private $last_page;

    public function get_last_page(){
        if( ! $this->last_page){
            $this->last_page = Page_Model::find_by_id($this->get(self::F_LAST_PAGE_ID));
        }
        return $this->last_page;
    }
    /**
     * 是否能编辑项目，指能新增，修改项目的代码和界面
     * @return bool
     */
    public function can_edit()
    {
        return  in_array($this->role, [Project_Member_Model::ROLE_ADMIN, Project_Member_Model::ROLE_DEVELOPER]);
    }
    public function get_role_desc() {
        switch ($this->role) {
            case self::ROLE_ADMIN: return __('Admin');
            case self::ROLE_DEVELOPER: return __('Developer');
            case self::ROLE_REPORTER: return __('Reporter');
            case self::ROLE_GUEST: return __('Guest');
            default: return $this->role;
        }
    }
    public function remove(){
        try{
            parent::remove();
        }catch (\Exception $e){
            $this->set('is_deleted', 1)->save();
        }
    }
}?>
