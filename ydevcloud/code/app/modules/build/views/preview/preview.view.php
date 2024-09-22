<?php
namespace app\modules\build\views\preview;
use app\build\Build_Model;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\Io_Data_Fetch;
use app\project\Action_Model;
use app\project\Page_Bind_Api_Model;
use app\project\Page_Bind_Data_Model;
use app\project\Page_Bind_Event_Model;
use app\project\Page_Model;
use yangzie\YZE_FatalException;
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
    /**
     * 组件包含的子页的子页对象 subPageId
     * @var Page_Model
     */
    private $subPage;

    /**
     * @var 组件的样式数组，格式[selector=>[styleName=>styleValue]]
     */
    protected $styles = [];
    /**
     * @var array Preview_View
     */
    protected $childViews = [];
    /**
     * 在具体某个事件中使用到到变量名称，格式[变量]
     * @var array
     */
    protected $usedVariables = [];
    /**
     * 元素上的属性数组 [属性名=>属性值1]
     * @var array
     */
    protected $_attrs = [];
    private $_pages;
    private $_outputData;
    private $_outputDataName;
    private $_inputData;
    private $_inputDataName;
    private $_boundData;
    private $_boundDataName;
    private $_iteratorIndexName;
    private $_iteratorDataName;
    private $_events = [];

    public function __construct($data, $controller, Build_Model $build)
    {
        parent::__construct($data, $controller);

        $this->build = $build;
        $this->cssTranslate = $build->get_Css_Translate();
        foreach ((array)@$this->data['items'] as $index => $item){
            $this->childViews[] = $this->create_item_view($index, $item);
        }
    }

    protected function create_item_view($index, $item){
        $build = $this->build->clone();
        $build->set_ui_config($item);
        $build->set_is_subpage(false);
        $subPageId = @$item['subPageId'];
        // 如果该组件的内容是引用一个组件页面; 因为顶层的build已经把该页面所有的uibase拉取出来了
        // 这里只需要init_data重新拉取子页关联的数据即可
        // 组件页面的关联内容在宿主页面是不提现出来的
        if ($subPageId){
            $subPage = $this->get_page($subPageId);
            if ($subPage){
                $build->set_page($subPage);
                $build->set_is_subpage(true);
                $build->set_id_suffix("_".$this->myid()."_{$index}");
                $build->init_data();
            }
        }

        $build->increase_indent(1);
        return self::create_View($build);
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
     * 元素上的css样式字符串, 返回数组，key是样式主题名称，value是对于的样式字符串
     * @var array
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
     *  这里会加上!important
     * @return array
     */
    public function translate_style($meta){
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
                $styles[$name] = $name.': '.trim(join(' ', $value));
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
//        $styles = array_map(function ($item){
//            return $item;
//        }, $styles);
        return $styles;
    }

    /**
     * 调用translate_style获取元素上自定义style字符串, key是style的属性名，值是完整的style属性设置，
     * 比如['color'=>'color:#fff']
     * @return array
     */
    protected function style_map($meta=null, $state='normal') {
        return $this->translate_style($meta ?? $this->data['meta']);
    }
    /**
     * 元素引用的selector包含的style字符串, key是style的属性名，值是完整的style属性设置，
     * 比如['color'=>'color:#fff']
     *
     * @return array
     */
    protected function common_style_map() {
        return $this->translate_style($this->data['meta']['selector']);
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
     * 构建伪类样式
     * @return void
     */
    protected function style_of_pseudo(){
        $pseudoStates = [];
        foreach ($this->build->get_uiid_bind_state($this->myid()) as $state){
            if ($state->state_type != 'pseudo')continue;
            $pseudoStates[] = $state;
        }

        $key = '[data-uiid='.$this->myId().']';
        foreach ($pseudoStates as $pseudoState){
            // 里面包含css，style和selector，custom，同uimeta结构体
            $styles = $this->build->get_uiid_bind_state_styles($this->myId(), $pseudoState->uuid);
            if (!$styles) continue;
//            print_r($styles);
//            $cssInfo = [];
//            伪类暂时不支持预定义css样式
//            $this->fetch_css($styles['css'], $cssInfo);
//            print_r($cssInfo);
            $this->styles[$key.$pseudoState->state_name] = join(' !important;'.PHP_EOL, $this->style_map($styles, $pseudoState->state_name)).' !important;';
        }
    }

    /**
     * 构建自定义状态中使用的css，并根据前端框架构建条件输出，由具体的
     * @return array [css: variable expression]
     */
    protected function css_of_state(){
        $states = [];
        foreach ($this->build->get_uiid_bind_state($this->myid()) as $state){
            if ($state->state_type == 'pseudo' || $state->state_type == 'hidden')continue;
            $states[] = $state;
        }

        $cssVariable = [];
        foreach ($states as $state){
            $state_name = $state->state_type == 'custom' ? $state->state_name : $state->state_type;
            $styles = $this->build->get_uiid_bind_state_styles($this->myId(), $state->uuid);
            if (!$styles) continue;
            $expression = $state->get_expression() ? $state->get_expression()->get_expression_code(true) : null;
            if (!$expression) continue;

            $css = [];
            if ($styles['css']){// 使用的预定义css
                $this->fetch_css($styles['css'], $css);
                if ($css) $css = array_values($css);
            }
            $cssVariable[] = "'{$state_name}".($css ? ' '.join(' ', $css) : '')."': {$expression}";
        }
        return $cssVariable;
    }

    /**
     * 返回配置的hidden的表达式
     * @return array|void
     */
    protected function show_state_expression(){
        $hiddenState = null;

        foreach ($this->build->get_uiid_bind_state($this->myid()) as $state){
            if ($state->state_type == 'hidden'){
                $hiddenState = $state;
                break;
            }
        }
        return $hiddenState && $hiddenState->get_expression() ? $hiddenState->get_expression()->get_expression_code(true) : null;
    }

    /**
     * 构建自定义状态样式，如果state_type=custom表示用户自定义，其他表示系统预定义的
     * @return void
     */
    protected function style_of_state(){
        $states = [];
        foreach ($this->build->get_uiid_bind_state($this->myid()) as $state){
            if ($state->state_type == 'pseudo' || $state->state_type == 'hidden')continue;
            $states[] = $state;
        }

        $key = '[data-uiid='.$this->myId().']';
        foreach ($states as $state){
            $state_name = $state->state_type == 'custom' ? $state->state_name : $state->state_type;
            $styles = $this->build->get_uiid_bind_state_styles($this->myId(), $state->uuid);
            if (!$styles) continue;
//            print_r($styles);
            $this->styles[$key.'.'.$state_name] = join(' !important;'.PHP_EOL, array_values($this->style_map($styles, $state_name))).' !important;';
        }
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
            $this->styles[$key] =  join(' !important;'.PHP_EOL, array_values($styleArray)).' !important;';
        }

        $this->style_of_state();
        $this->style_of_pseudo();

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
    protected function add_style($name, $style){
        $this->styles[$name] = $style;
        return $this;
    }

    /**
     * 构建公共的样式
     * @return void
     */
    public function build_common_style(){
        $styles = [];
        foreach ($this->build->get_styles() as $styleModel){
            $styleValues = $this->translate_style(json_decode(html_entity_decode($styleModel->meta), true));
            $styles[".".$styleModel->class_name] =  join(';'.PHP_EOL, $styleValues).';';
        }
        return $styles;
    }

    /**
     * 返回组件的逻辑代码
     * 默认情况下，如果是容器，则需要输出所包含的组件
     *
     * @param $in_page_id int
     * @return Base_Code_Fragment
     */
    public function build_code(): Base_Code_Fragment{
        $fragment = $this->get_code_fragment();
        if ($this->data['subPageDeleted']){
            return $fragment;
        }
        $this->build_event_code();
        foreach ((array)@$this->childViews as $view){
            $view->build_code();
            $fragment->merge($view->get_code_fragment());
        }
        return $fragment;
    }

    protected function data_default($data){
        switch ($data['type']){
            case 'array':
                // 数组是动态的，非mock时返回控空数组
                if (!$this->build->need_mock() || !$data["mock"]){
                    if ($data["initLength"]){
                        if ($this->is_iteration($data['item'])){
                            $default = $this->data_default($data['item']);
                        }else{
                            $default = $this->get_default_value($data["item"]);
                        }
                        return '['.join(',',array_fill(0, $data["initLength"], $default)).']';
                    }else{
                        return $data['defaultValue']?:'[]';
                    }
                }
                if ($this->is_iteration($data['item'])){
                    $mock = $this->data_default($data['item']);
                }else{
                    $mock = $this->get_default_value($data["item"]);
                }
                return '['.join(',',array_fill(0, rand(3,10), $mock)).']';
            case 'object':{
                if ($data['defaultValue']) return $data['defaultValue'];
                $propDefault = [];
                foreach ($data['props'] as $prop){
                    $default = $this->data_default($prop);
                    if ($default) $propDefault[] = '"'.$prop['name'].'": '.$default;
                }
                if ($propDefault){
                    return "{".join(",", $propDefault)."}";
                }else{
                    return "{}";
                }
            }
            case 'map':{
                if (!$this->build->need_mock() || !$data["mock"]){
                    return $data['defaultValue']?:"{}";
                }else{
                    $propDefault = [];
                    foreach (range(1,10) as $item){
                        $propDefault[] = "item{$item}: \"@string\"";
                    }
                    return "{".join(",", $propDefault)."}";
                }
            }
            default:
                return $this->get_default_value($data);
        }
    }

    private function mock_scale_value($data){
        if ($data['type'] === 'float'){
            return '@float';
        }else if ($data['type'] === 'integer'){
            return '@integer';
        }else if ($data['type'] === 'number'){
            return '@natural';
        }else if ($data['type'] === 'boolean'){
            return '@boolean';
        }else if ($data['type'] === 'string'){
            return '@string';
        }else{
            return '@csentence';
        }
    }
    private function get_default_value($data){
        if ($this->build->need_mock() && $data['mock']){
            return !strcasecmp($data['mock'],'1') ? '"'.$this->mock_scale_value($data).'"' : '"'.$data['mock'].'"';
        }
        if ($data['defaultValue']) {
            if ($data['type']=='string'){
                return '"'.addslashes($data['defaultValue']).'"';
            }else{
                return $data['defaultValue'];
            }
        }
        if ($data['nullable']) return 'null';
        switch ($data['type']){
            case 'array': return '[]';
            case 'object':
            case 'map': return '{}';
            case 'string': return '""';
            case 'number':
            case 'float':
            case 'integer': return 0;
            default: return 'undefined';
        }
    }

    /**
     * 返回UI的默认值（yduibuilder中设置的），不同类型的ui可能默认值不同，有可能是标量有可能是数组
     * @return mixed|string
     */
    protected function default_value() {
        return $this->data['meta']['value']??'';
    }
    /**
     * 每个组件构建并输出组件的ui代码
     * @return mixed
     */
    public abstract function build_ui();

    /**
     * 获取代码片段对象
     * @return Base_Code_Fragment
     */
    public abstract function get_code_fragment(): Base_Code_Fragment;

    /**
     * ydecloud事件在各终端上的对应名称, 如果返回null，则不在html 元素上注册事件
     * @param $eventName
     * @return mixed
     */
    protected abstract function eventName($eventName);

    /**
     * 构建弹窗事件代码
     * @param Action_Model $action
     * @param $actionCodeLines
     * @return mixed
     */
    protected abstract function build_popup_event_code(Action_Model $action, &$actionCodeLines);

    /**
     * 构建内部调用事件代码
     * @param Action_Model $action
     * @param $actionCodeLines
     * @return mixed
     */
    protected abstract function build_call_event_code(Action_Model $action, &$actionCodeLines);

    /**
     * 构建web api调用事件代码
     * @param Action_Model $action
     * @param $codeLines array
     * @return mixed
     */
    protected abstract function build_webapi_code(Action_Model $action, &$codeLines);

    /**
     * 构建重定向事件代码
     * @param Action_Model $action
     * @param $actionCodeLines
     * @return mixed
     */
    protected abstract function build_redirect_code(Action_Model $action, &$actionCodeLines);

    /**
     * 构建定时器事件代码
     * @param Action_Model $action
     * @param $actionCodeLines
     * @return mixed
     */
    protected abstract function build_interval_code(Action_Model $action, &$actionCodeLines);

    /**
     * 构建触发内部事件的代码
     * @param Action_Model $action
     * @param $actionCodeLines
     * @return mixed
     */
    protected abstract function build_emit_code(Action_Model $action, &$actionCodeLines);

    /**
     * 构建数据验证代码
     * @param Action_Model $action
     * @param $actionCodeLines
     * @return mixed
     */
    protected abstract function build_validate_code(Action_Model $action, &$actionCodeLines);

    /**
     * 构建内部数据赋值代码
     * @param Action_Model $action
     * @param $actionCodeLines
     * @return mixed
     */
    protected abstract function build_mutation_code(Action_Model $action, &$actionCodeLines);
    protected abstract function build_closepopup_code(Action_Model $action, &$actionCodeLines);
    protected abstract function build_break_code(Action_Model $action, &$actionCodeLines);
    /**
     * 弹窗模版输出，各终端根据自己的框架进行输出
     */
    public abstract function build_popup_ui(&$outputPopupIds=[]);

    private function build_select_prepare_event_code(&$actionCodeLines){
        if ($this->data['meta']['custom']['multiple']) {
            if (in_array('boundData', $this->usedVariables)) $actionCodeLines[] = "const boundData = []";
            if (in_array('value', $this->usedVariables)) $actionCodeLines[] = "const value = []";

            $actionCodeLines[] = "for(var opt of event.target.selectedOptions) {";
//                $actionCodeLines[] = $this->indent(1, true)."console.log(opt,opt.innerText,opt.dataset?.bound)";
            if (in_array('boundData', $this->usedVariables)){
                $actionCodeLines[] = $this->indent(1, true) . "const boundName = opt.dataset?.bound;";
                $actionCodeLines[] = $this->indent(1, true) . "if(boundName) boundData.push(Alpine.evaluate(eventTarget, boundName));";
            }
            if (in_array('value', $this->usedVariables)) $actionCodeLines[] = $this->indent(1, true) . "value.push(opt.value);";
            $actionCodeLines[] = "}";
        } else {
            $actionCodeLines[] = "const opt = event.target.selectedOptions?.[0]";
            if (in_array('boundData', $this->usedVariables)){
                $actionCodeLines[] = "const boundName = opt?.dataset?.bound;";
                $actionCodeLines[] = "const boundData = boundName ? Alpine.evaluate(eventTarget, boundName) : null;";
            }
            if (in_array('value', $this->usedVariables)) $actionCodeLines[] = "const value = opt ? opt?.value : null;";
        }
    }
    private function build_other_prepare_event_code(&$actionCodeLines){
        $actionCodeLines[] = "const eventTarget = event.target.closest('[data-value]') || event.target.closest('[data-bound]');";
        if (in_array('boundData', $this->usedVariables)) $actionCodeLines[] = "const boundName = eventTarget?.dataset?.bound;";
        // x-for 的数据直接可以通过page.boundName访问
        if (strtolower($this->data['type']) == 'checkbox'){
            $actionCodeLines[] = 'const checked = eventTarget.querySelector("[type=\'checkbox\']")?.checked';
            if (in_array('boundData', $this->usedVariables)){
                $actionCodeLines[] = "const boundData = checked ? Alpine.evaluate(eventTarget, boundName) : undefined;";
            }
            if (in_array('value', $this->usedVariables)) {
                $actionCodeLines[] = 'const value = checked ? eventTarget.dataset?.value : null';
            }
        }else{
            if (in_array('boundData', $this->usedVariables)) $actionCodeLines[] = "const boundData = boundName ? Alpine.evaluate(eventTarget, boundName) : undefined;";
            if (in_array('value', $this->usedVariables)) $actionCodeLines[] = "const value = eventTarget?.dataset?.value;";
        }
    }
    private function build_input_prepare_event_code(&$actionCodeLines){
        $tagName = strtoupper($this->data['type']);
        $tagName = $tagName=='RANGEINPUT'?'INPUT':$tagName;

        if (in_array('value', $this->usedVariables)) {
            $actionCodeLines[] = "const target = event.target.tagName=='{$tagName}' ? event.target : event.target.querySelector('.input') || undefined";
            $actionCodeLines[] = "const value = target?.value";
        }
    }

    /**
     * 构建事件代码中使用的基础数据，比如bind的value，事件参数等
     * @param $eventModel
     * @param $html_event_name
     * @param $eventCodes
     * @param $actionCodeLines
     * @return void
     */
    private function build_prepare_event_code($eventModel, $html_event_name, &$eventCodes, &$actionCodeLines){
        $eventCodes[$html_event_name]['args'] = $this->get_event_arg_names($html_event_name);
        $codes = [];
        // 提取自定义事件的自定义参数
        if ($eventModel->uicomponent_event_id){
            $customEvent = $eventModel->get_uicomponent_event();
            $args = json_decode(html_entity_decode($customEvent->args), true);
            $argNames = [];
            foreach ($args as $arg){
                if (!$this->usedVariables || !in_array($arg['name'], $this->usedVariables)){
                    continue;
                }
                $argNames[] = $arg['name'];
            }
            if ($argNames) $codes[] = "const { ".join(', ', $argNames)." } = event.detail";
            array_unshift($actionCodeLines, ...$codes);
            return;
        }

        if (!$this->usedVariables || !array_intersect(['value','boundData'], $this->usedVariables)) return;

        // 数据值和绑定对数据
        if (strtolower($this->data['type']) == 'select'){
            $this->build_select_prepare_event_code($codes);
        }elseif (in_array(strtolower($this->data['type']), ['input', 'textarea', 'rangeinput'])){
            $this->build_input_prepare_event_code($codes);
        }else{
            $this->build_other_prepare_event_code($codes);
        }
        $codes[] = "";
        array_unshift($actionCodeLines, ...$codes);
    }
    protected function get_event_function_name($html_event_name, $uiconfig=null){
        $myid = $uiconfig ? $uiconfig->meta->id : $this->myid();
        return "{$myid}_{$html_event_name}";
    }
    /**
     * 获取事件的action代码
     * @return array|void [事件名=>['args'=>[], 'code'=>action codes]
     */
    protected function get_event_action_codes(){
        $eventModels = @$this->build->get_events($this->myid());
        return $this->_get_event_action_codes($eventModels);
    }

    /**
     * 获取弹窗的事件
     * @return array|null
     */
    protected function get_popup_event_action_codes(){
        $eventModels = @$this->build->get_popup_events();
        return $this->_get_event_action_codes($eventModels);
    }
    private function _get_event_action_codes($eventModels){
        $eventCodes = [];

        if (!$eventModels) return;

        foreach($eventModels as $eventModel) {
            $actionCodeLines = [];
            $this->usedVariables = [];
            if ($eventModel->uicomponent_event_id){
                $html_event_name = $eventModel->event;
            }else{
                $html_event_name = $this->eventName($eventModel->event) ?: $eventModel->event;
            }
            $html_event_name = strtolower($html_event_name);

            if (!$eventCodes[$html_event_name]) {
                $eventCodes[$html_event_name] = ['code'=>[],'args'=>[],'comment'=>''];
            }
            if ($eventModel->desc){
                $eventCodes[$html_event_name]['comment'] .= PHP_EOL.$eventModel->desc;
            }else if ($eventModel->get_uicomponent_event()) {
                $eventCodes[$html_event_name]['comment'] .= PHP_EOL.$eventModel->get_uicomponent_event()->desc;
            }

            // 先编译事件体代码，并记录使用了哪些基础变量
            foreach ($eventModel->get_actions() as $action) {
                switch ($action->type) {
                    case 'popup': $this->build_popup_event_code($action, $actionCodeLines);break;
                    case 'call': $this->build_call_event_code($action, $actionCodeLines);break;
                    case 'webapi': {
                        $this->build_webapi_code($action, $actionCodeLines);
                        break;
                    }
                    case 'redirect': $this->build_redirect_code($action, $actionCodeLines);break;
                    case 'emit': $this->build_emit_code($action, $actionCodeLines);break;
                    case 'mutation': $this->build_mutation_code($action, $actionCodeLines);break;
                    case 'closepopup': $this->build_closepopup_code($action, $actionCodeLines);break;
                    case 'interval': $this->build_interval_code($action, $actionCodeLines);break;
                    case 'validate': $this->build_validate_code($action, $actionCodeLines);break;
                    case 'break': $this->build_break_code($action, $actionCodeLines);break;
                    default: $actionCodeLines = [];
                }
            }
            if ($actionCodeLines) {
                $this->build_prepare_event_code($eventModel, $html_event_name, $eventCodes, $actionCodeLines);
                $eventCodes[$html_event_name]['code'] = $actionCodeLines;
            }else{
                $eventCodes[$html_event_name]['code'] = ["// NOT DEFINE ACTION;"];
            }
        }
        return $eventCodes;
    }

    /**
     * 是否包含指定的事件
     *
     * @param $eventName
     * @return Page_Bind_Event_Model|mixed|void
     */
    protected function has_event($eventName)
    {
        if (!$this->_events){
            $eventModels = @$this->build->get_events($this->myid());
            if (!$eventModels) return;

            foreach ($eventModels as $eventModel) {
                $html_event_name = strtolower($eventModel->uicomponent_event_id ? $eventModel->event : $this->eventName($eventModel->event));
                $html_event_name = $html_event_name?:strtolower($eventModel->event);
                $this->_events[$html_event_name] = $eventModel;
            }
        }
        return $this->_events[strtolower($eventName)];
    }
    /**
     * 输出组件自己的事件绑定的代码, 并放入codefragment中
     */
    protected function build_event_code() {
        $eventCodes = $this->get_event_action_codes();

        if (!$eventCodes) return;

        foreach ($eventCodes as $html_event_name => $eventInfo){
            list('args'=>$args, 'code'=>$codeBlocks) = $eventInfo;
            if (!$codeBlocks) continue;
            $codeLines = [];
            $codeLines[] = $this->myId(true)."_{$html_event_name}(".join(', ', $args).") {";
            foreach ($codeBlocks as $codes){
                $codeLines = array_merge($codeLines, $this->build->indent_code(1, $codes));
            }
            $codeLines[] = "}";
            $this->get_code_Fragment()->add_code($codeLines);
        }
    }


    protected function output_component(){
        $this->build_ui();
    }

    public function is_custom_ui(){
        return $this->data['type']=='UIComponent';
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

    public function get_page_uuid(){
        $data = $this->build->get_page()->get_ui_config();
        return $data->meta->id;
    }

    public function get_sub_page(){
        if (!$this->subPage){
            $this->subPage = $this->data['subPageId'] ? find_by_uuid(Page_Model::CLASS_NAME, $this->data['subPageId']) : null;
        }
        return $this->subPage;
    }
    /**
     * 从根查找uuid的父级
     */
    protected function find_parent($uiid, &$index=-1, $parent =null) {
        if (!$parent) {
            $parent = json_decode(json_encode($this->build->get_page()->get_config()), true);
        }
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

    public function is_input_ui() {
        return is_a($this, Valuable_View::class);
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
     * - 输出css，id等基本属性和attr内容， attr内容需要在该方法前调用add_attr先设置
     * - 数据输出绑定
     * - 事件绑定
     * - 非迭代类ui的bound和value绑定
     *
     * <strong style="color:red">注意这部分内容只能在ui元素的主体上进行调用输出，具体每个组件那个部分是主体内容，由
     * 组件自己决定。这意味着在一个ui组件及其上层master，该方法只能被调用一次</strong>
     *
     * @param $includeEvent boolean 是否包含事件输出绑定
     * @return void
     */
    protected function build_main_attrs($includeEvent = true, $includeInputBind = true, $includeDataIndex=true) {
        $this->build_css_attrs();
        $myid = $this->myid();
        echo $this->wrap_output('data-uiid', $this->myid());
        echo $this->wrap_output('x-id', "['{$myid}']");
        echo $this->wrap_output('data-type', strtolower($this->data['type']));
        if($includeDataIndex) echo $this->wrap_output(':data-index', $this->get_iterator_index_name());
        foreach ($this->_attrs as $name => $value){
            echo $this->wrap_output($name, $value);
        }
        $this->build_data_output_bind();
        if ($includeInputBind) $this->build_data_input_bind();
        if (!is_a($this, ValueList_View::class)){
            $boundDataNames = [];
            $boundDatas = $this->get_bound_datas($boundDataNames);
            $outputDatas = $this->get_output_datas($outputDataNames);
//            var_dump($outputDatas);
            $hasIterate = $this->need_iterate_data($iterateOutputAs, $dataName, $iterateDataName);
            if($boundDatas){
                foreach ($boundDataNames as $boundType => $boundDataName){
                    if ($boundType === 'BOUND'){
                        echo $this->wrap_output("data-bound", $hasIterate && $boundDataName==$dataName ? "itemOf{$iterateDataName}" : $boundDataName);
                    }elseif ($boundType === 'VALUE'){
                        echo $this->wrap_output(":data-value", $hasIterate && $boundDataName==$dataName  ? "itemOf{$iterateDataName}" : $boundDataName);
                    }
                }
            }
        }
        if($includeEvent) $this->build_event_listen();
    }

    /**
     * 获取绑定的输出数据项，该数据项可能是1级数据或者是数据下面的子数据，$dataName返回访问这个数据的访问路径。
     * 一个ui可以绑定多个输出类型，每个输出类型只能绑定一个数据项。
     * 返回格式：
     *
     * [
     * OUTPUTAS1: DATA1,
     * OUTPUTAS2: DATA2
     * ]
     *
     * 同时通过dataNames返回每个输出格式的数据对应的访问路径 格式[OUTPUTAS1: dataname, OUTPUTAS2: dataname]
     *
     * @param $dataNames
     * @return array
     */
    protected function get_output_datas(&$dataNames=[]){
        if ($this->_outputData){
            $dataNames = $this->_outputDataName;
            return $this->_outputData;
        }
        $fetch = new Io_Data_Fetch($this->build);
        $output = $fetch->fetch_output_data($this->myid(), $dataNames);
        $this->_outputData = $output;
        $this->_outputDataName = $dataNames;
        return $output;
    }
    protected function get_bound_datas(&$dataNames=[]){
        if ($this->_boundData){
            $dataNames = $this->_boundDataName;
            return $this->_boundData;
        }
        $fetch = new Io_Data_Fetch($this->build);
        $output = $fetch->fetch_output_data($this->myid(), $dataNames, 'bound');
        $this->_boundData = $output;
        $this->_boundDataName = $dataNames;
        return $output;
    }
    protected function get_input_data(&$dataName){
        if ($this->_inputData){
            $dataName = $this->_inputDataName;
            return $this->_inputData;
        }
        $fetch = new Io_Data_Fetch($this->build);
        $input = $fetch->fetch_input_data($this->myid(), $dataName);
        $this->_inputData = $input;
        $this->_inputDataName = $dataName;
        return $input;
    }

    /**
     * 构建class输出，其中包含有条件的class和固定的class；构建hidden条件输出
     * @return void
     */
    protected function build_css_attrs(){
        $css = trim($this->get_css());
        $cssVariable = $this->css_of_state();
        $xShownExpression = $this->show_state_expression();
        if ($xShownExpression){
            echo $this->wrap_output('x-show', $xShownExpression);
        }
        if ($cssVariable){
            if ($css) $cssVariable[] = "'{$css}': true";
            echo $this->wrap_output(':class', "{" . join(',', $cssVariable) . "}");
        }else{
            echo $this->wrap_output('class', $css?:NULL);
        }
    }

    /**
     * 前端根据绑定数据类型及输出类型输出绑定
     * @return void
     */
    protected abstract function build_data_output_bind();
    /**
     * 构建前端数据输入绑定
     * @return void
     */
    protected abstract function build_data_input_bind();
    /**
     * 前端事件绑定
     * @return void
     */
    protected abstract function build_event_listen();

    protected function get_event_listen_props(){
        $events = $this->build->get_events($this->myid());
        $eventHandlers = [];
        $myid = $this->myid();
        foreach ($events as $event){
            if(!$event->uicomponent_event_id && $this->isLifeCycleEvent($event->event)) continue;
            $name = strtolower($event->uicomponent_event_id ? $event->event : $this->eventName($event->event));
            if (!$name) continue;
            if (!$this->is_custom_ui() && $name=='change') {
                $inputDataName = $this->get_input_data_name();
                $eventHandlers['x-init'] = "\$watch(alpinejs_get_input_data_name(\$el, '{$inputDataName}'), (value, oldValue) => {$myid}_change(value, oldValue))";
            }else{
                $eventHandlers['@'.$name] = $this->myid().'_'.$name;
            }
        }
        return $eventHandlers;
    }
    /**
     * 对于表单元素，输出表单特有的属性，比如name，disabled，readonly required placeholder等
     *
     * <strong style="color:red">注意这部分内容只能在具体的表单元素的上进行调用输出，比如input，textarea等</strong>
     * @param false $notOutputId 默认输出表单元素id
     */
    protected function build_form_attrs ($includeName = true, $includeUuid=true) {
        $myid = $this->myid();
        if ($includeName) {
            echo $this->wrap_output(':name', "\$id('{$myid}')");
        }
        if($includeUuid) echo $this->wrap_output('data-uiid', $this->myId().$this->data['type']);

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
        echo $this->wrap_output('data-root', $this->myid());
    }

    protected function wrap_icon($outputInner, $indent=null, $wrapTag='div', $iconTag='i') {
        $icon = $this->data['meta']['custom']['icon'];
        if (!$icon) {
            echo PHP_EOL;
            echo $this->indent($indent ?: 1, true);
            $outputInner();
            return;
        }
        echo PHP_EOL;
        echo $this->indent($indent ?: 1, true);
        switch ($this->data['meta']['custom']['icon-position']) {
            case 'top':{
                echo $this->indent($indent ?: 1, true);
                echo "<{$wrapTag}><{$iconTag} class='{$icon}'></{$iconTag}></{$wrapTag}>".PHP_EOL;
                echo $this->indent($indent ?: 1, true);
                $outputInner();
                return;
            }
            case 'bottom':{
                $outputInner();
                echo PHP_EOL;
                echo $this->indent($indent ?: 1, true);
                echo "<{$wrapTag}><{$iconTag} class='{$icon}'></{$iconTag}></{$wrapTag}>";
                return;
            }
            case 'right':{
                $outputInner();
                echo PHP_EOL;
                echo $this->indent($indent ?: 1, true);
                echo "<{$iconTag} class='{$icon}'></{$iconTag}>";
                return;
            }
            default:{
                echo "<{$iconTag} class='{$icon}'></{$iconTag}>".PHP_EOL;
                echo $this->indent($indent ?: 1, true);
                $outputInner();
            }
        }
    }

    protected function wrap_output($attr, $data, $justAttr=false) {
        if (!$attr) return '';
        if ($justAttr) return " {$attr}";
        if (!isset($data)) return '';
        $data = str_replace('"', '\"', $data);
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

    /**
     * 从dataConfig中查找是否存在给定名称的props，找到就返回给prop配置
     * @param $objectDataConfig array 对象数据类型
     * @param $propName
     * @return mixed|null
     */
    protected function has_props($objectDataConfig, $propName){
        if (!$objectDataConfig['props']) return null;
        foreach ($objectDataConfig['props'] as $prop){
            if ($prop['name'] == $propName) return $prop;
        }
        return null;
    }
    protected function is_iteration($dataConfig){
        return in_array($dataConfig['type'], ['array', 'map', 'object']);
    }
    protected function is_object($dataConfig){
        return in_array($dataConfig['type'], ['map', 'object']);
    }
    protected function is_scale($dataConfig){
        return $this->is_scale_type($dataConfig['type']);
    }
    protected function is_scale_type($type){
        return in_array($type, ['string', 'integer', 'number','boolean']);
    }
    protected function is_array($dataConfig){
        return $dataConfig['type'] == 'array';
    }
    protected function is_1d_scale_array($dataConfig){
        return $dataConfig['type']=='array' && $this->is_scale($dataConfig['item']);
    }
    protected function is_1d_any_array($dataConfig){
        return $dataConfig['type']=='array' && $dataConfig['item']['type'] == 'any';
    }
    protected function is_1d_file_array($dataConfig){
        return $dataConfig['type']=='array' && $dataConfig['item']['type'] == 'file';
    }
    protected function is_1d_object_array($dataConfig){
        return $dataConfig['type']=='array' && $this->is_object($dataConfig['item']);
    }
    protected function is_1d_array($dataConfig){
        return $dataConfig['type']=='array' && $dataConfig['item']['type'] != 'array';
    }
    protected function is_2d_array($dataConfig){
        return $dataConfig['type']=='array' && $dataConfig['item']['type']=='array';
    }
    protected function is_2d_scale_array($dataConfig){
        return $this->is_2d_array($dataConfig) && $this->is_scale($dataConfig['item']['item']);
    }
    protected function is_2d_iteration_array($dataConfig){
        return $this->is_2d_array($dataConfig) && $this->is_iteration($dataConfig['item']['item']);
    }

    /**
     * 根据输出的数据及输出类型，返回前端绑定的数据名
     *
     * - 如果是循环输出，output_component中输出了x-for循环绑定语句，根据输出类型判断是否加上itemOf前缀
     * - 如果是对象判断是否需要JSON.stringify
     *
     * 如果不能输出，则返回null
     *
     * @param $outputData
     * @param $dataName
     * @return string
     */
    protected function get_output_data_name($outputAs, $outputData, $outputDataName){
        if ($outputAs === 'NONE') return null;
        $dataName = $outputData['name'];

        if ($this->is_iteration_ui()){
            switch ($outputAs){
                case 'HTML':
                case 'TEXT':
                case 'ALT':return null;
                case 'VALUELIST':
                    if ($this->is_scale($outputData)) return null;
                    if ($this->is_1d_file_array($outputData)) return "itemOf{$dataName}?.name";
                    if ($this->is_object($outputData) || $this->is_1d_array($outputData)) return "itemOf{$dataName}";
                    if ($this->is_2d_array($outputData)) return "itemOf{$dataName}2";
                    return null;
                case 'STYLE':
                    if ($this->is_scale($outputData) || $this->is_object($outputData) || $this->is_1d_scale_array($outputData)) return $outputDataName;
                    return null;
                case 'CSS':
                    if ($this->is_scale($outputData) || $this->is_1d_scale_array($outputData)) return $outputDataName;
                    return null;
                case 'TITLE':
                    if ($this->is_scale($outputData)) return $outputDataName;
                    return "JSON.stringify({$outputDataName})";
                case 'KEYVALUE':
                    if ($this->is_object($outputData)) return $outputDataName;
                    return null;
            }
        }else{
            switch ($outputAs){
                case 'HTML':
                case 'TEXT':
                    if ($this->is_scale($outputData)) return $outputDataName;
                    if ($outputData['type'] == 'any' || $this->is_object($outputData)) return "JSON.stringify({$outputDataName})";
                    if ($outputData['type'] == 'file') return "{$outputDataName}?.name";
                    if ($this->is_1d_scale_array($outputData)) return "itemOf{$dataName}";
                    if ($this->is_1d_any_array($outputData)) return "JSON.stringify(itemOf{$dataName})";
                    if ($this->is_1d_file_array($outputData)) return "itemOf{$dataName}?.name";
                    if ($this->is_1d_object_array($outputData) || $this->is_2d_array($outputData)) return "JSON.stringify(itemOf{$dataName})";
                    return $outputDataName;
                case 'VALUELIST': return null;
                case 'VALUE':
                    if ($this->is_scale($outputData)) return $outputDataName;
                    if ($this->is_object($outputData) || $this->is_1d_array($outputData)) return "itemOf{$dataName}";
                    if ($this->is_2d_array($outputData)) return "itemOf{$dataName}2";
                    return null;
                case 'STYLE':
                    if ($this->is_scale($outputData) || $this->is_object($outputData) || $this->is_1d_scale_array($outputData)) return $outputDataName;
                    if ($this->is_1d_object_array($outputData) || $this->is_2d_scale_array($outputData)) return "itemOf{$dataName}";
                    return null;
                case 'CSS':
                    if ($this->is_scale($outputData) || $this->is_1d_scale_array($outputData)) return $outputDataName;
                    if ($this->is_2d_scale_array($outputData)) return "itemOf{$dataName}";
                    return null;
                case 'KEYVALUE':
                    if ($this->is_object($outputData)) return $outputDataName;
                    if ($this->is_1d_object_array($outputData)) return "itemOf{$dataName}";
                    return null;
                case 'ALT':
                case 'TITLE':
                    if ($this->is_scale($outputData)) return $outputDataName;
                    return "JSON.stringify({$outputDataName})";
            }
        }
        return NULL;
    }

    /**
     * 迭代类ui指内部有循环输出元素的ui，包含：
     * - breadcrumb
     * - carousel
     * - checkbox
     * - collapse
     * - dropdown
     * - list
     * - nav
     * - pagination
     * - radio
     * - select
     * - table
     *
     * @return bool
     */
    protected function is_iteration_ui(){
        return in_array(strtolower($this->data['type']),['breadcrumb','carousel','checkbox','collapse','dropdown','list','nav','radio','select', 'table']);
    }

    /**
     * 判断当前绑定的输出数据是否需要循环当前ui，并通过参数返回需要循环输出的输出类型及该数据的name
     * @param $iterateOutputAs string 循环输出类型
     * @param $dataName string 循环输出的数据名
     * @param $iterateDataName string 在循环时用的item name，比如 for($iterateDataName in $dataName)
     * @return bool
     */
    protected function need_iterate_data(&$iterateOutputAs=null, &$dataName=null, &$iterateDataName=null){
        $outputDatas = $this->get_output_datas($dataNames);
        foreach ($outputDatas as $outputAS => $outputData){
            if ($this->need_iterate_ui($outputAS, $outputData)){
                $iterateOutputAs = $outputAS;
                $dataName = $dataNames[$outputAS];
                $iterateDataName = $outputData['name'] ?: $dataName;
                return true;
            }
        }
        return false;
    }
    /**
     * 判断绑定dataconfig输出时，是否需要循环输出UI，
     * 以下情况需要循环输出：
     * - 迭代类ui
     *      - 除表格外：二维数组并绑定valuelist： 这时一维循环输出ui，二维循环输出内部list,表格只能绑定二维数组，并且不会导致ui迭代
     * - 非迭代类ui
     *      - 一维标量数组并绑定html：循环输出ui并绑定x-html
     *      - 一维标量数组并绑定text：循环输出ui并绑定x-text
     *      - 一维标量数组并绑定value：循环输出ui并绑定value
     *      - 一维对象数组并绑定text：1维循环输出ui，二维json后输出x-text
     *      - 一维对象数组并绑定html：1维循环输出ui，二维json后输出x-html
     *      - 一维对象数组并绑定keyvalue：循环输出ui，并绑定x-keyvalue
     *      - 一维对象数组并绑定style：循环输出ui，并绑定x-style
     *      - 二维数组并绑定html：1维循环输出ui，二维json后输出x-html
     *      - 二维数组并绑定text：1维循环输出ui，二维json后输出x-text
     *      - 二维标量数组并绑定style：一维循环输出ui，二维绑定x-style
     *      - 二维标量数组并绑定css：一维循环输出ui，二维绑定x-css
     *
     * @param $outputData
     * @return false
     */
    public function need_iterate_ui($outputas, $outputData){
        if (!$outputData) return false;
        $outputas = strtoupper($outputas);
        if ($this->is_iteration_ui()){
            if(strtolower($this->data['type']) == 'table') return false;
            return $this->is_2d_array($outputData) && $outputas == 'VALUELIST';
        }else{
            if ($this->is_1d_any_array($outputData) && in_array($outputas, ['TEXT', 'HTML', 'NONE'])) return true;
            if ($this->is_1d_file_array($outputData) && in_array($outputas, ['TEXT', 'HTML', 'NONE'])) return true;
            if ($this->is_1d_scale_array($outputData) && in_array($outputas, ['TEXT', 'HTML', 'VALUE', 'NONE'])) return true;
            if ($this->is_1d_object_array($outputData) && in_array($outputas, ['TEXT', 'HTML', 'KEYVALUE', 'STYLE', 'NONE'])) return true;
            if ($this->is_2d_array($outputData) && in_array($outputas, ['TEXT', 'HTML', 'NONE'])) return true;
            if ($this->is_2d_scale_array($outputData) && in_array($outputas, ['STYLE', 'CSS', 'NONE'])) return true;
        }
        return false;
    }

    /**
     * 返回输出格式在前端的绑定指令， 如果返回假值则表示不输出
     * @param $outputAs
     * @return string
     */
    protected function output_as_prop($outputAs, $outputData){
        if ($outputAs=='HTML'){
            return 'x-html';
        }else if ($outputAs=='NONE'||$outputAs=='VALUELIST'){
            return null;
        }else if($outputAs=='STYLE'){
            if ($this->is_scale($outputData)) return ":style";
            if ($this->is_2d_scale_array($outputData)) return 'x-style';
            if ($this->is_iteration($outputData)) return "x-style";
            return null;
        }else if($outputAs=='CSS'){
            if($this->is_scale($outputData)) return ':class';
            if($this->is_1d_scale_array($outputData) || $this->is_2d_scale_array($outputData)) return 'x-class';
            return null;
        }else if($outputAs=='TITLE'){
            return ':title';
        }else if($outputAs=='ALT'){
            return ':alt';
        }else if($outputAs=='KEYVALUE'){
            if($this->is_scale($outputData)) return null;
            if($this->is_1d_scale_array($outputData)) return null;
            if($this->is_2d_array($outputData)) return null;
            return 'x-keyvalue';
        }else{
            return 'x-text';
        }
    }

    /**
     * 返回在遍历时用到的name，value, checked, data等对应等获取名
     * - 对象数组，如果对象有name属性用之，没有JSON.stringify(数组项)
     * - 对象数组，如果对象有value属性用之，没有返回数组索引
     * - 对象，name和value都采用key:value都格式
     *
     * @param $bindOutput
     * @param $itemName
     * @return string[] [name, value, checked, data]
     */
    protected function get_bind_name_value($bindOutput, $itemName)
    {
        if (!$bindOutput || !$itemName) return ['name'=>null, 'value'=>null, 'checked'=>null, 'data'=>null];

        $name = "itemOf{$itemName}";
        $value = "itemOf{$itemName}";
        $checked = null;
        $data = "itemOf{$itemName}";

        if ($bindOutput['type']=='array'){
            if ($this->has_props($bindOutput['item'],'name')){//对象数组
                $name = "itemOf{$itemName}.name";
            }else if ($bindOutput['item']['type'] == 'file'){
                $name = "itemOf{$itemName}?.name";
            }else if ($this->is_object($bindOutput['item'])){
                $name = "JSON.stringify(itemOf{$itemName})";
            }

            if ($this->has_props($bindOutput['item'],'value')){
                $value = "itemOf{$itemName}.value";
            }else if ($bindOutput['item']['type'] == 'file'){
                $value = "itemOf{$itemName}?.name";
            }else if ($this->is_object($bindOutput['item'])){
                $value = "idxOf{$itemName}";
            }

            if ($this->has_props($bindOutput['item'],'checked')){
                $checked = "itemOf{$itemName}.checked";
            }
        }else if ($this->is_object($bindOutput)){
            $name = "idxOf{$itemName}";
            $value = "itemOf{$itemName}";
            $data = $itemName;
        }else if ($this->is_scale($bindOutput)){
            $name = $itemName;
            $value = $itemName;
            $data = $itemName;
        }else if ($bindOutput['type'] == 'file'){
            $name = "{$itemName}?.name";
            $value = "{$itemName}?.name";
            $data = $itemName;
        }
        return ['name'=>$name, 'value'=>$value, 'checked'=>$checked, 'data'=>$data];
    }

    protected function set_iterator_data_name($dataName){
        $this->_iteratorDataName = $dataName;
        return $this;
    }

    /**
     * 如果当前ui被迭代输出，该方法返回当前ui被迭代时的数据名，前端可以通过该数据名称获得动态的值
     * @return mixed
     */
    protected function get_iterator_data_name(){
        return $this->_iteratorDataName;
    }
    /**
     * @return void
     */
    protected function set_iterator_index_name($indexName){
        $this->_iteratorIndexName = $indexName;
        return $this;
    }

    /**
     * 如果当前ui被迭代输出，该方法返回当前ui被迭代时的索引数据名，前端可以通过该数据名称获得动态的索引值
     * @return mixed
     */
    protected function get_iterator_index_name(){
        return $this->_iteratorIndexName;
    }

    /**
     * 在具体的某个事件中使用的变量
     *
     * @param $argName
     * @return void
     */
    protected function add_used_variable($argName){
        preg_match_all('/\b[a-zA-Z]+\b/', $argName, $matches);
        foreach($matches[0] as $arg){
            $this->usedVariables[] = $arg;
        }
    }
    protected function get_event_arg_names($event_name) {
        $args = [
            'onchange' => [
                'args' => [
                    ["type" => 'string', "name" => 'value', "uuid" => 'value'],
                    ["type" => 'string', "name" => 'oldValue', "uuid" => 'oldValue'],
                    ["type" => 'any', "name" => 'boundData', "uuid" => 'boundData']
                ]
            ],
            'oninput' => [
                'args' => [
                    ["type" => 'string', "name" => 'value', "uuid" => 'value'],
                    ["type" => 'any', "name" => 'boundData', 'uuid' => 'boundData']
                ]
            ],
            'onkeyup' => [
                'args' => [
                    ["type" => 'string', "name" => 'keyCode', "uuid" => 'keyCode'],
                    ["type" => 'string', "name" => 'value', "uuid" => 'value'],
                    ["type" => 'any', "name" => 'boundData', "uuid" => 'boundData']
                ]
            ],
            'onkeydown' => [
                'args' => [
                    ["type" => 'string', "name" => 'keyCode', "uuid" => 'keyCode'],
                    ["type" => 'string', "name" => 'value', "uuid" => 'value'],
                    ["type" => 'any', "name" => 'boundData', "uuid" => 'boundData']
                ]
            ],
            'onkeypress' => [
                'args' => [
                    ["type" => 'string', "name" => 'keyCode', "uuid" => 'keyCode'],
                    ["type" => 'string', "name" => 'value', "uuid" => 'value'],
                    ["type" => 'any', "name" => 'boundData', "uuid" => 'boundData']
                ]
            ],
            'onclick' => [
                "args" => [
                    ["type" => 'string', "name" => 'value', "uuid" => 'value'],
                    ["type" => 'any', "name" => 'boundData', "uuid" => 'boundData']
                ]
            ],
            'ondblclick' => [
                "args" => [
                    ["type" => 'string', "name" => 'value', "uuid" => 'value'],
                    ["type" => 'any', "name" => 'boundData', "uuid" => 'boundData']
                ]
            ],
            'onmousedown' => [
                "args" => [
                    ["type" => 'string', "name" => 'value', "uuid" => 'value'],
                    ["type" => 'any', "name" => 'boundData', "uuid" => 'boundData']
                ]
            ],
            'onmouseup' => [
                "args" => [
                    ["type" => 'string', "name" => 'value', "uuid" => 'value'],
                    ["type" => 'any', "name" => 'boundData', "uuid" => 'boundData']
                ]
            ],
            'onmouseover' => [
                "args" => [
                    ["type" => 'string', "name" => 'value', "uuid" => 'value'],
                    ["type" => 'any', "name" => 'boundData', "uuid" => 'boundData']
                ]
            ],
            'onmouseout' => [
                "args" => [
                    ["type" => 'string', "name" => 'value', "uuid" => 'value'],
                    ["type" => 'any', "name" => 'boundData', "uuid" => 'boundData']
                ]
            ],
            'onmousemove' => [
                "args" => [
                    ["type" => 'string', "name" => 'value', "uuid" => 'value'],
                    ["type" => 'any', "name" => 'boundData', "uuid" => 'boundData']
                ]
            ],
            'onmouseenter' => [
                "args" => [
                    ["type" => 'string', "name" => 'value', "uuid" => 'value'],
                    ["type" => 'any', "name" => 'boundData', "uuid" => 'boundData']
                ]
            ],
            'onmouseleave' => [
                "args" => [
                    ["type" => 'string', "name" => 'value', "uuid" => 'value'],
                    ["type" => 'any', "name" => 'boundData', "uuid" => 'boundData']
                ]
            ],
            'onfilechange' => [
                "args" => [
                    ["type" => 'array', "name" => 'files', "uuid" => 'files', 'item' => ["type" => 'file']]

                ]],
            'onuploadprogress' => [
                "args" => [
                    ["type" => 'number', "name" => 'index', "uuid" => 'index'],
                    ["type" => 'file', "name" => 'file', "uuid" => 'file'],
                    ["type" => 'number', "name" => 'progress', "uuid" => 'progress']
                ]
            ],
            'onbeforeupload' => [
                "args" => [
                    ["type" => 'number', "name" => 'index', "uuid" => 'index'],
                    ["type" => 'file', "name" => 'file', "uuid" => 'file']
                ]
            ],
            'onfileuploaded' => [
                "args" => [
                    ["type" => 'number', "name" => 'index', "uuid" => 'index'],
                    ["type" => 'file', "name" => 'file', "uuid" => 'file'],
                    ["type" => 'any', "name" => 'rst', "uuid" => 'rst']
                ]
            ]
        ];
        $_ = ['event'];
        foreach ($args[strtolower($event_name)]['args'] ?: [] as $item){
            $_[] = $item['name'];
        };
        return $_;
    }
}
