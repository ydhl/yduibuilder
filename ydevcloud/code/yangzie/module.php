<?php
namespace yangzie;

/**
 * 模块配置基类，整个yangzie app也是一个module，
 * 每个module可以通过check方法检查该模块运行必须要满足的条件，
 * 通过config返回模块的配置，比如routers
 */
abstract class YZE_Base_Module extends YZE_Object {
	/**
	 * 模块的名字
	 * @var string
	 */
	public $name = "";


	/**
	 * 该模块中需要认证访问的资源,格式：
	 *     [
	 *     '控制器名'=>"action，支持正则"
	 *     ]
	 *
	 * 表示某个资源控制器的某个请求求认证。如
	 *     [
	 *     'resouce_controller_name2'=>"(post_?)add",
	 *     ]
	 *
	 * 需要认证的资源在访问时，框架会调用hook YZE_HOOK_GET_LOGIN_USER，该hook返回非假则表示已经登录，假值则抛出YZE_Need_Signin_Exception异常，并进入YZE_HOOK_YZE_EXCEPTION处理
	 * @var array
	 */
	public $auths = array();

	/**
	 * 同auths，定义不需要认证的action，优先级比auths高
	 *
	 */
	public $no_auths = array();

	/**
	 * 获取指定的模块配置，模块的配置包含了对象属性及通过config方法返回的内容
	 * @param $name
	 * @return array|mixed
	 */
	public function get_module_config($name=null){
		$config = get_class_vars(get_class($this));
		$config = array_merge($config,$this->config());
		return $name ? @$config[strtolower($name)] : $config;
	}

	/**
	 * 返回指定控制器上映射的url列表
	 * @param $controller
	 * @return array
	 */
	public function get_uris_of_controller($controller){
		$controller = rtrim(strtolower($controller), "_controller");
		$config = $this->config();
		$_ = array();
		foreach ($config['routers'] as $uri => $mapping){
			if(strtolower($mapping['controller']) == $controller){
				$_[] = $uri;
			}
		}
		return $_;
	}
	/**
	 * 加载该模块之间做检查, 出错则抛出异常
	 *
	 * @author leeboo
	 * @throws YZE_RuntimeException
	 */
	public function check(){
	}
	/**
	 * 初始化一些配置项的值，返回数组，键为配置名;
	 * module通过config返回路由映射：
	 * <pre>
	 * 格式:
	 * [
	 *   'routers' => [
	 * 	    'uri地址'=>["controller"=>'控制器名', 'aciton'=>'执行的方法',"args"=>["固定参数名"=>"参数值"]]
	 *   ]
	 * ]
	 * 如['/something/(?P<id>\d+)'=>["controller"=>'quote',"args"=>[]]]
	 * uri地址支持正则，并且可命名正则匹配值, 比如上面的id，则可以通过$request->get_var('id')获取地址上的值
	 * 控制器名是不包含controller的，比如quote_controller中的quote
	 * args是固定传入action的参数，也是通过$request->get_var('参数名')获取
	 *
	 * </pre>
	 * @return array
	 */
	protected abstract function config();
    /**
     * js资源分组，在加载时方便直接通过分组名一次性加载所有文件，并支持http缓存机制;<br/><br/>
     * 如果是项目级的资源：<br/>
     *   路径以web 绝对路径/开始，/指的上public_html目录
     *   在layouts中通过接口yze_js_bundle("foo,bar")一次打包加载这里指定的资源<br/><br/>
     * 如果是模块的资源：<br/>
     *   路径以web 绝对路径/开始，/指的上模块下的public_html目录
     *   在layouts中通过接口yze_module_js_bundle("foo,bar")一次打包加载这里指定的资源<br/><br/>
	 * 实现该函数决定如何返回要打包下载的资源
     * @return array(资源路径1，资源路径2)
     */
    public abstract function js_bundle($bundle);
    /**
     * css资源分组，在加载时方便直接通过分组名一次性加载所有文件，并支持http缓存机制;<br/><br/>
     * 如果是项目级的资源：<br/>
     * 资源路径以web 绝对路径/开始，/指的上public_html目录
     * 在layouts中通过接口yze_css_bundle("yangzie,foo,bar")一次打包加载这里指定的资源<br/><br/>
     * 如果是模块的资源：<br/>
     *   路径以web 绝对路径/开始，/指的上模块下的public_html目录
     *   在layouts中通过接口yze_module_css_bundle("yangzie,foo,bar")一次打包加载这里指定的资源<br/><br/>
     * 实现该函数决定如何返回要打包下载的资源
     * @return array(资源路径1，资源路径2)
     */
	public abstract function css_bundle($bundle);
}
?>
