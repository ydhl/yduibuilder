<?php
namespace app\uicomponent;
use \yangzie\YZE_Base_Module as YZE_Base_Module;
/**
 *
 * @version $Id$
 * @package uicomponent
 */
class Uicomponent_Module extends YZE_Base_Module{
    public $auths = "*";
    public $no_auths = array();
    protected function config(){
        return [
            'name'=>'Uicomponent',
            'routers' => [
        	    'myuicomponent' => ['controller' => 'index'],
        	]
        ];
    }
    public function js_bundle($bundle)
    {
        return ['/js/module-uicomponent.js'];
    }

    public function css_bundle($bundle)
    {
        // TODO: Implement css_bundle() method.
    }
}
?>
