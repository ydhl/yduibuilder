<?php
namespace app\user;
use \yangzie\YZE_Base_Module as YZE_Base_Module;
/**
 *
 * @version $Id$
 * @package User
 */
class User_Module extends YZE_Base_Module{
    public $auths = "*";
    public $no_auths = array();
	protected function config(){
		return array(
			'name'	=> 'User',
			'routers'	=> array(
				'profile'	=> array(
					'controller'	=> 'profile',
				),
				'profile/signinpwd'	=> array(
					'controller'	=> 'profile',
					'action'	=> 'signinpwd',
				),
				'profile/email'	=> array(
					'controller'	=> 'profile',
					'action'	=> 'email',
				),
				'profile/cellphone'	=> array(
					'controller'	=> 'profile',
					'action'	=> 'cellphone',
				),
				'account'	=> array(
					'controller'	=> 'account',
				)
			),
		);
	}
    public function js_bundle($bundle)
    {
        return ['/js/profile.js'];
    }

    public function css_bundle($bundle)
    {
        // TODO: Implement css_bundle() method.
    }
}
?>
