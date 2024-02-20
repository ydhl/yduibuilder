<?php
namespace app\modules\build\views\preview;
use app\build\Build_Model;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\wxmp\Wxmp_Code_Fragment;
use app\project\Page_Bind_Style_Model;
use app\project\Page_Model;
use app\project\Style_Model;
use app\vendor\css\Css_Factory;
use yangzie\YZE_FatalException;
use yangzie\YZE_JSON_View;
use yangzie\YZE_Notpl_View;
use yangzie\YZE_Simple_View;
use yangzie\YZE_View_Component;
use function yangzie\__;

/**
 * View的基类，封装基础逻辑处理，对于基于web的代码编译，其中的内容可以基本重用，
 * 比如web，wxmp其输出的都是类似于html的格式和css样式，其data是uibase结构体，分别通过如下方法编译得到目标代码：
 * <ol>
 * <li>build_popup: 编译得到弹窗ui，比如html</li>
 * <li>build_ui: 编译得到ui代码，比如html, 输出ui时通过YZE_View->output()输出</li>
 * <li>build_style: 编译得到ui的样式代码，比如html style</li>
 * <li>build_code: 编译得到功能代码，比如事件绑定</li>
 * </ol>
 * 以上编译是独立的，所以不要交叉使用，比如不要在build_ui中生成style或者code
 * @package app\api\views
 */
abstract class Preview_View extends \yangzie\YZE_View_Component{
    /**
     * @var Build_Model
     */
    protected $build;
    private $parentUI;
    private $parentIndex;
    private $subPage;
    /**
     * @var 组件的样式数组，格式[selector=>[styleName=>styleValue]]
     */
    private $styles = [];
    /**
     * @var array Preview_View
     */
    protected $childViews = [];
    /**
     * 元素上的属性数组 [属性名=>属性值1]
     * @var array
     */
    private $_attrs = [];
    private $_pages;

    public function __construct($data, $controller, Build_Model $build)
    {
        parent::__construct($data, $controller);

        $this->build = $build;
        $this->cssTranslate = $build->get_Css_Translate();
        foreach ((array)@$this->data['items'] as $index => $item){
            $build = $this->build->clone();
            $build->set_ui_config($item);
            $subPageId = @$item['subPageId'];
            // 如果该组件的内容是引用一个组件页面; 因为顶层的build已经把该页面所有的uibase拉取出来了
            // 这里只需要init data重新拉取子页关联的数据即可
            if ($subPageId){
                $subPage = $this->get_page($subPageId);
                if ($subPage){
                    $build->set_page($subPage);
                    //$this->myid() 这是this 是ui component他的id是唯一的
                    $build->set_id_suffix("_".$this->myid()."_{$index}");
                    $build->init_data();
                }
            }

            $build->increase_indent(1);
            $childView = self::create_View($build);
            $this->childViews[] = $childView;
        }
    }

    /**
     * 当前组件及其下级组件在原有的indent但基础上增加indent的缩进
     * @param number $indent
     * @return void
     */
    protected function increase_indent($indent){
        $this->build->increase_indent($indent);
        foreach ($this->childViews as $view){
            $view->increase_indent($indent);
        }
    }

    protected function get_page($pageId) {
        $page = $this->_pages[$pageId];
        if (!$page)$page = find_by_uuid(Page_Model::CLASS_NAME, $pageId);
        $this->_pages[$pageId] = $page;
        return $page;
    }
    protected function get_Img_Src($imgSrc){
        return $imgSrc;
    }

    private function fetch_css ($cssInfo, &$cssArray) {
        if (!@$cssInfo) return;

        foreach ((array)@$cssInfo as $name => $css){
            if (is_array($css)){
                $_arr = [];
                foreach ($css as $sub_key => $sub_css){
                    $compiled_string = $this->cssTranslate[$name][$sub_key][$sub_css] ?: $sub_css;
                    if ($compiled_string)$_arr[] = $compiled_string;
                }
                $cssArray[$name] = join(" ", $_arr);
            }else{
                // leeboo 如果不是预定义的（没有翻译结果），则直接用他，常见于某些地方自己硬性定义的css，比如modal的move-handler
                $css =  isset($this->cssTranslate[$name][$css]) ? $this->cssTranslate[$name][$css] : $css;
                if (!$css)continue;
                $cssArray[$name] = $css;
            }
        }
    }
    /**
     * 元素上的css样式字符串
     * @var string
     */
    protected function css_map() {
        $cssArray = [];
        $this->fetch_css($this->data['meta']['css'], $cssArray);
        $this->fetch_css($this->build->get_ui_css_selectors($this->myid()), $cssArray);

        // 引用的style selector
        $selector = $this->build->get_ui_style_selectors($this->myid());
        if ($selector){
            $cssArray['__selector__'] = join(" ", $selector);
        }
        return $cssArray;
    }
    /**
     * 元素上的css样式字符串
     * @var string
     */
    protected function get_css() {
        $cssArray = $this->css_map();
        return $cssArray ? join(' ', array_values($cssArray)) : '';
    }
    /**
     * style字符串, key是style的属性名，值是完整的style属性设置，
     * 比如['color'=>'color:#fff']
     *
     * @return array
     */
    public function get_style($meta){
        if (!$meta) return [];
        $styles = [];
        $metaStyle = (array)@$meta['style'];
        // 每个角单独设置的优先级最高
        $roundSize = $metaStyle['border-radius'];
        if ($roundSize) {
            if (!$metaStyle['border-top-left-radius']) $metaStyle['border-top-left-radius'] = $roundSize;
            if (!$metaStyle['border-top-right-radius']) $metaStyle['border-top-right-radius'] = $roundSize;
            if (!$metaStyle['border-bottom-left-radius']) $metaStyle['border-bottom-left-radius'] = $roundSize;
            if (!$metaStyle['border-bottom-right-radius']) $metaStyle['border-bottom-right-radius'] = $roundSize;
            unset($metaStyle['border-radius']);
        }

        foreach ($metaStyle as $name => $value){
            if (is_array($value)){
                $styles[$name] = $value;
                continue;
            }
            $value = trim($value);
            if (strlen($value)==0) continue;
            $styles[$name] = $name.': '.$value;

            if ($name == 'text-stroke') {
                $styles['-webkit-text-stroke'] = '-webkit-text-stroke: '.$value;
            }
        }

        // 字体属性处理
        $decoration = [];
        if (@$meta['custom']['underline']) {
            $decoration[] = 'underline';
        }
        if (@$meta['custom']['through']) {
            $decoration[] = 'line-through';
        }
        if ($decoration) {
            $styles['text-decoration'] = "text-decoration:".join(' ', $decoration);
        }
        if (@$meta['custom']['align']) {
            $styles['text-align'] = "text-align:".$meta['custom']['align'];
        }
        if (@$meta['custom']['italic']) {
            $styles['font-style'] = 'font-style:italic';
        }
        if (@$meta['custom']['bold']) {
            $styles['font-weight'] = "font-weight:".strtolower($meta['custom']['bold']);
        }
        if (@$meta['custom']['font-family']) {
            $styles['font-family'] = 'font-family:"'.$meta['custom']['font-family']['uuid'].'"';
        }
//        print_r($styles);
        // 背景图片的处理, 背景图片在style格式中是数组，那么需要对他们进行合并
        unset($styles['background-image'],$styles['background-repeat'],
            $styles['background-clip'],$styles['background-origin'],
            $styles['background-attachment'],$styles['background-position'],
            $styles['background-size']);
        $styles = array_merge($styles, $this->background_style($metaStyle));
        $styles = array_map(function ($item){
            return $item." !important";
        }, $styles);
        return $styles;
    }

    /**
     * 元素上自定义style字符串, key是style的属性名，值是完整的style属性设置，
     * 比如['color'=>'color:#fff']
     *
     * @return array
     */
    protected function style_map() {
        return $this->get_style($this->data['meta']);
    }
    /**
     * 元素引用的selector包含的style字符串, key是style的属性名，值是完整的style属性设置，
     * 比如['color'=>'color:#fff']
     *
     * @return array
     */
    protected function common_style_map() {
        return $this->get_style($this->data['meta']['selector']);
    }

    private function get_gradient_style($gradientInfo){
        $colors = [];
        if ($gradientInfo['stops']) {
            foreach ($gradientInfo['stops'] as $index => $stop) {
                $colors[] = $stop.' '.$gradientInfo['colorSize'][$index];
          }
        }
        if ($gradientInfo['type'] === 'radial') {
            $_ = [($gradientInfo['repeat'] ? 'repeating-' : '') . 'radial-gradient('];
          // shape size at position, color stop
          if ($gradientInfo['sizeCustom'] > 0) { // eg 20% 40% at 50% 50%
                $_[] = join(' ', $gradientInfo['sizeCustom']);
          } else { // eg circle farthest-corner at 50% 50%
                $_[] = $gradientInfo['shape'] ?: 'ellipse';
            $_[] = $gradientInfo['size'] ?: 'farthest-corner';
          }
          if ($gradientInfo['position']) {
                $_[] = 'at ' . join(' ', $gradientInfo['position']);
          }
          $_[] = ',' . join(',', $colors);
          $_[] = ')';
          return join(' ', $_);
        }
        return ($gradientInfo['repeat'] ? 'repeating-' : '')."linear-gradient(".($gradientInfo['direction'] ?: '0')."deg, ".join(',',$colors).')';
    }
    /**
     * style 都是单独编译输出的，而像微信小程序平台，不允许wxss中有本地图片地址引用，
     * 提供一个独立的方法给具体的平台重载
     * @param $metaStyle
     * @return array
     */
    protected function background_style($metaStyle){
        $styles = [];
        if ($metaStyle['background-image']) {
            $repeat = []; $clip = []; $origin = []; $attachment = []; $position = []; $size = [];
            $formatedImages = [];
            foreach($metaStyle['background-image'] as $index => $img){
                if (!$img['url'] && !$img['gradient']) {
                    continue;
                }
                if ($img['type'] === 'image') {
                    if (!$img['url']) continue;
                    $formatedImages[] = "url(".$this->get_Img_Src($img['url']).")";
                } else if ($img['type'] === 'gradient') {
                    if (!$img['gradient']) continue;
                    $formatedImages[] = $this->get_gradient_style($img['gradient']);
                }
                $repeat[] = $metaStyle['background-repeat'][$index];
                $clip[] = $metaStyle['background-clip'][$index];
                $origin[] = $metaStyle['background-origin'][$index];
                $attachment[] = $metaStyle['background-attachment'][$index];
                $position[] = $metaStyle['background-position'][$index];
                $size[] = $metaStyle['background-size'][$index];
            }
            if ($formatedImages) $styles['background-image'] = 'background-image:'.join(',', $formatedImages);
            if ($repeat) $styles['background-repeat'] = 'background-repeat:'.join(',', $repeat);
            if ($clip) $styles['background-clip'] = 'background-clip:'.join(',', $clip);
            if ($origin) $styles['background-origin'] = 'background-origin:'.join(',', $origin);
            if ($attachment) $styles['background-attachment'] = 'background-attachment:'.join(',', $attachment);
            if ($position) $styles['background-position'] = 'background-position:'.join(',', $position);
            if ($size) $styles['background-size'] = 'background-size:'.join(',', $size);
        }
        return $styles;
    }

    /**
     * 元素上的style字符串, 每个组件由其style_map返回该元素的样式字符串，但这些样式字符串应用到ui元素的那个部分，子类可以重载该方法来指定
     * 默认情况下，ui元素的所有样式字符串都应用到元素本身，并且以id作为selector
     * @param  $justSelf true 只返回自己的样式字符串， false 返回自己并递归旗下所有元素的样式字符串
     * @return array ['selector'=>'样式字符串']
     */
    public function build_style($justSelf = true){
        // ui 组件被删除了，不编译其style
        if ($this->data['subPageDeleted']){
            return [];
        }
        $key = '[data-uiid='.$this->myId().']';
        if ($this->styles){
            return $justSelf ? [$key =>  $this->styles[$key]] : $this->styles;
        }
        $this->styles = [];
        $styleArray = $this->style_map();
        if ($styleArray) {
            $this->styles[$key] =  join(';'.PHP_EOL, array_values($styleArray)).';';
        }

        if ($this->check_master()){
            $master = $this->master_view;
            $this->styles = array_merge($this->styles, $master->build_style(false));
        }

        foreach ($this->childViews as $view){
            $this->styles = array_merge($this->styles, $view->build_style(false));
        }
        foreach ($this->styles as $key => $styles){
            $this->styles[$key] = is_array($this->styles[$key]) ? array_unique($this->styles[$key]) : $this->styles[$key];
        }
        return $justSelf ? [$key =>  $this->styles[$key]] : $this->styles;
    }

    /**
     * 构建公共的样式
     * @return void
     */
    public function build_common_style(){
        $styles = [];
        foreach ($this->build->get_styles() as $styleModel){
            $styleValues = $this->get_style(json_decode(html_entity_decode($styleModel->meta), true));
            $styles[".".$styleModel->class_name] =  join(';'.PHP_EOL, $styleValues).';';
        }
        return $styles;
    }
    public abstract function build_ui();

    public abstract function get_code_fragment(): Base_Code_Fragment;

    /**
     * 返回组件的逻辑代码
     * 默认情况下，如果是容器，则需要输出所包含的组件
     * @return Base_Code_Fragment
     */
    public function build_code(): Base_Code_Fragment{
        $fragment = $this->get_code_fragment();
        if ($this->data['subPageDeleted']){
            return $fragment;
        }
        $this->build_event_binding_code();
        foreach ((array)@$this->childViews as $view){
            $view->build_code();
            if ($fragment) $fragment->merge($view->get_code_fragment());
        }
        return $fragment;
    }

    /**
     * 输出组件自己的事件绑定的代码, 并放入codefragment中
     */
    protected function build_event_binding_code() {
    }

    /**
     * 弹窗模版输出，各终端根据自己的框架进行输出
     */
    public function build_popup_ui(&$outputPopupIds=[]){
        foreach ($this->childViews as $view){
            $view->build_popup_ui($outputPopupIds);
        }
    }
    protected final function output_component(){
        $this->build_ui();
    }

    /**
     * @param $indent integer 在原有的缩进基础上再缩进多少
     * @param $force boolean 默认false，true时只使用传入的indent，不考虑原来的indent
     * @return string
     */
    public function indent($indent=0, $force=false){
        $indent = $force ? $indent : $this->build->get_indent() + $indent;
        if ($this->build->is_indent_with_tab()){
            return str_repeat("\t", $indent);
        }
        return str_repeat(' ', $indent * $this->build->indentSpaceSize);
    }

    /**
     * true 返回id的完整的id：主要是针对被重复使用的组件，自己的id会重复，这时要唯一获得id，就需要传入true，
     * 这是通过在自己的id上加上自己在父容器的排序来做到唯一识别。
     *
     * false只返回自己的id
     * @param $full boolean
     * @return string
     */
    public function myid($full=false){
        return $this->data['meta']['id'].($full ? $this->get_build()->get_id_suffix() : '');
    }

    public function get_sub_page(){
        if (!$this->subPage){
            $this->subPage = $this->data['subPageId'] ? find_by_uuid(Page_Model::CLASS_NAME, $this->data['subPageId']) : null;
        }
        return $this->subPage;
    }
    /**
     * 查找uuid的父级
     */
    protected function find_parent($uiid, &$index=-1, $parent=null) {
        if (!$parent) $parent = $this->build->get_ui_config();
        foreach ((array)@$parent['items'] as $i => $item){
            if ($item['meta']['id'] == $uiid) {
                $index = $i;
                return $parent;
            }
            $finded = $this->find_parent($uiid, $index, $item);
            if ($finded) {
                return $finded;
            }
        }
        $index = -1;
        return null;
    }


    /**
     * 查找上级信息，
     * @param string $uuid 查找uuid的父级parent, 如果不传，则查找自己的父级
     * @param int $index 自己在父级中的位置
     * @return array 父级uibase
     */
    protected function get_parent_UI(&$index=-1){
        if (!$this->parentUI){
            $this->parentUI = $this->find_parent($this->myId(), $index);
            $this->parentIndex = $index;
        }
        $index = $this->parentIndex;
        return $this->parentUI;
    }

    public function set_build(Build_Model $build) {
        $this->build = $build;
        return $this;
    }

    /**
     * @return Build_Model
     */
    public function get_build() {
        return $this->build;
    }
    protected function get_endKind() {
        return $this->build->get_project()->end_kind;
    }

    /**
     * 添加属性, 属性是指会输出到对应ui元素的结构中的内容，比如<foo id='' style='' data-attr=''> 中的id，style data-attr
     * @param string $name 属性名
     * @param string $value 属性值
     * @param string $seperate 属性值的分割字符
     */
    protected function add_attr($name, $value, $seperate=''){
        if (!$value || !$name) return;
        if (!@$this->_attrs[$name]){
            $this->_attrs[$name] = $value;
            return;
        }
        $this->_attrs[$name] = trim($this->_attrs[$name], $seperate).$seperate.$value;
    }
    /**
     * 输出css，id等基本属性和attr内容， attr内容需要在该方法前调用add_attr先设置
     *
     * <strong style="color:red">注意这部分内容只能在ui元素的主体上进行调用输出，具体每个组件那个部分是主体内容，由
     * 组件自己决定。这意味着在一个ui组件及其上层master，该方法只能被调用一次</strong>
     */
    protected function build_main_attrs() {
        $css = $this->get_css();
        echo $this->wrap_output('class', $css?:NULL);
        echo $this->wrap_output('data-type', $this->data['type']);// 用户前端处理时知道元素的类型
        echo $this->wrap_output('id', $this->myid(true));
        echo $this->wrap_output('data-uiid', $this->myid());
        foreach ($this->_attrs as $name => $value){
            echo $this->wrap_output($name, $value);
        }
    }

    /**
     * 对于表单元素，输出表单特有的属性，比如name，disabled，readonly required placeholder等
     *
     * <strong style="color:red">注意这部分内容只能在具体的表单元素的上进行调用输出，比如input，textarea等</strong>
     * @param false $notOutputId 默认输出表单元素id
     */
    protected function build_form_attrs ($notOutputId=false) {
        if (!$notOutputId) {
            echo ' id="'.$this->myId(true).$this->data['type'].'"';
        }
        echo $this->wrap_output('name', $this->myId(true));
        echo $this->wrap_output('data-uiid', $this->myId().$this->data['type']);

        if (@$this->data['meta']['form']['state']=='disabled'){
            echo ' disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            echo ' readonly';
        }
        if (@$this->data['meta']['form']['required']){
            echo ' required';
        }
        if (@$this->data['meta']['form']['placeholder']) {
            echo $this->wrap_output('placeholder', $this->data['meta']['form']['placeholder']);
        }
    }

    protected function wrap_icon($outputInner, $indent=null, $wrapTag='div', $iconTag='i') {
        $icon = $this->data['meta']['custom']['icon'];
        if (!$icon) {
            echo "\r\n";
            echo $this->indent($indent ?: 1);
            $outputInner();
            return;
        }
        echo "\r\n";
        echo $this->indent($indent ?: 1);
        switch ($this->data['meta']['custom']['icon-position']) {
            case 'top':{
                echo "<{$wrapTag}><{$iconTag} class='{$icon}'></{$iconTag}></{$wrapTag}>\r\n";
                echo $this->indent($indent ?: 1);
                $outputInner();
                return;
            }
            case 'bottom':{
                $outputInner();
                echo "\r\n";
                echo $this->indent($indent ?: 1);
                echo "<{$wrapTag}><{$iconTag} class='{$icon}'></{$iconTag}></{$wrapTag}>";
                return;
            }
            case 'right':{
                $outputInner();
                echo "\r\n";
                echo $this->indent($indent ?: 1);
                echo "<{$iconTag} class='{$icon}'></{$iconTag}>";
                return;
            }
            default:{
                echo "<{$iconTag} class='{$icon}'></{$iconTag}>\r\n";
                echo $this->indent($indent ?: 1);
                $outputInner();
            }
        }
    }

    protected function wrap_output($attr, $data) {
        if (!isset($data)) return '';
        $data = addslashes($data);
        return " {$attr}=\"{$data}\"";
    }
    public static function get_View_Class(array $uiconfig, Build_Model $build){
        $ui = $build->get_ui();
        $type = strtolower($uiconfig['type']);
        return "app\\modules\\build\\views\\preview\\{$ui}\\{$type}_View";
    }
    /**
     * 创建视图
     * @param $build Build_Model 配置
     * @return Preview_View
     */
    public static function create_View(Build_Model $build){
        $uiconfig = $build->get_ui_config();
        $controller = $build->get_controller();
        $class = static::get_View_Class($uiconfig, $build);
        if (!class_exists($class)){
            throw new YZE_FatalException(sprintf(__('%s not found'), $class));
        }
        $view = new $class($uiconfig, $controller, $build);
        return $view;
    }

    /**
     * @param $color
     * @return array ['r'=>'','g'=>'','b'=>'','a'=>'']
     */
    protected function get_Rgba_Info($color){
        $color = trim($color);
        if (preg_match("/^#/", $color)){
            if (strlen($color) === 4) {// #fff 短写格式
                $r = substr($color, 1,1);
                $g = substr($color, 2,1);
                $b = substr($color, 3,1);
                return [
                    "r"=> intval($r.$r, 16),
                    "g"=> intval($g.$g, 16),
                    "b"=> intval($b.$b, 16),
                    "a"=> 1,
                ];
            }

            $a = substr($color, 7,2); #12345678
            return [
                "r"=> intval(substr($color, 1,2), 16),
                "g"=> intval(substr($color, 3,2), 16),
                "b"=> intval(substr($color, 5,2), 16),
                "a"=> $a ? intval($a, 16) / 255 : 1,
            ];
        }

        preg_match("/(?P<r>[\d]+)\s*,\s*(?P<g>[\d]+)\s*,\s*(?P<b>[\d]+)\s*(,\s*(?P<a>.+))?\)/", $color, $match);

        return [
            "r"=> $match['r'],
            "g"=> $match['g'],
            "b"=> $match['b'],
            "a"=> $match['a'] ?: 1,
        ];
    }
}
