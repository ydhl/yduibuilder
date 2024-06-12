<?php
namespace app\test;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use \yangzie\YZE_JSON_View;

/**
*
* @version $Id$
* @package test
*/
class Student_Controller extends YZE_Resource_Controller {
    public function detail(){
        $request = $this->request;
        $this->layout = '';
        $uuid = trim($request->get_from_get("uuid"));
        $gender = intval($request->get_from_get("gender"));
        if ($gender) {
            return YZE_JSON_View::success($this, ['name'=>'测试用户'.$uuid,'class'=>'测试班级', 'course'=>['语文','数学','物理','化学']]);
        }
        return YZE_JSON_View::error($this, "用户不存在");
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = '';
        return YZE_JSON_View::error($this, $e->getMessage());
    }
}
?>
