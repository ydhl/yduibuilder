<?php
namespace app\common;
use \yangzie\YZE_Base_Module as YZE_Base_Module;
/**
 *
 * @version $Id$
 * @package Common
 */
class Common_Module extends YZE_Base_Module{
    public $auths = array('download'=>'index');
    public $no_auths = array();
    protected function config(){
        return [
            'name'=>'Common',
            'routers' => [
                'signin/code'	=> ['controller' => 'index'],
                'signout'=> ['controller' => 'index', 'action'=>'signout'],
                'signin'=> ['controller' => 'index', 'action'=>'signin'],
                'sendcode'=> ['controller' => 'index', 'action'=>'sendcode'],
                'bind'	=> ['controller' => 'bind'],
                'download'	=> ['controller' => 'download'],
                'image'	=> ['controller' => 'download', 'action'=>'image'],
                'sms'	=> ['controller' => 'bind', 'action'=>'sms'],
            ]
        ];
    }
    public function js_bundle($bundle)
    {
        return ['/js/common.js'];
    }

    public function css_bundle($bundle)
    {
        return ['/css/common.css'];
    }
}
?>
