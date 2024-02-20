<?php
namespace app\project;
use app\vendor\Env;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use function yangzie\__;

trait Project_Setting_Model_Method{

    public static function get_setting_value($project_id, $name){
        $model = Project_Setting_Model::from()
            ->where('name=:name and project_id=:pid')
            ->get_Single([':name'=>$name,':pid'=>$project_id]);
        return $model ? json_decode(html_entity_decode($model->value), true) : null;
    }
}?>
