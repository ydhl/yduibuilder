<?php
namespace app\parallax;
use \yangzie\YZE_Base_Module as YZE_Base_Module;
/**
 *
 * @version $Id$
 * @package Parallax
 */
class Parallax_Module extends YZE_Base_Module{
    public $auths = array();
    public $no_auths = array();
    protected function config(){
        return [
        'name'=>'Parallax',
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
        // TODO: Implement js_bundle() method.
    }

    public function css_bundle($bundle)
    {
        // TODO: Implement css_bundle() method.
    }
}
?>
