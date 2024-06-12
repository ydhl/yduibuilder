<?php
namespace app\project;
use app\vendor\Env;
use \yangzie\YZE_Model;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use function yangzie\__;

trait Project_Setting_Model_Method{

    public static function set_setting_value($project_id, $name, $value){
        $sql = new YZE_SQL();
        $sql->from(Project_Setting_Model::CLASS_NAME,'ps')
            ->where('ps', 'project_id', '=', $project_id)
            ->where('ps', 'name', '=', $name)->select('ps', ['id']);

        $setting = new Project_Setting_Model();
        $setting->set('uuid', Project_Setting_Model::uuid())
            ->set('project_id', $project_id)
            ->set('name', $name)
            ->set('value', $value)
            ->save(YZE_SQL::INSERT_NOT_EXIST_OR_UPDATE, $sql);
        return $setting;
    }
    public static function get_setting_value($project_id, $name){
        $model = Project_Setting_Model::from()
            ->where('name=:name and project_id=:pid')
            ->get_Single([':name'=>$name,':pid'=>$project_id]);
        return $model ? json_decode(html_entity_decode($model->value), true) : null;
    }
}?>
