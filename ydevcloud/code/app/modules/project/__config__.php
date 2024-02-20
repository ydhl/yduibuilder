<?php
namespace app\project;
use \yangzie\YZE_Base_Module as YZE_Base_Module;
/**
 *
 * @version $Id$
 * @package Project
 */
class Project_Module extends YZE_Base_Module{
    public $auths = "*";
    public $no_auths = array();
    protected function config(){
        return [
            'name'=>'Project',
            'routers' => [
                'project/(?P<pid>[^/]{36,})' => [
                    'controller' => 'project',
                ],
                'project/(?P<pid>[^/]+)/edit' => [
                    'controller' => 'add'
                ],
                'project/(?P<pid>[^/]+)/tech' => [
                    'controller' => 'tech'
                ],
                'project/(?P<pid>[^/]+)/transfer' => [
                    'controller' => 'transfer'
                ],
                'project/(?P<pid>[^/]+)/addmodule' => [
                    'controller' => 'module',
                    'action' => 'add'
                ],
                'project/(?P<pid>[^/]+)/homepage' => [
                    'controller' => 'homepage'
                ],
                'project/(?P<pid>[^/]+)/pageversion' => [
                    'controller' => 'pageversion'
                ],
                'project/(?P<pid>[^/]+)/structure' => [
                    'controller' => 'structure'
                ],
                'project/(?P<pid>[^/]+)/func' => [
                    'controller' => 'func'
                ],
                'project/(?P<pid>[^/]+)/page' => [
                    'controller' => 'page'
                ],
                'project/(?P<pid>[^/]+)/recycle' => [
                    'controller' => 'recycle'
                ],
                'project/(?P<pid>[^/]+)/recovery' => [
                    'controller' => 'recovery'
                ],
                'project/(?P<pid>[^/]+)/choosepage' => [
                    'controller' => 'structure',
                    'action' => 'choosepage'
                ],
                'project/(?P<pid>[^/]+)/lib' => [
                    'controller' => 'lib'
                ],
                'project/(?P<pid>[^/]+)/uicomponent' => [
                    'controller' => 'uicomponent'
                ],
                'project/(?P<pid>[^/]+)/icon' => [
                    'controller' => 'icon'
                ],
                'project/(?P<pid>[^/]+)/asset' => [
                    'controller' => 'asset'
                ],
                'project/(?P<pid>[^/]+)/asset/remove' => [
                    'controller' => 'asset',
                    'action'=>'remove'
                ],
                'project/(?P<pid>[^/]+)/upload' => [
                    'controller' => 'upload'
                ],
                'module/(?P<mid>[^/]+)' => [
                    'controller' => 'module',
                ],
                'module/(?P<mid>[^/]+)/edit' => [
                    'controller' => 'module',
                    'action' => 'add'
                ],
                'module/(?P<mid>[^/]+)/delete' => [
                    'controller' => 'module',
                    'action' => 'delete'
                ],
                'module/(?P<mid>[^/]+)/addfunction' => [
                    'controller' => 'function',
                    'action' => 'add'
                ],
                'function/(?P<fid>[^/]+)/edit' => [
                    'controller' => 'function',
                    'action' => 'add'
                ],
                'function/(?P<fid>[^/]+)/delete' => [
                    'controller' => 'function',
                    'action' => 'delete'
                ],
                'project/(?P<pid>[^/]+)/api' => [
                    'controller' => 'api',
                ],
                'project/(?P<pid>[^/]+)/build' => [
                    'controller' => 'build',
                ],
                'project/(?P<pid>[^/]+)/member' => [
                    'controller' => 'member',
                ],
                'project/(?P<pid>[^/]+)/member/join' => [
                    'controller' => 'member',
                    'action' => 'join'
                ],
                'project/(?P<pid>[^/]+)/member/disagree' => [
                    'controller' => 'member',
                    'action' => 'disagree'
                ],
                'project/(?P<pid>[^/]+)/invite-member' => [
                    'controller' => 'member',
                    'action' => 'invite',
                ],
                'project/(?P<pid>[^/]+)/remove-member' => [
                    'controller' => 'member',
                    'action' => 'remove',
                ],
                'project/(?P<pid>[^/]+)/setting' => [
                    'controller' => 'setting',
                ],
                'project/(?P<pid>[^/]+)/database' => [
                    'controller' => 'database',
                ],
                'project/(?P<pid>[^/]+)/delete' => [
                    'controller' => 'delete',
                ],
                'project/reverse/(?P<versionid>[^/]+)' => [
                    'controller' => 'reverse',
                ],
        	]
        ];
    }
    public function js_bundle($bundle)
    {
        return ['/js/module-functions.js'];
    }

    public function css_bundle($bundle)
    {
        // TODO: Implement css_bundle() method.
    }
}
?>
