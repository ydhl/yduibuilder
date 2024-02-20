<?php
namespace yangzie;

class Generate_Controller_Script extends AbstractScript{
	private $controller;
	private $view_format;
	private $module_name;
	private $uri;
	private $uri_args = array();


	public function generate(){
		$argv = $this->args;
		$this->controller		= $argv['controller'];
		$this->view_format 		= $argv['view_format'];
		$this->module_name 		= $argv['module_name'];
		$this->uri 				= $argv['uri'];

		$generate_module = new Generate_Module_Script(array("module_name" => $this->module_name));
		$generate_module->generate();

		$this->save_class();
		$this->save_test();

		echo __("update __config__ file :\t");
		$this->update_module();
		echo get_colored_text(__("Ok."), "blue","white")."\r\nDone.";
	}

	private function update_module(){
		$module = $this->module_name;
		$path = dirname(dirname(__FILE__))."/app/modules/".$module;

		$module_file = "$path/__config__.php";
		include_once $module_file;
		$module_cls = "\\app\\".$this->module_name."\\".$module."_Module";
		$module = new $module_cls;
		$ref_cls 	= new \ReflectionClass($module_cls);
		$method 	= $ref_cls->getMethod("_config");
		$method->setAccessible(true);
		$configs = $method->invoke($module);
		if($this->uri && !@$configs['routers'][$this->uri]){
			$configs['routers'][$this->uri] = array("controller"=>$this->controller, "args"=>$this->uri_args);
			$config_str = $this->_arr2str($configs, "\t\t");
			$start_line = $method->getStartLine();
			$end_line 	= $method->getEndLine();
//  echo $start_line,", ",$end_line;
			$file_content_arr = file($module_file);
			for($i=$start_line; $i<$end_line; $i++){
				unset($file_content_arr[$i]);
			}
// 			echo "\tprotected function _config(){\r\n\t\treturn ".$config_str."\r\n\t}\r\n";
// 			print_r($file_content_arr);die;
			//Tip 数组的索引从0开始
			$file_content_arr[$start_line-1] = "\tprotected function _config(){\r\n\t\treturn ".$config_str."\r\n\t}\r\n";
			$file_content_arr = array_values($file_content_arr);
			file_put_contents($module_file, implode($file_content_arr));
		}
	}


	private function _arr2str(array $array, $tab, $is_end=true)
	{
		$str = "array(\r\n";
		foreach ($array as $name => $value){
			$str .= $tab."\t".(is_numeric($name) ? $name : "'$name'")."\t=> ";
			if(is_array($value)){
				$str .= $this->_arr2str($value, $tab."\t", false);
			}else{
				$str .= "'$value',\r\n";
			}
		}
		$str .= $tab.")".($is_end ? ";" : ",\r\n");
		return $str;
	}


	private function save_test(){
		$module = $this->module_name;
		$controller = $this->controller;
		if(empty($controller)){
			return;
		}
		echo __("create controller phpt file:\t");
		$class = YZE_Object::format_class_name($controller,"Controller");
		$class_file_path = dirname(dirname(__FILE__))
		."/tests/". $module."/" ."".strtolower($class).".class.phpt";
		$test_file_content = "--TEST--
		$class class Controller Unit Test
--FILE--
<?php
namespace app\\$module;
ini_set(\"display_errors\",0);
chdir(dirname(dirname(dirname(__FILE__))).\"/app/public_html\");
include \"init.php\";
//write you test code here
?>
--EXPECT--";
		$this->create_file($class_file_path,$test_file_content);
	}



	private function create_controller($controller){
		$module = $this->module_name;

		$class = YZE_Object::format_class_name($controller,"Controller");
		$class_file_path = dirname(dirname(__FILE__))
		."/app/modules/". $module."/controllers/".strtolower($class).".class.php";
		$class_file_content = "<?php
namespace app\\$module;
use \\yangzie\\YZE_Resource_Controller;
use \\yangzie\\YZE_Request;
use \\yangzie\\YZE_Redirect;
use \\yangzie\\YZE_RuntimeException;
use \\yangzie\YZE_JSON_View;

/**
*
* @version \$Id\$
* @package $module
*/
class $class extends YZE_Resource_Controller {
    public function index(){
        \$request = \$this->request;
        //\$this->layout = 'tpl name';
        \$this->set_view_data('yze_page_title', 'this is controller ".$this->controller."');
    }

    public function exception(\Exception \$e){
        \$request = \$this->request;
        \$this->layout = 'error';
        //Post 请求或者返回json接口时，出错返回json错误结果
        \$format = \$request->get_output_format();
        if (!\$request->is_get() || strcasecmp ( \$format, \"json\" )==0){
        	\$this->layout = '';
        	return YZE_JSON_View::error(\$this, \$e->getMessage());
        }
    }
}
?>";
		echo __("create controller:\t\t");
		$this->create_file($class_file_path, $class_file_content);

		if($this->view_format){
			$this->create_view();
			$this->create_layout();
		}
	}


	private function save_class(){
		$module = $this->module_name;
		$controller = $this->controller;

		//create controller
		$this->create_controller($controller);
	}


	protected function create_view(){
		$module = $this->module_name;
		$controller = $this->controller;
		$formats = explode(" ", $this->view_format);
		$this->check_dir(dirname(dirname(__FILE__))."/app/modules/". $module."/views");
		foreach ($formats as $format){
			$view_file_path = dirname(dirname(__FILE__))
			."/app/modules/". $module."/views/{$controller}-index.{$format}.php";
			$view_file_content = "<?php
namespace app\\$module;
use \\yangzie\\YZE_Resource_Controller;
use \\yangzie\\YZE_Request;
use \\yangzie\\YZE_Redirect;
use \\yangzie\\YZE_RuntimeException;

/**
 * 视图的描述
 * @param type name optional
 *
 */
 
\$data = \$this->get_data('arg_name');
?>

this is {$controller} view";
			echo __("create view {$controller}.{$format}.php:\t\t\t");
			$this->create_file($view_file_path, $view_file_content);
		}

	}

	protected function create_layout(){
		$module = $this->module_name;
		$controller = $this->controller;
		$formats = explode(" ", $this->view_format);

		foreach ($formats as $format){
			$layout = dirname(dirname(__FILE__))."/app/vendor/layouts/{$format}.layout.php";
			if(!file_exists($layout)){
				$layout_file_content = "<?php
/**
  * {$format}布局
  */
echo \$this->content_of_view()
?>
";
				echo __("create layout {$format} :\t\t\t");
				$this->create_file($layout, $layout_file_content);
			}
		}
	}
}
?>
