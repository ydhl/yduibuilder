<?php
/**
 * 该文件是独立于框架外直接访问的，所以部分框架的功能不能使用，比如YZE_Request, YZE_Controller等
 * 该文件的作用
 *
 * 1. 对系统的js,css bundle打包下载，参数t=css|js, b=要加载的bundle，在__config__.php中进行定义
 * 2. 对某个模块的bundle进行打包下载，参数t=css|js, m=模块名，在模块的__config__.php中进行定义
 * 3. 访问某个模块下面的静态资源, 参数t=asset, m=模块名，src=要加载的资源路径，位于模块下面的public_html
 * 通过yze_css_bundle,通过yze_js_bundle,yze_module_js_bundle,yze_module_css_bundle接口生产访问本文件的html脚本
 */
use app\App_Module;
use yangzie\YZE_Base_Module as YZE_Base_Module;
use yangzie\YZE_Object;

require 'init.php';
date_default_timezone_set('Asia/Chongqing');
set_time_limit(0);

function get_download_mime_type($ext) {
	if (!\yangzie\yze_isimage($ext)) return "application/octet-stream";
	$ext = strtolower($ext);
	switch ($ext) {
		case "png": return "image/png";
		case "svg": return "image/svg+xml";
		case "gif": return "image/gif";
		case "bmp": return "image/bmp";
		case "ico": return "image/x-icon";
		case "jpeg":
		case "jpg":
		default :return "image/jpeg";
	}
}
/**
 * @param $module
 * @return YZE_Base_Module|null
 */
function create_module($module){
	$module_class = YZE_Object::format_class_name ( $module, "Module" );

	$class = "\\app\\" . $module . "\\" . $module_class;
	if (class_exists ( $class )) {
		return new $class ();
	}
	return null;
}

$bundle_files = array();
$type = strtolower($_GET["t"]);
$bundle = @$_GET["b"];//load static bundle
$module = @$_GET["m"];//load module bundle
$asset  = @$_GET["src"];//load asset
$eTag = '';
$asset_file_ext = '';
$asset_file_path = '';

if( ! in_array($type, array("js","css",'asset')))return;
if ( ! $bundle && ! $module && !$asset ) return;

/**
 * 1.output content type
 */
if( "css" == $type){
    header("Content-Type: text/css");
}else if("js" == $type){
    header('Content-type: text/javascript');
}else{
	if (!$asset || !$module) return;
	$asset_file_ext = pathinfo($asset, PATHINFO_EXTENSION);
	header("Content-type: " . get_download_mime_type($asset_file_ext));
	$is_phar      = is_file(YZE_APP_MODULES_INC.$module.".phar");
	$asset_file_path = ($is_phar?'phar://':'').\yangzie\yze_get_abs_path(YZE_APP_MODULES_INC.$module. ($is_phar ? '.phar' : '') .'/public_html'.ltrim($asset));
	$bundle_files[] = $asset_file_path;
	$eTag = md5($asset_file_path);
}

/**
 * 2.read file
 */
if ($type != 'asset'){
	$base_dir     = '';
	$moduleConfig = null;
	$is_phar      = null;
	if($module){
		$moduleConfig = create_module($module);
		$eTag         = md5($module.$bundle);
		$is_phar      = is_file(YZE_APP_MODULES_INC.$module.".phar");
		$base_dir     = YZE_APP_MODULES_INC.$module . ($is_phar ? '.phar' : '') . '/public_html';
	}else if ($bundle){
		$eTag         = md5($bundle);
		$moduleConfig = new App_Module();
		$base_dir     = dirname(__FILE__);
	}
	if (!$moduleConfig) return;
	foreach (explode(",", $bundle) as $_bundle) {
		$temp = $type=="js" ? $moduleConfig->js_bundle($_bundle) : $moduleConfig->css_bundle($_bundle);
		if( ! $temp)continue;

		$bundle_files = array_merge($bundle_files, $temp);
	}

	$bundle_files = array_map(function($item) use ($base_dir, $is_phar){
		return ($is_phar?'phar://':'').\yangzie\yze_get_abs_path($item, $base_dir);
	}, $bundle_files);
}

if (!$bundle_files)return;

$last_modified_time = 0;
$files = array();

foreach ($bundle_files as $bundle_file) {
    if (empty($bundle_file)) continue;

    if ( ! file_exists($bundle_file) ) continue;
	$path_info = pathinfo($bundle_file);
	if( strcasecmp( $path_info['extension'], $type) != 0) continue;

	$files[]    = $bundle_file;

    $modified_time  = filemtime($bundle_file);
    if ($last_modified_time == 0 || $modified_time > $last_modified_time) {
        $last_modified_time = $modified_time;
    }
}
$eTag .= $last_modified_time;

/**
 * 3. cache control
 */
header("Cache-Control: max-age=86400");
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified_time).' GMT');
header('Etag:' . $eTag);
header('Expires:' . gmdate('D, d M Y H:i:s', time()+86400).' GMT');

if (@$_SERVER['HTTP_IF_NONE_MATCH'] == $eTag) {
    header("HTTP/1.0 304 Not Modified");
    exit(0);
}

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    $browser_time = strtotime(preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']));
    if ($browser_time >= $last_modified_time) {
        header("HTTP/1.0 304 Not Modified");
        exit(0);
    }
}

/**
 * 4. output
 */
if ($type=="asset"){
	if(!file_exists($asset_file_path)) return;

	ob_clean();
	$file_name = basename($asset_file_path);
	$filesize = filesize($asset_file_path);

	header("Content-type: " . get_download_mime_type($asset_file_ext));
	header("Accept-Ranges: bytes");
	header("Accept-Length: " . $filesize);

	if (!\yangzie\yze_isimage($asset_file_path)) {
		header("Content-Disposition: attachment; filename=" . $file_name);
	}

	$read_buffer = 4096;
	$handle = fopen($asset_file_path, 'rb');
	$sum_buffer = 0;

	while (!feof($handle) && $sum_buffer < $filesize) {
		echo fread($handle, $read_buffer);
		$sum_buffer += $read_buffer;
	}
	ob_flush();
	flush();
	exit(0);
}else{
	foreach ($files as $file) {
		echo file_get_contents($file);
	}
}
?>
