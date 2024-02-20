<?php
namespace app\build;
use \yangzie\YZE_Base_Module as YZE_Base_Module;
/**
 *
 * @version $Id$
 * @package Build
 */
class Build_Module extends YZE_Base_Module{
    public $auths = "*";
    public $no_auths = array();
    protected function config(){
        return [
            'name'=>'Build',
            'routers' => [
                'preview/page/(?P<pageid>[^/]+)'	=> ['controller' => 'preview', 'action' => 'page'],
                'preview/(?P<pid>[^/]+)'	=> [ 'controller' => 'preview' ],
                'code/(?P<pid>[^/]+)'	=> [ 'controller' => 'code' ],
                'code/(?P<pid>[^/]+)/common'	=> [ 'controller' => 'code', 'action'=>'common' ],
                'code/page/(?P<pageid>[^/]+)'	=> ['controller' => 'code', 'action' => 'page'],
            ]
        ];
    }
    public function js_bundle($bundle)
    {
        return [];
    }

    public function css_bundle($bundle)
    {
        return ['/css/preview.css'];
    }
}
?>
