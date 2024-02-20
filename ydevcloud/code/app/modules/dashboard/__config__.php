<?php
namespace app\dashboard;
use \yangzie\YZE_Base_Module as YZE_Base_Module;
/**
 *
 * @version $Id$
 * @package Dashboard
 */
class Dashboard_Module extends YZE_Base_Module{
    public $auths = "*";
    public $no_auths = array();
    protected function config(){
        return [
        'name'=>'Dashboard',
        'routers' => [
        	//'uri'	=> [
			//	'controller' => 'controller name',
			//	'action' => 'action name',
			// //通过$request->get_var('foo')获取
        	//	'args'	=> [
        	//		"foo" =>  "bar"
        	//	],
        	//],
        	]
        ];
    }
    public function js_bundle($bundle)
    {
       return ['/js/event.js'];
    }

    public function css_bundle($bundle)
    {
        // TODO: Implement css_bundle() method.
    }
}
?>
