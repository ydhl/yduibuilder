<?php
namespace yangzie;

class YZE_Object{
	const VERSION = '2.0.0';
	private static $loaded_modules = array();

	public static function set_loaded_modules($module_name, $module_info){
		self::$loaded_modules[strtolower($module_name)] = $module_info;
	}

	public static function loaded_module($module_name){
		return @self::$loaded_modules[strtolower($module_name)];
	}

	public function output(){

	}



	/**
	 * 取得一个变量的值，该方法主要是增加了默认值处理，如果变量为假值，返回默认值
	 * @return mixed
	 */
	public static function the_val($val,$default){
		return $val ?: $default;
	}

	/**
	 *
	 * aa_bb_cc格式化成Aa_Bb_Cc_suffix
	 * @param string $class_name
	 * @param string $suffix
	 */
	public static function format_class_name($class_name,$suffix){
		foreach(explode("_", trim($class_name)) as $word){
			$class[] = ucfirst(strtolower($word));
		}
		return join("_", $class).($suffix ? "_{$suffix}" : "");
	}


	/**
	 * 转义html符号
	 *
	 * @param array $array
	 * @param $type INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, INPUT_ENV, INPUT_SESSION, or INPUT_REQUEST.
	 * @return unknown
	 */
	public static function filter_special_chars(array $array=array(),$type){
		$definition = array();
		foreach($array as $name=>$value){
			$definition[$name]['filter'] 	= FILTER_CALLBACK;
			$definition[$name]['options']	= 'htmlspecialchars';
		}
		return filter_input_array($type, $definition);
	}

	/**
	 * 转义$array数组中的html符号
	 * @param array $array
	 * @return array|false|null
	 */
	public static function filter_vars(array $array){
		$definition = array();
		foreach($array as $name=>$value){
			$definition[$name]['filter'] 	= FILTER_CALLBACK;
			$definition[$name]['options']	= 'htmlentities';
		}
		return filter_var_array($array, $definition);
	}

	/**
	 * 转义数据中的html符号
	 * @param $var
	 * @return mixed
	 */
	public static function filter_var($var){
		return filter_var($var, FILTER_CALLBACK,array('options' => 'htmlentities')) ?: $var;
	}

	/**
	 * 解码数据中的html符号
	 * @param $var
	 * @return mixed
	 */
	public static function defilter_var($var){
		return filter_var($var, FILTER_CALLBACK,array('options' => 'html_entity_decode')) ?: $var;
	}

	/**
	 * 中文解码
	 * @param $str
	 * @param $charcode
	 * @return array|false|string
	 */
	public static function unescape($str,$charcode="UTF-8"){
		$text = preg_replace_callback("/[%|\\\\]u(?P<c>[0-9A-Za-z]{4})/",function($matches){
			$c = '';
			$val = intval($matches['c'],16);
			if($val < 0x7F){        // 0000-007F
				$c .= chr($val);
			}elseif($val < 0x800) { // 0080-0800
				$c .= chr(0xC0 | ($val / 64));
				$c .= chr(0x80 | ($val % 64));
			}else{                // 0800-FFFF
				$c .= chr(0xE0 | (($val / 64) / 64));
				$c .= chr(0x80 | (($val / 64) % 64));
				$c .= chr(0x80 | ($val % 64));
			}
			return $c;
		},$str);
		return mb_convert_encoding($text, $charcode, 'utf-8');
	}
}

?>
