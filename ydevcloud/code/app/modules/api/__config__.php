<?php
namespace app\api;
use \yangzie\YZE_Base_Module as YZE_Base_Module;
/**
 *
 * @version $Id$
 * @package Api
 */
class Api_Module extends YZE_Base_Module{
    /**
     * @see YZE_Base_Module::$auths
     * @var string[]
     */
    public $auths = "*";
    /**
     * @see YZE_Base_Module::$no_auths
     * @var string[]
     */
    public $no_auths = ['sso'=>'post_index','icon'=>'*', 'screenshot'=>'*'];
    protected function config(){
        return [
            'name'=>'Api',
            'routers' => [
                'api/(?P<pid>[^/]+)/upload' => [
                    'controller' => 'upload',
                ],
                'api/(?P<pid>[^/]+)/icon' => [
                    'controller' => 'icon',
                ],
                'api/(?P<pid>[^/]+)/file' => [
                    'controller' => 'file',
                ],
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
