<?php

namespace yangzie;

/**
 * 资源控制器抽象基类，提供控制器的处理机制，子类控制器的action映射到具体的uri，具体处理请求<br/>
 * 同一个url的request method映射到不同的action，<br/>
 * 比如GET /user 映射到User_Controller:index<br/>
 * 比如POST /user 映射到User_Controller:post_index<br/>
 * 比如DELETE /user 映射到User_Controller:delete_index<br/>
 * 也就是非get请求，则在action前面加上REQUEST_METHOD_<br/>
 * <br/><br/>
 * 对于OPTIONS请求，由于OPTIONS不是请求具体的业务逻辑只是对服务器的询问，只需要返回对应的header，任何实际输出内容都会被忽略，
 * 所以不需要有对应的options_action方法，只需要在request_headers中根据options询问的情况进行应答即可<br/>
 * 比如Access-Control-Request-Headers: content-type,x-product,<br/>
 * Access-Control-Request-Method: POST<br/>
 * 那么只需要返回对应的允许的header即可：Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect, x-product
 * <br/><br/>
 * 可通过request->get_from_server()来获取http的头部，但是有所区别，
 * 比如如果request的headers是Access-Control-Request-Headers: content-type,x-product
 * 那么则这样取：$request->get_from_server('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
 *
 * @category Framework
 * @package Yangzie
 * @author liizii
 * @link yangzie.yidianhulian.com
 */
abstract class YZE_Resource_Controller extends YZE_Object {
    protected $view_data = array ();
    protected $layout = 'tpl';
    protected $view = "";

    /**
     * @var YZE_Request
     */
    protected $request;
    /**
     * 所在模块
     * @var YZE_Base_Module
     */
    protected $module;
    /**
     * 返回当前请求对响应对象
     *
     * @author leeboo
     * @param string $view_tpl 模板的路径
     * @param string $format
     * @return \yangzie\YZE_Simple_View
     */
    private function get_Response($view_tpl = null, $format = null) {
        $request = $this->request;
        $method  = $request->the_method();
        if(!$request->is_get()){
            $method = preg_replace("/[^_]+?_/", "", $method, 1);
        }

        $view_data  = $this->view_data;

        if (!$view_tpl){
            $class_name = strtolower ( get_class ( $this ) );
            $ref  = new \ReflectionObject ( $this );
            if($this->view){
                $tpl  = $this->view;
            }else{
                $tpl  = substr ( str_replace ( $ref->getNamespaceName () . "\\", "", $class_name ), 0, - 11 ) . "-" . $method;
            }

            $view = $request->view_path () . "/" . $tpl;
        }else{
            $view = $view_tpl;
        }

        if (! $format) {
            $format = $request->get_output_format ();
        }
        return new YZE_Simple_View ( $view, $view_data, $this, $format );
    }

    public function __construct($request = null) {
        $this->request = $request ?: YZE_Request::get_instance ();
        $this->module = $this->request->module_instance ();
        // init layout
        if ($this->request->get_output_format ()) {
            $this->layout = $this->request->get_output_format ();
        }
    }

    /**
     * 当前请求实例
     * @return YZE_Request
     */
    public function get_Request() {
        return $this->request;
    }
    /**
     * 布局名，比如tpl，则对应对是app/vendor/layouts/tpl.layout.php文件
     * @return string
     */
    public function get_Layout() {
        return $this->layout;
    }
    public function set_View_Data($name, $value) {
        $this->view_data [$name] = $value;
        return $this;
    }

    public function get_View_Data($name) {
        return @$this->view_data [$name];
    }


    /**
     * 子类重载设置响应头，可根据当前请求的信息做出区别对待
     * 比如
     * <pre>
     * [
     * "Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, token, Redirect",
     * "Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH",
     * "Access-Control-Allow-Origin: *"
     * ]
     * </pre>
     */
    public function response_headers(){
        return [];
    }

    /**
     * 调用映射的action方法并返回响应
     * @return YZE_Redirect|YZE_Simple_View
     */
    public final function handle_request(){
        $request = $this->request;
        $method = $request->the_method ();
        $redirect = new YZE_Redirect ( $request->the_full_uri (), $this, $this->view_data );

        $response = $this->$method ();
        if (! $response) {
            $response = $this->get_Response ();
        }

        $format = $request->get_output_format();

        if (strcasecmp ( $format, "json" ) == 0) {
            $this->layout = "";
        }
        return $response?:$redirect;
    }

    /**
     * 在action处理过程中出现的异常进入该方法，之类需要重载exception方法做具体的异常处理
     *
     * @param \Exception $e
     * @return YZE_IResponse|YZE_JSON_View|YZE_Simple_View
     */
    public final function do_exception(\Exception $e) {
        $request = $this->request;
        $request->set_Exception($e);
        \yangzie\YZE_Hook::do_hook ( YZE_HOOK_BEFORE_DO_EXCEPTION, $this );
        $format = $request->get_output_format();
        $response = $this->exception ( $e );

        if (strcasecmp ( $format, "json" ) == 0) {
            $this->layout = "";
            return YZE_JSON_View::error($this, $e->getMessage(), $e->getCode());
        }else if (! $response) {
            $this->set_View_Data ( "exception", $e );
            $response = $this->get_Response ( YZE_APP_VIEWS_INC . "500" );
        }

        return $response;
    }

    /**
     * 子类重载该方法对请求处理过程中出现对异常进行处理
     *
     * @author leeboo
     * @param Exception $e
     * @return YZE_IResponse
     */
    public function exception(\Exception $e) {
    }

    /**
     * 获取action上指定注解的值，
     * <pre>
     * //@ test testvalue
     * public function index()
     * get_Annotation('index', 'test') 将返回testvalue
     * </pre>
     * @param string $action 方法名
     * @param string $annotation 检查对注解
     */
    public function get_Annotation($action, $annotation){
        try{
            $ref = new \ReflectionObject ($this);
            $methodRef = $ref->getMethod($action);
            if (!$methodRef) return null;

            $comment = $methodRef->getDocComment();
            preg_match("/@{$annotation}\s(?P<name>.+)/i", $comment, $matches);
            return @$matches['name'] ?: null;
        }catch (\Exception $e){
            return null;
        }
    }

    /**
     * 判断action上是否有指定注解
     * @param string $action 方法名
     * @param string $annotation 检查对注解
     */
    public function has_Annotation($action, $annotation){
        try{
            $ref  = new \ReflectionObject ( $this );
            $methodRef = $ref->getMethod($action);
            if (!$methodRef) return false;

            $comment = $methodRef->getDocComment();
            return preg_match("/@{$annotation}/i", $comment) ? true : false;
        }catch (\Exception $e) {
            return false;
        }
    }

}
class Yze_Default_Controller extends YZE_Resource_Controller {
    public function index() {
        $this->set_View_Data ( "yze_page_title", __ ( "Yangzie Framework" ) );
        return new YZE_Simple_View ( YANGZIE . "welcome", $this->view_data, $this );
    }
}
class YZE_Exception_Controller extends YZE_Resource_Controller {
    private $exception;

    public function index() {
        $this->layout = "error";
        $this->output_status_code ( $this->exception ? $this->exception->getCode () : 0 );

        if (! $this->exception) {
            return new YZE_Simple_View ( YZE_APP_VIEWS_INC . "500", array (
                    "exception" => $this->exception
            ), $this );
        }

        $errorCode = $this->exception->getCode ();
        if (!file_exists(YZE_APP_VIEWS_INC . $errorCode .".tpl.php")){
            $errorCode = 500;
        }
        return new YZE_Simple_View ( YZE_APP_VIEWS_INC . $errorCode, array (
                "exception" => $this->exception
        ), $this );
    }
    public function exception(\Exception $e) {
        $this->exception = $e;
        return $this->index ();
    }


    private function output_status_code($error_number) {
        switch ($error_number) {
            case 404 :
                header ( "HTTP/1.0 404 Not Found" );
                return;
            case 500 :
            default:
                header ( "HTTP/1.0 500 Internal Server Error" );
        }
    }
}
?>
