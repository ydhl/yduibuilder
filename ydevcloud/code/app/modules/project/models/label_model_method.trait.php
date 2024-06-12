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
trait Label_Model_Method{
    // 这里实现model的业务方法
    public static function get_labels(YZE_Model $target){
        return Label_Model::from('l')
            ->left_join(Label_Target_Model::CLASS_NAME, 't', 't.label_id=l.id')
            ->where('l.is_deleted=0 and t.is_deleted=0 and t.target_class=:cls and t.target_id=:id')
            ->select([':cls'=>get_class($target),':id'=>$target->id], 'l');

    }
}?>
