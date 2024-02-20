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
trait Page_Bind_Style_Model_Method{
    // 这里实现model的业务方法

    /**
     * 删除那些在页面上已经删除掉的ui绑定
     * @param $page
     * @return void
     */
    public static function remove_gone_uiid(Page_Model $page) {
        $bounds = Page_Bind_Style_Model::from()->where('page_id=:pid')->select([':pid'=>$page->id]);
        foreach ($bounds as $bound){
            if(!$page->find_ui_item($bound->uiid)){
                $bound->remove();
            }
        }
    }
}?>
