<?php
namespace app\front;
use \yangzie\YZE_Base_Module as YZE_Base_Module;
/**
 *
 * @version $Id$
 * @package Front
 */
class Front_Module extends YZE_Base_Module{
    public $auths = array();
    public $no_auths = array();
    protected function config(){
        return [
        'name'=>'Front',
        'routers' => [
        	'not-support-browser' => [
				'controller' => 'not_support_browser'
        	    ],
            '' => [
                'controller' => 'index',
                'action' => 'index'
            ]
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
