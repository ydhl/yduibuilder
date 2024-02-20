<?php
namespace app\build;

use app\project\Page_Bind_Style_Model;
use app\project\Page_Model;
use app\project\Project_Model;
use app\project\Project_Setting_Model;
use app\project\Style_Model;

use app\vendor\css\Css_Factory;
use yangzie\YZE_Resource_Controller;

/**
 * 构建预览，导出代码用到的设置属性
 *
 * @package build
 */
class Build_Model{
	public $indentSpaceSize = 4;
	/**
	 * @var Page_Model
	 */
	private $page;
	/**
	 * api 环境，如正式环境，测试环境
	 * @var string
	 */
	private $api_env;
	/**
	 * api 环境配置，格式[环境名称=>url]
	 * @var string
	 */
	private $api_env_config;
	/**
	 * @var 当前需要构建的UIBase结构体
	 */
	private $uiConfig;
	/**
	 * @var Project_Model 项目的设置列表
	 */
	private $project;
	/**
	 * @var 当前组件输出代码的缩进量
	 */
	private $indent;

	private $cssTranslate;

	/**
	 * 编译成代码时资源的根目录
	 */
	private $rootPath;
	/**
	 * 编译成代码时的图片资源目录
	 */
	private $imgAssetPath;
	private $controller;
	/**
	 * @var string 当前元素在上级元素的那个位置（由上级元素确定）删除
	 */
	private $inParentPlacement;
	/**
	 * @var Css_Factory
	 */
	private $cssFactory;
	private $events=[];
	private $ioBinds=[];
	private $dataBinds=[];
	private $styleBinds=[];
	private $styles=[];
	private $bindApis=[];
	private $indentWithTab;
	private $idSuffix='';

	/**
	 * @return array<Style_Model>
	 */
	public function get_styles() {
		return array_values($this->styles);
	}
	/*
	 * 返回指定的ui引用的selector 名称;
	 * 中的css里面的内容
	 * @return array
	 */
	public function get_ui_style_selectors($uiid) {
		$selector = [];
		foreach ((array)$this->styleBinds[$uiid] as $styleID){
			$selector[] = $this->styles[$styleID]->class_name;
		}
		return $selector;
	}
	/*
	 * 返回指定的ui引用的selector中的其他css class name， 比如 {"style":{"display":"flex"},"css":{"padding":"p-3"}}
	 * 中的css里面的内容
	 * @return array
	 */
	public function get_ui_css_selectors($uiid) {
		$selector = [];
		foreach ((array)$this->styleBinds[$uiid] as $styleID){
			$meta = json_decode(html_entity_decode($this->styles[$styleID]->meta), true);
			if (!$meta['css']) continue;
			$selector[] = array_merge($selector, $meta['css']);
		}
		return $selector;
	}
	public function get_api_env(){
		return $this->api_env;
	}
	public function set_api_env($api_env){
		$this->api_env = $api_env;
	}
	public function get_id_suffix(){
		return $this->idSuffix;
	}
	public function set_id_suffix($idSuffix){
		$this->idSuffix = $idSuffix;
	}
	public function set_Css_Factory($cssFactory){
		$this->cssFactory = $cssFactory;
	}
	public function set_Css_Translate($cssTranslate){
		$this->cssTranslate = $cssTranslate;
	}
	public function is_indent_with_tab(){
		return $this->indentWithTab;
	}
	public function get_Css_Factory(){
		if (!$this->cssFactory){
			$this->cssFactory = Css_Factory::getFactory($this->get_ui());
		}
		return $this->cssFactory;
	}
	public function get_Css_Translate(){
		if (!$this->cssTranslate){
			$this->cssTranslate = $this->get_Css_Factory()->cssTranslat($this->get_ui_Version());
		}
		return $this->cssTranslate;
	}

	/**
	 * @param $controller YZE_Resource_Controller 当前请求控制器
	 * @param $page Page_Model 当前页面
	 * @param $indent number 开始缩进 默认1
	 * @param $rootPath string 项目根路径 默认/
	 * @param $uiConfig array | null 当前uicofnig配置，默认null，从page中取
	 * @param $indentWithTab boolean 是否用tab缩进，默认false用空格缩进
	 * @return void
	 */
	public function __construct($controller, $page, $indent=1, $rootPath='/', $uiConfig=null, $indentWithTab=false) {
		$this->controller = $controller;
		$this->indent = $indent;
		$this->rootPath = $rootPath;
		$this->indentWithTab = $indentWithTab;
		$this->page = $page;
		if (is_null($uiConfig) && $this->page){
			$this->uiConfig = json_decode(json_encode($this->page->get_config()), true);
		}else{
			$this->uiConfig = $uiConfig;
		}
		if($page) $this->project = $this->page->get_project();
		$this->init_data();
	}

	/**
	 * 调用该方法前需要先设置page
	 * @return void
	 */
	public function init_data(){
		if (!$this->page) return; // 直接编译style（单独自定义公用的style selector）、或者编译一个项目的公用代码时候没page
		$this->styleBinds = [];
		$this->styles = [];

		foreach (Page_Bind_Style_Model::from('bs')
					 ->left_join(Style_Model::CLASS_NAME,'s', 's.id=bs.style_id')
					 ->where('bs.page_id=:pid and bs.is_deleted=0 and s.is_deleted=0')
					 ->select([':pid'=>$this->page->id]) as $item){
			$this->styleBinds[$item['bs']->uiid][] = $item['s']->uuid;
			$this->styles[$item['s']->uuid] = $item['s'];
		}
	}
	public function get_page(){
		return $this->page;
	}
	public function set_page(Page_Model $page){
		$this->page = $page;
		return $this;
	}
	public function get_project(){
		return $this->project;
	}
	public function set_project(Project_Model  $project){
		$this->project = $project;
		return $this;
	}
	public function get_ui_config(){
		return $this->uiConfig;
	}
	public function set_ui_config($uiconfig){
		$this->uiConfig = $uiconfig;
	}
	public function get_event($uiid){
		return $this->events[$uiid]?:[];
	}
	public function get_ui(){
		return $this->project->get_setting_value('ui');
	}
	public function get_ui_Version() {
		return $this->project->get_setting_value('ui_version');
	}
	public function get_front_Framework(){
		return $this->project->get_setting_value('frontend_framework');
	}
	public function get_front_Framework_Version(){
		return $this->project->get_setting_value('frontend_framework_version');
	}
	public function get_frontend(){
		return $this->project->get_setting_value('frontend');
	}
	public function get_root_path(){
		return $this->rootPath;
	}
	public function increase_indent($indent){
		return $this->indent += $indent;
	}
	public function get_indent(){
		return $this->indent;
	}
	public function set_indent($indent){
		$this->indent = $indent;
		return $this;
	}
	public function set_img_Asset_Path($imgAssetPath){
		$this->imgAssetPath = $imgAssetPath;
		return $this;
	}
	public function get_img_Asset_Path(){
		return $this->imgAssetPath;
	}
	public function get_controller(){
		return $this->controller;
	}
	public function set_controller($controller){
		$this->controller = $controller;
	}
	public function set_in_Parent_Placement($inParentPlacement){
		$this->inParentPlacement = $inParentPlacement;
		return $this;
	}
	public function get_in_Parent_Placement(){
		return $this->inParentPlacement;
	}
	public function get_api_base(){
		if (!$this->api_env_config) {
			$this->api_env_config = Project_Setting_Model::get_setting_value($this->project->id, 'api_env');
		}
		return $this->api_env_config[$this->api_env] ? rtrim($this->api_env_config[$this->api_env],"/")."/" : "";
	}

	/**
	 * 找到fromuuid中的输入或者输出数据项关联的ui id
	 * @param $fromUuid string 数据关联的对象uuid，可能是Page_Bind_API或Page_Bind_Event,Page_Bind_Data
	 * @param $inOut string in或者out
	 * @param $dataConfig array 数据项配置结构体
	 * @return mixed
	 */
	public function get_uiid_of_data($fromUuid, $inOut, $dataConfig){
		return $this->ioBinds[$fromUuid][$inOut][$dataConfig['uuid']];
	}
	public function clone($page=null){
		if($page){
			$build = new Build_Model($this->controller, $page, $this->indent, $this->rootPath, null, $this->indentWithTab);
			$build->set_Css_Translate($this->cssTranslate);
			$build->set_img_Asset_Path($this->imgAssetPath);
			$build->set_in_Parent_Placement($this->inParentPlacement);
			$build->set_Css_Factory($this->cssFactory);
		}else{
			$build = clone $this;
		}
		return $build;
	}

	public function output_code($codes, $indent=null){
		if (!$codes) return;
		$lines = $this->indent_code(isset($indent) ? $indent: $this->get_indent(), $codes);
		echo join(PHP_EOL, $lines).PHP_EOL;// 每行代码后面加个换行
	}

	/**
	 * 对给定对codes里面对代码进行缩进，并返回代码数组，每项一句代码
	 * @param $indent integer 缩进
	 * @param $codes string | array 可以是代码组成的数组或者代码字符串
	 * @return string[]
	 */
	public function indent_code($indent, $codes){
		if (!$codes) return [];
		if ($this->is_indent_with_tab()){
			$indent =  str_repeat("\t", $indent * $this->indentSpaceSize);
		}else{
			$indent =  str_repeat(' ', $indent * $this->indentSpaceSize);
		}

		$lines = array_map(function ($code) use($indent){
			$lines = explode(PHP_EOL, $code);
			return join(PHP_EOL,array_map(function ($line) use($indent){
				if (!trim($line)) return;
				return $indent.$line;
			},$lines));
		}, (array)$codes);
		return $lines;
	}
}?>
