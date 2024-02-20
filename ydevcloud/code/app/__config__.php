<?php
namespace  app;

use yangzie\YZE_FatalException;
use function yangzie\yze_js_bundle;

/**
 * 指定上传目录
 */
define("YZE_UPLOAD_PATH", YZE_APP_PATH. "public_html".DS."upload".DS);
/**
 * 数据库类型
 */
define("YZE_DB_TYPE",  "MYSQL");
/**
 * MYSQL数据库用户名
 */
define("YZE_DB_USER",  "root");
/**
 * MYSQL数据库主地址
 */
define("YZE_DB_HOST_M",  "127.0.0.1");
/**
 * MYSQL数据库名
 */
define("YZE_DB_DATABASE",  "ydecloud_os");
/**
 * MYSQL端口
 */
define("YZE_DB_PORT",  "3306");
/**
 * MYSQL密码
 */
define("YZE_DB_PASS",  "12345678");
/**
 * MYSQL加解密的秘钥
 */
define("YZE_DB_CRYPT_KEY",  "");
/**
 * 网站地址
 */
define("SITE_URI", "http://ydecloud-os.local.com/");
/**
 * 上传内容的访问地址，如果有cdn，填写cdn地址
 */
define("UPLOAD_SITE_URI", "http://ydecloud-os.local.com/upload/");
define("UI_BUILDER_URI", "http://localhost:9999/");
// Build SOCKET_HOST
define("SOCKET_HOST", "ws://localhost:8888");

/**
 * 开发环境true还是生产环境（false）
 */
define("YZE_DEVELOP_MODE",  false );
/**
 * 错误报告级别
 */
ini_set('error_reporting', E_ALL & ~E_STRICT & ~E_DEPRECATED & ~E_NOTICE);
/**
 * 时区
 */
date_default_timezone_set('Asia/Chongqing');
/**
 * 应用名
 */
define("APPLICATION_NAME", "YDE Cloud");
/**
 * 是否是session less应用，session less将不开启session功能
 */
define("SESSIONLESS", false);

// Rabbitmq 配置
define("RABBITMQ_HOST", "localhost");
define("RABBITMQ_PORT", "5672");
define("RABBITMQ_USER", "guest");
define("RABBITMQ_PWD", "guest");


define('VERSION', '1.0.0');

/**
 * app模块配置
 *
 * @author leeboo
 *
 */
class App_Module extends \yangzie\YZE_Base_Module{

	//数据库配置
	public $db_user = YZE_DB_USER;
	public $db_host= YZE_DB_HOST_M;
	public $db_name= YZE_DB_DATABASE;
	public $db_port = YZE_DB_PORT;
	public $db_psw= YZE_DB_PASS;
	public $db_charset= 'UTF8';

	/**
	 * App 访问时做一些检查，比如php的版本
	 * @return bool|void
	 * @throws YZE_FatalException
	 */
	public function check(){
		if( version_compare(PHP_VERSION,'7.1.0','lt')){
			throw new YZE_FatalException("要求7.1及以上PHP版本");
		}
		if(! extension_loaded('sockets')){
			throw new YZE_FatalException("未安装sockets模块");
		}
		if(! extension_loaded('openssl')){
			throw new YZE_FatalException("未安装openssl模块");
		}
		if(! extension_loaded('zip')){
			throw new YZE_FatalException("未安装zip模块");
		}
		if(! extension_loaded('xml')){
			throw new YZE_FatalException("未安装xml模块");
		}
		if(! extension_loaded('gd')){
			throw new YZE_FatalException("未安装gd2模块");
		}
	}

	protected function config()
	{
		//动态返回配置
		return array();
	}

	/**
	 * 应用启动时需要加载的文件，如果指定目录，则自动包含里面的所有文件,
	 * 但要注意是按文件名排序顺序包含的，如果被包含的文件之间有依赖关系，这会导致代码错误，这种情况请手动添加包含的文件
	 */
	public function module_include_files() {
        $files = [
			"app/vendor/pomo/translation_entry.class.php",
			"app/vendor/pomo/pomo_stringreader.class.php",
			"app/vendor/pomo/pomo_cachedfilereader.class.php",
			"app/vendor/pomo/pomo_cachedIntfilereader.class.php",
			"app/vendor/pomo/translations.class.php",
			"app/vendor/pomo/gettext_translations.class.php",
			"app/vendor/pomo/mo.class.php",
			"app/vendor/ydhttp.php",
			"app/vendor/util.php",
			"app/vendor/uploader.class.php",
			"vendor/autoload.php",
		];

        return $files;
	}

	/**
	 * js资源分组，在加载时方便直接通过分组名加载;
	 * 资源路径以web 绝对路径/开始，/指的上public_html目录
	 * 在layouts中通过接口yze_js_bundle("yangzie,foo,bar")一次打包加载这里指定的资源
	 * @return array(资源路径1，资源路径2)
	 */
	public function js_bundle($bundle){
		$config = ['all'=>[
			"/js/plupload/plupload.full.min.js",
			'/js/jquery-3.6.0.min.js',
			'/js/yze_ajax.js',
			'/js/bootstrap.5.1.0.bundle.js',
			'/ydjs/yd_dynamic_select.js',
			'/ydjs/yd_tree.js',
			'/ydjs/yd_tree_select.js',
			'/ydjs/yd_upload.js',
			'/ydjs/ydhl-bootstrap-5.0.1.js',
			'/js/event.js'
		]];
		return $config[$bundle];
	}
	/**
	 * css资源分组，在加载时方便直接通过分组名加载;
	 * 资源路径以web 绝对路径/开始，/指的上public_html目录
	 * 在layouts中通过接口yze_css_bundle("yangzie,foo,bar")一次打包加载这里指定的资源
	 * @return array(资源路径1，资源路径2)
	 */
	public function css_bundle($bundle){
		$config = ['all'=>['css/bootstrap-5.1.0.min.css','icon/iconfont.css','css/ydjs.css','css/ydevcloud.css']];
		return $config[$bundle];
	}
}
?>
