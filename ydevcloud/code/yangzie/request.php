<?php

namespace yangzie;

/**
 * 一次请求处理的上下文，单例模式；
 *
 * @category Framework
 * @package Yangzie
 * @author liizii
 * @link yangzie.yidianhulian.com
 */
class YZE_Request extends YZE_Object {
    private $method;
    private $request_method;
    private $vars;
    private $post = array ();
    private $get = array ();
    private $cookie = array ();
    private $server = array ();
    private $env = array ();
    private $controller_name;
    private $controller_class;
    private $controller;
    private $module_class;
    private $module_obj;
    private $module;
    private $view_path;
    private $uri;
    private $full_uri;
    private $queryString;
    private $uuid;
    private $exception;
    private static $me;
    private $context;

    /**
     * 在上下文中设置值, 在这次请求的中可取出该值
     * @param $name
     * @param $value
     * @return YZE_Request
     */
    public function set($name, $value){
        $this->context[$name] = $value;
        return $this;
    }

    /**
     * 在上下文中取值
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        return @$this->context[$name];
    }

    /**
     * post请求数据
     * @return array
     */
    public function the_post_datas() {
        return $this->post;
    }

    /**
     * 请求的get数据
     * @return array
     */
    public function the_get_datas() {
        return $this->get;
    }

    /**
     * 往post数据中设置值，注意该值是后端设置，不是前端提交的
     * @param string $name
     * @param string $value
     * @return YZE_Request
     */
    public function set_post($name, $value) {
        $this->post [$name] = $value;
        return $this;
    }

    /**
     * 从post数据中取值
     * @param string $name
     * @param string $default post中不存在name时返回该值
     * @return false|mixed|string|null
     */
    public function get_from_post($name, $default = null) {
        if (array_key_exists ( $name, $this->post )) {
            return $this->post [$name];
        }
        return $default;
    }

    /**
     * 从$_SERVER中取值
     * @param string $name
     * @param string $default 不存在name时返回该值
     * @return mixed
     */
    public function get_from_server($name, $default=null) {
        if (array_key_exists ( $name, $this->server )) {
            return @$this->server [$name];
        }
        return $default;
    }

    /**
     * 从cookie中取值
     * @param string $name
     * @param string $default 不存在name时返回该值
     * @return false|mixed|string|null
     */
    public function get_from_cookie($name, $default = null) {
        if (array_key_exists ( $name, $this->cookie )) {
            return @$this->cookie [$name];
        }
        return $default;
    }

    /**
     * 从query string取值
     * @param string $name
     * @param string $default 不存在name时返回该值
     * @return false|mixed|string|null
     */
    public function get_from_get($name, $default = null) {
        if (array_key_exists ( $name, $this->get )) {
            return @$this->get [$name];
        }
        return $default;
    }

    /**
     * 从post > cookie > get > server中取值
     *
     * @param string $name
     * @param string $default 不存在name时返回该值
     * @return false|mixed|string|null
     */
    public function get_from_request($name, $default = null) {
        if (array_key_exists ( $name, $this->post )) {
            return $this->get_from_post($name, $default);
        }
        if (array_key_exists ( $name, $this->cookie )) {
            return $this->get_from_cookie($name, $default);
        }
        if (array_key_exists ( $name, $this->get )) {
            return $this->get_from_get($name, $default);
        }
        if (array_key_exists ( $name, $this->server )) {
            return $this->get_from_server($name, $default);
        }
        return $default;
    }

    /**
     * 当前请求的方法，如get post delete put等
     * @return mixed
     */
    public function get_request_method() {
        return $this->request_method;
    }
    /**
     * 请求的资源的URI，每次请求，URI是唯一且在一次请求内是不变的
     * 返回的只是uri中的路径部分，query部分不包含，如/people-1/question-2/answers?p=3
     * 只返回/people-1/question-2/answers
     * 返回的url进行了urldecode
     *
     * @return string
     * @author liizii
     */
    public function the_uri() {
        return $this->uri;
    }

    /**
     * 请求的路径及query string
     * 返回的url没有urldecode
     *
     * @return string
     */
    public function the_full_uri() {
        return $this->full_uri;
    }

    /**
     * 请求字符串，请求地址中?后面的部分
     * @return mixed
     */
    public function the_query() {
        return $this->queryString;
    }

    /**
     * 请求的scheme，如http，https
     */
    public function get_Scheme() {
        $scheme = 'http';
        if (isset ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] == 'on') {
            $scheme .= 's';
        }
        return $scheme;
    }

    private function __construct() {
        // 预处理请求数据，把get，post，cookie等数据进行urldecode后编码
        $this->post = $_POST ? self::filter_special_chars ( $_POST, INPUT_POST ) : array ();
        $this->get = $_GET ? self::filter_special_chars ( $_GET, INPUT_GET ) : array ();
        $this->cookie = $_COOKIE ? self::filter_special_chars ( $_COOKIE, INPUT_COOKIE ) : array ();
        $this->env = $_ENV ? self::filter_special_chars ( $_ENV, INPUT_ENV ) : array ();
        $this->server = $_SERVER ? self::filter_special_chars ( $_SERVER, INPUT_SERVER ) : array ();
        $this->uuid = uniqid ();
    }

    /**
     * 每次请求的唯一uuid
     * @return string
     */
    public function uuid() {
        return $this->uuid;
    }

    /**
     * 返回YZE_Request 实例
     * @return YZE_Request
     */
    public static function get_instance() {
        if(!isset(self::$me)){
            $c = __CLASS__;
            self::$me = new $c;
        }
        return self::$me;
    }
    private function _init($newUri) {
        if (! $newUri) {
            $this->uri = parse_url ( $_SERVER ['REQUEST_URI'], PHP_URL_PATH );
            $this->full_uri = $_SERVER ['REQUEST_URI'];
            $this->queryString = @$_SERVER ['QUERY_STRING'];
        } else {
            $this->uri = parse_url ( $newUri, PHP_URL_PATH );
            $this->full_uri = $newUri;
            $this->queryString = parse_url ( $newUri, PHP_URL_QUERY );
        }
        $uri = urldecode ( $this->uri );
        $uri = \yangzie\YZE_Hook::do_hook ( YZE_HOOK_FILTER_URI,  $uri) ?? $uri;
        $this->uri = is_array ( $uri ) ? "/" . implode ( "/", $uri ) : $uri;
    }

    /**
     * 初始化请求
     * 解析请求的uri，如果没有传入url，默认解析当前请求的uri
     *
     * @param string $newUri
     * @param string $action 该请求的方法
     * @param string $format 请求返回的格式, 如果uri中没有明确指定格式，则返回该格式
     * @param string $request_method http请求方法
     * @return YZE_Request
     */
    public function init($newUri = null, $action = null, $format = null, $request_method=null) {
        $this->_init ( $newUri );

        if($request_method){
            $this->request_method = $request_method;
        }else{
            $this->request_method = $_SERVER['REQUEST_METHOD'];
        }

        $uri = $this->the_uri ();
        if ($newUri) {
            parse_str ( parse_url ( $newUri, PHP_URL_QUERY ), $args );
            if ($args) {
                $this->get = array_merge ( $this->get, $args );
            }
        }

        $routers = YZE_Router::get_instance ()->get_routers ();

        $config_args = self::parse_url ( $routers, $uri );
        $this->set_vars ( @( array ) $config_args ['args'] );
        if ($format && ! $this->get_var ( "__yze_resp_format__" )) {
            $this->set_var ( "__yze_resp_format__", $format );
        }

        $curr_module = null;
        if ($config_args) {
            $controller_name = @$config_args ['controller_name'];
            $curr_module = @$config_args ['module'];
            $curr_action = @$config_args ['action'];
        }

        $action = self::the_val($action ?: $curr_action, "index");
        $method = ($this->is_get() ? "" : $this->request_method."_") . str_replace("-", "_", $action);
        $this->set_method ( $method );

        if (@$curr_module && $controller_name) {
            $this->set_module ( $curr_module )->set_controller_name ( $controller_name );
        } else{
            $this->controller_name = "yze_default";
            $this->controller_class = "Yze_Default_Controller";
            $this->controller = new Yze_Default_Controller ( $this );
        }

        $controller_cls = $this->controller_class ();

        if (! $this->controller_instance ()) {
            throw new YZE_Resource_Not_Found_Exception ( "Controller $controller_cls Not Found" );
        }
        return $this;
    }

    /**
     * 映射的控制器方法
     * @return string
     */
    public function the_method() {
        return $this->method;
    }

    /**
     * 是否是post请求
     * @return bool
     */
    public function is_post() {
        return strcasecmp ( $this->request_method, "post" ) === 0;
    }

    /**
     * 是否是get请求
     * @return bool
     */
    public function is_get() {
        return strcasecmp ( $this->request_method, "get" ) === 0;
    }
    /**
     * 访问当前请求的来源地址
     * @param string $just_path 如果为true只显示uri的path部分
     */
    public function the_referer_uri($just_path = false) {
        $referer = @$_SERVER ['HTTP_REFERER'];
        if (! $just_path) {
            return $referer;
        }
        return parse_url ( $referer, PHP_URL_PATH );
    }

    /**
     * 对当前请求做身份认证处理,options请求不做处理
     *
     * @return YZE_Request
     * @throws YZE_Need_Signin_Exception
     * @throws YZE_Permission_Deny_Exception
     */
    public function auth() {
        // Options请求不做验证
        if (!strcasecmp($this->request_method, 'options')){
            return $this;
        }
        $req_method = $this->the_method ();
        if (!$this->need_auth ( $req_method )) return $this;

        // 需要验证
        $loginuser = YZE_Hook::do_hook ( YZE_HOOK_GET_LOGIN_USER );
        if ( ! $loginuser ) throw new YZE_Need_Signin_Exception (__ ( "Please signin" ));

        $aro = \yangzie\YZE_Hook::do_hook ( YZE_HOOK_GET_USER_ARO_NAME);

        // 验证请求的方法是否有权限调用
        $acl = YZE_ACL::get_instance ();
        $aco_name = "/" . $this->module () . "/" . $this->controller_name ( true ) . "/" . $req_method;

        if (! $acl->check_byname ( $aro, $aco_name )) {
            throw new YZE_Permission_Deny_Exception ( sprintf ( __ ( "You do not have permission(%s:%s)" ), \app\yze_get_aco_desc ( $aco_name ), $aro) );
        }
        return $this;
    }

    /**
     *
     * 取得请求指定的输出格式, 默认为tpl，也就是返回普通的html内容；
     *
     * 可通过后缀的方式指定输出格式，比如/foo/bar.json，/foo/bar.xml，json、xml就是输出格式
     *
     * 其他情况下pc端返回tpl，移动端返回mob
     *
     * @author leeboo
     * @return string
     *
     */
    public function get_output_format() {
        $format = $this->get_var ( "__yze_resp_format__" ); // 指定的输出格式,如http://domain/action.json
        if ($format) {
            return $format;
        } elseif ($this->is_mobile_client ()) { // 客户端是移动设备
            return "mob";
        }
        return "tpl"; // default
    }

    /**
     * 是否是移动端，user agent包含android|iphone|ipad的看作移动端
     * @return false
     */
    public function is_mobile_client() {
        return preg_match ( "/android|iphone|ipad/i", $_SERVER ['HTTP_USER_AGENT'] );
    }

    /**
     * 是否是ios环境，user agent包含iphone|ipad的看作ios环境
     * @return false
     */
    public function is_In_IOS(){
        return preg_match ( "/iphone|ipad/i", $_SERVER ['HTTP_USER_AGENT'] );
    }

    /**
     * 是否是ios环境，user agent包含android的看作android环境
     * @return false
     */
    public function is_In_Android(){
        return preg_match ( "/android/i", $_SERVER ['HTTP_USER_AGENT'] );
    }

    /**
     * 把data_str 格式化成GMT格式
     * @param $date_str
     * @return string
     */
    public static function format_gmdate($date_str) {
        return gmdate ( 'D, d M Y H:i:s', strtotime ( $date_str ) ) . " GMT";
    }
    private function set_method($method) {
        return $this->method = $method;
    }
    private function set_vars($vars) {
        return $this->vars = $vars;
    }
    public function set_var($name, $val) {
        $this->vars [$name] = $val;
        return $this;
    }

    /**
     * 取得var变量，var变量是router中url中正则表示的命名参数
     *
     * @param $key
     * @param $default
     * @return mixed|null
     */
    public function get_var($key, $default = null) {
        $vars = $this->vars;
        return @array_key_exists ( $key, $vars ) ? $vars [$key] : $default;
    }
    /**
     * 当前的请求是否需要认证
     * @return bool true
     */
    private function need_auth() {
        $req_method = $this->the_method();
        $need_auth_methods = $this->get_auth_methods ( $this->controller_name ( true ), "need" );
        $no_auth_methods = $this->get_auth_methods ( $this->controller_name ( true ), "noneed" );

        // 不需要验证
        if ($no_auth_methods && ($no_auth_methods == "*" || preg_match ( "/$no_auth_methods/i", $req_method ))) {
            return false;
        }
        if ($need_auth_methods && ($need_auth_methods == "*" || preg_match ( "/$need_auth_methods/i", $req_method ))) { // 需要验证
            return true;
        }
        return false;
    }
    private function get_auth_methods($controller_name, $type) {
        if (!$this->module_instance ()) return null;
        if ($type == "need") {
            $auth_methods = @$this->module_instance ()->auths [$controller_name];
            if ($auth_methods) return $auth_methods;

            if ($this->module_instance ()->auths=="*" || $this->module_instance ()->auths == ['*']) return '*';
        } elseif ($type == "noneed") {
            $auth_methods = @$this->module_instance ()->no_auths [$controller_name];
            if ($auth_methods) return $auth_methods;

            if ($this->module_instance ()->no_auths=="*" || $this->module_instance ()->no_auths == ['*']) return '*';
        }
        return null;
    }

    /**
     * 根据路由表配置解析当前url，如果路由中没有配置，则根据默认的地址格式解析：/module/controller/vars.format
     *
     * @param array $routers 路由表
     * @param string $uri 地址
     *
     * @return array 格式('controller_name'=>, 'module'=>, 'args'=>)
     */
    public static function parse_url($routers, $uri) {
        $_ = array ();
        foreach ( $routers as $module => $router_info ) {
            foreach ( $router_info as $router => $acontroller ) {
                $router = ltrim($router, '/');
                if (preg_match ( "#^/{$router}\.(?P<__yze_resp_format__>[^/]+)$#i", $uri, $matches ) || preg_match ( "#^/{$router}/?$#i", $uri, $matches )) {
                    $_ ['controller_name'] = strtolower ( $acontroller ['controller'] );
                    $_ ['module'] = $module;
                    $_ ['action'] = @$acontroller ['action'];
                    $config_args = $matches;
                    foreach ( ( array ) @$acontroller ['args'] as $name => $value ) {
                        $config_args [$name] = $value;
                    }
                    $_ ['args'] = @$config_args;

                    return $_;
                }
            }
        }

        // 默认按照 /module/controller/action/var/ 解析
        $str = trim ( $uri, "/" );
        $format_pos = strripos ( $str, "." );

        if ($format_pos === false) {
            $uri_split = explode ( "/", $str );
        } else {
            $uri_split = explode ( "/", substr ( $str, 0, $format_pos ) );
        }

        // 把controller-name 转换成controller_name
        if (@$uri_split [1]) {
            $path = self::the_val ( str_replace ( "-", "_", $uri_split [1] ), "index" );
            $_ ['controller_name'] = pathinfo ( $path, PATHINFO_FILENAME );
            $_ ['module'] = strtolower ( $uri_split [0] );
        } else {
            $_ ['module'] = pathinfo ( $uri_split [0], PATHINFO_FILENAME );
            $_ ['controller_name'] = "index";
        }

        if (count ( $uri_split ) > 3) {
            $_ ['args'] = array_slice ( $uri_split, 3 );
        }
        if (count ( $uri_split ) > 2) {
        	if(! is_numeric($uri_split[2])){
        		$_ ['action'] = $uri_split[2];
        	}else{
        		$_ ['args'][] = $uri_split[2];
        	}
        }
        if (preg_match ( "#\.(?P<__yze_resp_format__>[^/]+)$#i", $uri, $matches )) {
            $_ ['args'] ["__yze_resp_format__"] = $matches ['__yze_resp_format__'];
        }
        return $_;
    }

    /**
     * 处理请求，把控制交给具体控制器的具体方法
     * @return mixed
     * @throws YZE_Resource_Not_Found_Exception
     */
    public function dispatch() {
        $controller = $this->controller;

        $method = $this->the_method();
        if (! method_exists ( $controller, $method )) {
            throw new YZE_Resource_Not_Found_Exception ( $this->controller_class . "::" . $method . " 不存在" );
        }

        return $controller->handle_request();
    }
    private function set_controller_name($controller) {
        $this->controller_class = self::format_class_name ( $controller, "Controller" );
        $this->controller_name = $controller;
        if (class_exists ( $this->controller_class )) {
            $this->controller = new $this->controller_class ( $this );
            return $this;
        }

        $class = "\\app\\" . $this->module () . "\\" . $this->controller_class;

        if (class_exists ( $class )) {
            $this->controller = new $class ( $this );
        }

        return $this;
    }
    /**
     * 控制器名字,如\app\module_name\controller_name，如果返回短格式，则返回controller_name
     *
     * @param bool $is_sort true 返回短格式
     * @return string
     */
    public function controller_name($is_sort = false) {
        if (! $this->module ())
            return "";
        return $is_sort ? $this->controller_name : "\\app\\" . $this->module () . "\\" . $this->controller_name;
    }

    /**
     * 控制器类名,如\app\module_name\controller_name_Controller，如果返回短格式，则返回controller_name
     *
     * @param bool $is_sort true 返回短格式
     * @return string
     */
    public function controller_class($is_sort = false) {
        if (! $this->module ())
            return "";
        return $is_sort ? $this->controller_class : "\\app\\" . $this->module () . "\\" . $this->controller_class;
    }
    /**
     * 控制器对象
     *
     * @author leeboo
     *
     * @return YZE_Resource_Controller
     */
    public function controller_instance() {
        return $this->controller;
    }
    public function set_module($module) {
        $this->module = $module;
        $this->module_class = YZE_Object::format_class_name ( $module, "Module" );

        if (class_exists ( $this->module_class )) {
            $this->module_obj = new $this->module_class ();
            return $this;
        }

        $class = "\\app\\" . $module . "\\" . $this->module_class;

        if (class_exists ( $class )) {
            $this->module_obj = new $class ();
        }
        return $this;
    }

    /**
     * 返回模块名
     * @return mixed
     */
    public function module() {
        return $this->module;
    }
    /**
     * 返回模块类名
     * @return YZE_Base_Module
     */
    public function module_class() {
        return $this->module_class;
    }
    /**
     * 模块的配置对象
     * @return YZE_Base_Module;
     */
    public function module_instance() {
        return $this->module_obj;
    }
    /**
     * 返回当前请求的模块views目录，注意结尾无/
     * @return string
     */
    public function view_path() {
        $info = \yangzie\YZE_Object::loaded_module ( $this->module () );
        if ($info ['is_phar']) {
            return "phar://" . YZE_APP_PATH . "modules/" . $this->module () . ".phar/views";
        } else {
            return YZE_APP_PATH . "modules/" . $this->module () . "/views";
        }
    }

    /**
     *
     * @return \Exception
     */
    public function get_exception(){
    	return $this->exception;
    }
    public function set_Exception(\Exception $exception){
    	$this->exception = $exception;
    }

    /**
     * 返回前端支持的语言
     * @return string
     */
    public function get_Accept_Language(){
        preg_match("/(?P<lang>[^,]+),/",@$_SERVER['HTTP_ACCEPT_LANGUAGE'], $matchs);
        return $matchs ? strtolower($matchs['lang']) : '';
    }
}
?>
