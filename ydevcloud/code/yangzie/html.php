<?php

namespace yangzie;

/**
 * 显示给定的视图并停止执行
 *
 * @param YZE_View_Adapter $view
 * @param YZE_Resource_Controller $controller
 */
function yze_die(YZE_View_Adapter $view, YZE_Resource_Controller $controller) {
    $layout = new YZE_Layout ( "error", $view, $controller );
    $layout->output ();
    die ( 0 );
}


/**
 * 返回当前控制器的出错信息
 *
 * @author leeboo
 * @param $begin_tag 每条错误消息的开始html标签
 * @param $end_tag 每条错误消息的结束html标签
 * @return string
 *
 */
function yze_controller_error($begin_tag = null, $end_tag = null) {
    if (($exception = YZE_Request::get_instance ()->get_exception (  ))) {
        return $begin_tag . $exception->getMessage() . $end_tag;
    }
}

/**
 * 在当前请求的url参数的基础上加上args参数，并于url一并返回
 *
 * @param unknown $url
 * @param unknown $args
 */
function yze_merge_query_string($url, $args = array(), $format=null){
    $path   = parse_url($url, PHP_URL_PATH);
    $query  = parse_url($url, PHP_URL_QUERY);
    $get    = array_merge($_GET, $args);
    if($query && parse_str($query, $newArgs)){
        $get    = array_merge($get, $newArgs);
    }

    if ($format){
        $url = (strrpos($url, ".")===false ? $url : substr($url, 0, strrpos($url, "."))).".{$format}";
    }

    return $url."?".http_build_query($get);
}

/**
 * 输出js加载script代码, 工作路径是网站工作目录（public_html），
 * 所以js中如果有资源地址访问，请注意要调成相对于网站工作目录
 * @param string $bundle, 多个bundle用,号分隔
 * @param string $version 版本
 */
function yze_js_bundle($bundle, $version=""){
?>
<script type="text/javascript" src="/load.php?t=js&v=<?php echo $version?>&b=<?php echo $bundle?>"></script>
<?php
}

/**
 * 输出css加载link代码, 工作路径是网站工作目录（public_html），
 * 所以css中如果有资源地址访问，请注意要调成相对于网站工作目录
 * @param string $bundle, 多个bundle用,号分隔
 * @param string $version 版本
 */
function yze_css_bundle($bundle, $version=""){
?>
<link rel="stylesheet" type="text/css" href="/load.php?t=css&v=<?php echo $version?>&b=<?php echo $bundle?>" />
<?php
}
/**
 * 输出module指定的js bundle，bundle在__config__中配置
 */
function yze_module_js_bundle($bundle="", $version=""){
	$request = YZE_Request::get_instance();
	?>
<script type="text/javascript" src="/load.php?t=js&v=<?php echo $version?>&m=<?php echo $request->module()?>&b=<?php echo $bundle?>"></script>
<?php
}
/**
 * 输出module指定的css bundle，bundle在__config__中配置
 */
function yze_module_css_bundle($bundle="", $version=""){
	$request = YZE_Request::get_instance();
?>
<link rel="stylesheet" type="text/css" href="/load.php?t=css&v=<?php echo $version?>&m=<?php echo $request->module()?>&b=<?php echo $bundle?>" />
<?php
}

/**
 * 返回当前访问模块下html模块里面src的访问地址
 * @param $src
 */
function yze_module_asset_url($src, $version='') {
    $request = YZE_Request::get_instance();
    return "/load.php?t=asset&m=".$request->module()."&v={$version}&src=".urlencode($src);
}
?>
