<?php

namespace yangzie;

use \app\App_Module;

/**
 * 自动加载文件处理
 */
function yze_autoload($class) {
    $_ = preg_split("{\\\\}", strtolower($class));

    $module_name = $_[1];
    $class_name = @$_[2];
    $loaded_module_info = \yangzie\YZE_Object::loaded_module($module_name);

    $file = "";
    if(@$loaded_module_info['is_phar']){
        $module_name .= ".phar";
        $file = "phar://";
    }
    $file .= YZE_INSTALL_PATH . "app" . DS . "modules" . DS . $module_name . DS ;

    if(preg_match("{_controller$}i", $class)){
        $file .= "controllers" . DS . $class_name . ".class.php";
    }else if(preg_match("{_model$}i", $class)){
        $file .= "models" . DS . $class_name . ".class.php";
    }else if(preg_match("{_method$}i", $class)){//model meta define
        $file .= "models" . DS . $class_name . ".trait.php";
    }else if(preg_match("{_module$}i", $class)){
        $file .= "__config__.php";
    }else if(preg_match("{_view$}i", $class)){
        $file .= "views" . DS . preg_replace("{_view$}i", "", $class_name) . ".view.php";
        if ( ! file_exists($file)){
            $file = YZE_INSTALL_PATH . preg_replace("{_view$}i", "",strtr(strtolower($class), array("\\"=>"/"))) . ".view.php";
        }
    }
    if (!file_exists($file)){
        $file = YZE_INSTALL_PATH . strtr(strtolower($class), array("\\"=>"/")) . ".class.php";
    }
    if (!file_exists($file)){
        $file = YZE_INSTALL_PATH . strtr(strtolower($class), array("\\"=>"/")) . ".trait.php";
    }

    if(@$file && file_exists($file)){
        include $file;
    }else{
        YZE_Hook::do_hook("YZE_HOOK_AUTO_LOAD_CLASS", $class);
    }
}

spl_autoload_register("\yangzie\yze_autoload");



/**
 * 加载所有的模块，初始化配置
 */
function yze_load_app() {
    // 加载app配置
    if (! file_exists ( YZE_APP_PATH . "__config__.php" )) {
        die ( __ ( "app/__config__.php not found" ) );
    }
    include_once YZE_APP_PATH . '__config__.php';
    include_once YZE_APP_PATH . '__aros_acos__.php';

    $app_module = new App_Module ();
    $app_module->check ();

    $module_include_files = $app_module->module_include_files ( );
    foreach ( ( array ) $module_include_files as $path ) {
        $path = YZE_INSTALL_PATH.ltrim($path, DS);
        if(is_dir($path)){
            foreach (glob(rtrim($path, DS) . "/*") as $file) {
                include_once $file;
            }
        }else {
            include_once $path;
        }
    }

    YZE_Hook::include_hooks("app", YZE_APP_PATH.'hooks');

    $hook_dirs = [];
    foreach (glob(YZE_APP_MODULES_INC . "*") as $module) {
        $phar_wrap = "";
        if (is_file($module)) { // phar
            $phar_wrap = "phar://";
        }

        $module_name = strtolower(basename($module));
        if ($phar_wrap) {
            $module_name = ucfirst(preg_replace('/\.phar$/', "", $module_name));
        }

        if (@file_exists("{$phar_wrap}{$module}/__config__.php")) {
            require_once "{$phar_wrap}{$module}/__config__.php";

            $class = "\\app\\{$module_name}\\" . ucfirst($module_name) . "_Module";
            $object = new $class ();
            $object->check();

            $mappings = $object->get_module_config('routers');
            if ($mappings) {
                YZE_Router::get_Instance()->set_Routers($module_name, $mappings);
            }

            \yangzie\YZE_Object::set_loaded_modules($module_name, array(
                "is_phar" => $phar_wrap ? true : false
            ));
        }
        $hook_dirs[$module_name] = "{$phar_wrap}{$module}/hooks";
    }

    foreach($hook_dirs as $module_name=>$hook_dir){
        YZE_Hook::include_hooks($module_name, $hook_dir);
    }
}

/**
 * yangzie处理入口
 * 开始处理请求，如果没有指定uri，默认处理当前的uri请求,
 * @return string
 */
function yze_handle_request() {
    $output = function($request, $controller, $response) {
        if(is_a($response,"\\yangzie\\YZE_View_Adapter")){
            $layout = new YZE_Layout($controller->get_layout(), $response, $controller);
            $layout->output();
            return;
        }
        $output = $response->output(true);
        if ($output)header("Location: {$output}");
    };

    try {
        $request = YZE_Request::get_instance ();
        $dba     = YZE_DBAImpl::get_instance ();

        $request->init ();
        $controller = $request->controller_instance ();

        foreach($controller->response_headers() as $header){
            header($header);
        }

        $request->auth ();
        $dba->begin_Transaction();

        \yangzie\YZE_Hook::do_hook(YZE_HOOK_BEFORE_DISPATCH);
        $response = $request->dispatch();
        \yangzie\YZE_Hook::do_hook(YZE_HOOK_AFTER_DISPATCH);

        $dba->commit();

        $output($request, $controller, $response);
    }catch(\Exception $e){
        $controller = $request->controller_instance ();
        try{
            if (@$dba) $dba->rollback();
            if( !$controller) $controller = new YZE_Exception_Controller();
            if(is_a($e, "\\yangzie\\YZE_Suspend_Exception")) $controller = new YZE_Exception_Controller();

            $response = $controller->do_exception($e);
            if( ! $response){
                $controller = new YZE_Exception_Controller();
                $response = $controller->do_exception($e);
            }

            $filter_data = ["exception"=>$e, "controller"=>$controller, "response"=>$response];
            $filter_data = \yangzie\YZE_Hook::do_hook(YZE_HOOK_YZE_EXCEPTION,$filter_data);
            $response = $filter_data['response'];

            $output($request, $controller, $response);
        }catch (\Exception $notCatch){
            $controller = new YZE_Exception_Controller();
            $controller->do_exception(new YZE_RuntimeException($notCatch->getMessage()))->output();
        }
    }
}
?>
