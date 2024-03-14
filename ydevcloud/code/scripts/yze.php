<?php
namespace yangzie;
define("YZE_SCRIPT_LOGO", "
================================================================
		YANGZIE(V2.0.0) Generate Script
		易点互联®
================================================================");

define("YZE_METHED_HEADER", "
================================================================
		%s
================================================================");
define("YZE_SCRIPT_USAGE", YZE_SCRIPT_LOGO."

\t1.  Generate module, controller, view Scaffolding file
\t2.  Generae model (database to code)	
\t3.  Delete module
\t4.  Delete controller and view file	
\t5.  Phar a module
\t6.  Run unit
\t0.  Quit

please input number to select: ");


global $language, $db;
$language = "zh-cn";

if(!preg_match("/cli/i",php_sapi_name())){
	echo wrap_output(sprintf(__("please run in command mode: php scrips/yze.php",dirname(__FILE__))));die();
}


chdir("./app/public_html");
include_once 'init.php';
include_once '../../scripts/generate-controller.php';
include_once '../../scripts/generate-model.php';
include_once '../../scripts/generate-module.php';

if(true){
	clear_terminal();
	while(($cmds = display_home_wizard())){
		$command = $cmds["cmd"];
		clear_terminal();
		echo get_colored_text(wrap_output(__("begin generate...")), "blue", "white")."\r\n";
		$class_name = "\yangzie\Generate_".ucfirst(strtolower($command))."_Script";
		$object = new $class_name($cmds);
		$object->generate();
		echo "\r\n".get_colored_text(wrap_output(__("generate done.")), "blue", "white")."\r\n";
		//fgets(STDIN);
		die();
	}
}


function display_home_wizard(){
	clear_terminal();
	echo wrap_output(YZE_SCRIPT_USAGE);

	while(!in_array(($input = fgets(STDIN)), array(0,1, 2, 3, 4, 5, 6))){
		echo wrap_output(__("please input number to select: "));
	}

	switch ($input){
		case 1: return display_mvc_wizard();
		case 2:  return display_model_wizard();
		case 3:  return display_delete_module_wizard();
		case 4:  return display_delete_controller_wizard();
		case 5:  return display_phar_wizard();
		case 6:  return _run_test();
		case 0:  die(wrap_output("\r\n
Quit.\r\n
"));
		default: return array();
	}
}

function _run_test(){
	clear_terminal();
	echo wrap_output(sprintf(__( YZE_METHED_HEADER."
	
Choose unit to run，%sBack

"), "run unit test", get_colored_text(" 0 ", "red", "white")));

	$index = 0;
	$tests = array();
	foreach(glob("../../tests/*")  as $f){
		if(is_dir($f)) {
			$index++;
			$test = basename($f);
			$tests[$index] = $f;
			echo "\t".($index).". {$test} \n";
		}
	}
	if($tests){
		$tests[0] = "";
		echo wrap_output(__("\t0. run all tests\n:"));
	}else{
		echo wrap_output(__("\tno unit test\n"));
	}
	while (!array_key_exists(($selectedIndex = get_input()), $tests)){
		echo get_colored_text(wrap_output(__("\ttest not exist, please choose again:  ")), "red");
	}

	include "../../tests/config.php";
	$php = getenv("TEST_PHP_EXECUTABLE");
	if (empty($php) || !file_exists($php)) {
		echo get_colored_text(wrap_output(__("please modify TEST_PHP_EXECUTABLE in tests/config.php ")), "red");die;
	}


	if($selectedIndex==="0"){
		system("php ../../tests/run-tests.php  ../../tests");
	}else{
		system("php ../../tests/run-tests.php ".$tests[$selectedIndex]);
	}
}


function display_phar_wizard(){
	clear_terminal();
	echo wrap_output(sprintf(__( YZE_METHED_HEADER."
	
phar a module，%s back
1. (1/2)please input module name:  "), "phar module", get_colored_text(" 0 ", "red", "white")));

	while (!is_validate_name(($module = get_input()))){
		echo get_colored_text(wrap_output(__("\tname is invalid, pleae input again:  ")), "red");
	}

	if( ! file_exists(dirname(dirname(__FILE__))."/app/modules/".$module)){
		echo wrap_output("module not exist");
	}

	echo wrap_output(__("2. (2/2)phar signature key file name (pem file in the tmp folder) 
	
if you need create pem file, do such as:
1.cd tmp
2.openssl genrsa -out mykey.pem 1024
3.openssl rsa -in mykey.pem -pubout -out mykey.pub

if not need signature please press enter 

:"));
	$key_path = trim(get_input());
	if ($key_path){
		while (!file_exists(($key_path = YZE_INSTALL_PATH."tmp/".$key_path))){
			echo get_colored_text(wrap_output(vsprintf(__("\t%s file not exist:  "), $key_path)), "red");
		}
	}

	phar_module($module, $key_path);
	@unlink(YZE_APP_PATH."modules/{$module}.phar");
	yze_move_file(YZE_INSTALL_PATH."tmp/{$module}.phar", YZE_APP_PATH."modules");
	echo wrap_output(sprintf(__("phar saved at modules/%s.phar\r\n"),$module));
	if($key_path){
		$key_name = pathinfo(basename($key_path), PATHINFO_FILENAME);
		copy(YZE_INSTALL_PATH."tmp/{$key_name}.pub", YZE_APP_PATH."modules/{$module}.phar.pubkey");
		echo wrap_output(sprintf(__("%s.phar.pubkey saved at modules/%s.phar.pubkey\r\n"),$module,$module), 'green');
	}
	return array();
}

function phar_module($module, $key_path){
	@mkdir(dirname(dirname(__FILE__))."/tmp/");
	try{
		echo ini_get('phar.readonly');
		$phar = new \Phar(dirname(dirname(__FILE__))."/tmp/".$module.'.phar', 0, $module.'.phar');
	}catch (\Exception $e){
		echo wrap_output($e->getMessage());
		die();
	}
	$phar->buildFromDirectory(dirname(dirname(__FILE__))."/app/modules/".$module);
	//$phar->setStub($phar->createDefaultStub('__config__.php'));
	$phar->compressFiles(\Phar::GZ);
	if($key_path){
		$private = openssl_get_privatekey(file_get_contents($key_path));
		$pkey = '';
		openssl_pkey_export($private, $pkey);
		$phar->setSignatureAlgorithm(\Phar::OPENSSL, $pkey);
	}
}

function display_delete_controller_wizard(){
	clear_terminal();
	echo sprintf(wrap_output(__( YZE_METHED_HEADER."

delete controller and view，%s back
1. (1/2)module name: ")), "delete controller",get_colored_text(" 0 ", "red", "white"));

	while (!is_validate_name(($module = get_input()))){
		echo get_colored_text(wrap_output(__("\tmodule not found:  ")), "red");
	}

	echo wrap_output(__("2. (2/2)controller name:  "));
	while (!is_validate_name(($controller = get_input()))){
		echo get_colored_text(wrap_output(__("\tcontroller not found:  ")), "red");
	}

	if( ! file_exists(dirname(dirname(__FILE__))."/app/modules/{$module}/controllers/{$controller}_controller.class.php")){
		echo wrap_output(__("controller not found"));
	}else{
		unlink(dirname(dirname(__FILE__))."/app/modules/{$module}/controllers/{$controller}_controller.class.php");
		foreach (glob(dirname(dirname(__FILE__))."/app/modules/{$module}/views/{$controller}.*") as $file){
			unlink($file);
		}
		unlink(dirname(dirname(__FILE__))."/tests/{$module}/{$controller}_controller.class.phpt");
		echo wrap_output(__("deleted"));
	}

	return array();
}

function display_delete_module_wizard(){
	clear_terminal();
	echo sprintf(wrap_output(__( YZE_METHED_HEADER."
	
module name，%s back:  ")), "delete module", get_colored_text(" 0 ", "red", "white"));

	while (!is_validate_name(($module = get_input()))){
		echo get_colored_text(wrap_output(__("\tmodule not found:  ")), "red");
	}

	if( ! file_exists(dirname(dirname(__FILE__))."/app/modules/".$module)){
		echo wrap_output(__("module not found "));
	}else{
		rrmdir(dirname(dirname(__FILE__))."/app/modules/".$module);
		rrmdir(dirname(dirname(__FILE__))."/tests/".$module);
		echo wrap_output(__("deleted"));
	}

	return array();
}

function display_mvc_wizard(){
	clear_terminal();
	echo wrap_output(sprintf(__( YZE_METHED_HEADER."
  
generate controller and view，%s back:
1. (1/3)module name:  "), "generate controller", get_colored_text(" 0 ", "red", "white")));

	while (!is_validate_name(($module = get_input()))){
		echo get_colored_text(wrap_output(__("\tname is invalid, please type again:  ")), "red");
	}

	echo wrap_output(__("2. (2/3)controller name:  "));
	while (!is_validate_name(($controller = get_input()))){
		echo get_colored_text(wrap_output(__("\tname is invalid, please type again:  ")), "red");
	}
	$uri = '';
	if(($uris = is_controller_exists($controller, $module))){
		echo wrap_output(__("3. (3/3)controller is exist，it's URI:\n\n"));
		foreach ($uris as $index => $uri){
			echo "\t ".($index+1).". {$uri}\n";
		}
	}else{
		echo wrap_output(__("3. (3/3)URI route, default uri is /{$module}/{$controller}:  "));
		$uri = get_input();
	}

	return @array(
		"cmd" => "controller",
		"controller"=>$controller,
        "uri"=>$uri,
        "module_name"=>$module,
        "view_format"=>"tpl" ,

// 		"model"=>$model,
// 		"view_tpl"=>$view_tpl
	);
}

function is_controller_exists($controller, $module){
	if(file_exists(YZE_APP_MODULES_INC.$module."/__config__.php")){
		include_once YZE_APP_MODULES_INC.$module."/__config__.php";
		$class = "\\app\\".$module."\\".ucfirst(strtolower($module))."_Module";
		$object = new $class();
		return $object->get_uris_of_controller($controller);

	}
	return false;
}

function display_model_wizard(){
    global $db;
	clear_terminal();

	echo wrap_output(sprintf(__( YZE_METHED_HEADER."

generate model，%s back:
1. (1/2)db table name: "), "generate model", get_colored_text(" 0 ", "red", "white")));


	while (!is_validate_table(($table=get_input()))){
		echo get_colored_text(wrap_output(sprintf(__("\tdb table not exist (%s)，please check:  "), mysqli_error($db))), "red");
	}

	echo wrap_output(__("2. (2/2)module name:  "));
	while (!is_validate_name(($module = get_input()))){
		echo get_colored_text(wrap_output(__("\tmodule is invalid, please check:  ")), "red");
	}


	return array(
		"cmd" => "model",
		"base"=>"table",
		"module_name"=>$module,
		"class_name"=>$table,
		"table_name"=>$table,
	);
}

function get_colored_text($text, $fgcolor=null, $bgcolor=null){
	if(PHP_OS=="WINNT")return $text;
	//return "\033[40m\033[31m some colored text \033[0m"; // red
	if(!$fgcolor && !$bgcolor)return $text;

	$_fgcolor = get_fgcolor($fgcolor);
	$_bgcolor = get_bgcolor($bgcolor);

	$colored_string = "";
	if ($_fgcolor) {
		$colored_string .= "\033[" . $_fgcolor . "m";
	}

	if ($_bgcolor) {
		$colored_string .= "\033[" . $_bgcolor . "m";
	}

	$colored_string .=  $text . "\033[0m";
	return $colored_string;
}

function get_bgcolor($color){
	switch(strtolower($color)){
	case 'black': return'0;30';
	case 'dark_gray': return'1;30';
	case 'blue': return'0;34';
	case 'light_blue': return'1;34';
	case 'green': return'0;32';
	case 'light_green': return'1;32';
	case 'cyan': return'0;36';
	case 'light_cyan': return'1;36';
	case 'red': return'0;31';
	case 'light_red': return'1;31';
	case 'purple': return'0;35';
	case 'light_purple': return'1;35';
	case 'brown': return'0;33';
	case 'yellow': return'1;33';
	case 'light_gray': return'0;37';
	case 'white': return'1;37';

		default: return null;
	}
}
function get_fgcolor($color){
	switch(strtolower($color)){
	case 'black': return'40';
	case 'red': return'41';
	case 'green': return'42';
	case 'yellow': return'43';
	case 'blue': return'44';
	case 'magenta': return'45';
	case 'cyan': return'46';
	case 'light_gray': return'47';
	default: return null;
	}
}

function get_input(){
	$input = strtolower(trim(fgets(STDIN)));
	is_back($input);
	return $input;
}

function is_back($input){
	if(strlen($input) >0 && $input=="0"){display_home_wizard();die;}
}

function is_validate_name($input){
	return preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $input);
}

function is_validate_table($table){
    global $db;
	$app_module = new \app\App_Module();
	$db = mysqli_connect(
			$app_module->get_module_config("db_host"),
			$app_module->get_module_config("db_user"),
			$app_module->get_module_config("db_psw"),
			$app_module->get_module_config("db_name"),
			$app_module->get_module_config("db_port")
	);
	mysqli_select_db($db, $app_module->get_module_config("db_name"));
	return mysqli_query($db, "show full columns from `$table`");
}


function clear_terminal(){
	if(PHP_OS=="WINNT"){
		$clear = "cls";
	}else{
		$clear = "clear";
	}
	exec($clear);
}

function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

function wrap_output($msg){
//	if(PHP_OS=="WINNT"){
//		return iconv("UTF-8", "GB2312//IGNORE", $msg);
//	}else{
		return $msg;
//	}
}

abstract class AbstractScript{
	protected $args = array();
	public function __construct($args){
		$this->args = $args;
	}
	public abstract function generate();

	public function check_dir($path){
		if(!file_exists($path)){
			$dir = mkdir($path);
			if(empty($dir)){
				die("\r\n\r\n\tcan not make dir: \r\n\r\n\t$path \r\n\r\n");
			}
			chmod($path, 0777);
		}
	}

	public function create_file($file_path,$content,$force=false){
		if(file_exists($file_path) && !$force){
			echo get_colored_text("file exists", "red", "white")."\r\n";return;
		}

		$f = fopen($file_path,'w+');
		if(empty($f)){
			echo get_colored_text("can not open file:{$file_path}");return;
		}
		chmod($file_path,0777);
		fwrite($f,$content);
		fclose($f);
		echo get_colored_text("OK.","blue","white")."\r\n";
	}

}
?>
