<?php
namespace yangzie;

use MO;
use Translations;
class YZE_I18N extends YZE_Object{
	private $i18n = [];
	private static $me;
	private function __construct() {
	}

	/**
	 *
	 * @return YZE_I18N
	 */
	public static function get_instance() {
		if(!isset(self::$me)){
			$c = __CLASS__;
			self::$me = new $c;
		}
		return self::$me;
	}
	/**
	 * 清空加载的i18n
	 * @return YZE_I18N
	 */
	public function clear(){
		$this->i18n = [];
	}

	public function getLoadedI18N(){
		return $this->i18n;
	}
	public function setLoadedI18N($domain, &$mo){
		$this->i18n[$domain] = &$mo;
	}
}

function translate( $text, $domain = 'default' ) {
	if(!class_exists("Translations"))return $text;

	$l10n = YZE_I18N::get_instance()->getLoadedI18N();
	$empty = new Translations();
	if ( isset($l10n[$domain]) )
		$translations = $l10n[$domain];
	else
		$translations = $empty;
	return $translations->translate($text);
}

function __( $text, $domain = 'default' ) {
	return translate( $text, $domain );
}

function _e( $text, $domain = 'default' ) {
	echo translate( $text, $domain );
}

function load_textdomain($domain, $mofile) {
	if ( !is_readable( $mofile ) ) return false;
	$mo = new MO();
	if ( !$mo->import_from_file( $mofile ) ) return false;
	YZE_I18N::get_instance()->setLoadedI18N($domain, $mo);
	return true;
}

/**
 * 获取语言文件，比如zh-cn,en
 * @return array|mixed|string|unknown
 */
function get_accept_language() {
	$locale = YZE_Hook::do_hook(YZE_HOOK_GET_LOCALE);
	return $locale ?: YZE_Request::get_instance()->get_Accept_Language();
}

function load_default_textdomain() {
	$locale = get_accept_language();
	$mofile =  YZE_INSTALL_PATH."i18n/$locale.mo";
	return load_textdomain('default', $mofile);
}
?>
