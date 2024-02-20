<?php
namespace app\front;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use \yangzie\YZE_JSON_View;

/**
*
* @version $Id$
* @package front
*/
class Index_Controller extends YZE_Resource_Controller {
    /**
     * @ignoreLog
     */
    public function index(){
        $request = $this->request;
        $this->layout = 'front';
        $this->set_view_data('yze_page_title', 'UI Prototype IS Front Code');
    }

    public function exception(\Exception $e){
        $request = $this->request;
        $this->layout = 'error';
        //Post 请求或者返回json接口时，出错返回json错误结果
        $format = $request->get_output_format();
        if (!$request->is_get() || strcasecmp ( $format, "json" )==0){
        	$this->layout = '';
        	return YZE_JSON_View::error($this, $e->getMessage());
        }
    }
}
?>