<?php
namespace app\modules\build\views\code\web;
use app\build\Build_Model;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\ValueList_View;
use app\modules\build\views\preview\Vue_Build_Code;
use app\project\Action_Model;
use app\project\Page_Bind_Api_Model;
use app\project\Page_Bind_Data_Model;

/**
 * 由于bootstrap前端生成的代码和在线预览一样，所以web前端不同框架代码生成Class都继承之Preview
 * 这里通过trait都方式统一重载相关的实现，vue3的组件使用
 * @package app\api\views
 */
trait Vue {
    use Vue_Code_Helper,Vue_Build_Code;
    protected function get_Img_Src($imgSrc){
        return '@/'.rtrim($this->build->get_img_Asset_Path(), '/').'/'.basename(urldecode($imgSrc), PATHINFO_BASENAME);
    }

    /**
     * 输出成vue格式的json字符串，单引号，如果是合法的单词，则不需要引号，比如
     * { name: 'value', 'name-1': 'value2' }
     * @param $array
     */
    public function formatVue3JSON($array){
        $string = [];
        $maybeIsObject = false;
        foreach ($array as $name => $value) {
            if (is_numeric($name)){
                $key = '';
            }else{
                $maybeIsObject = true;
                $key = (preg_match("/-/", $name) ? escapeshellarg($name) : $name).':';
            }
            if (is_array($value)) {
                $string[] = $key.$this->formatVue3JSON($value);
            }else{
                $string[] = $key.escapeshellarg($value);
            }
        }
        return $maybeIsObject ? "{".join(',', $string)."}" : "[".join(',', $string)."]";
    }

    public static function get_View_Class(array $uiconfig, Build_Model $build){
        $ui = $build->get_ui();
        $frontend = $build->get_frontend();
        $frontendFramework = $build->get_front_Framework();
        $type = strtolower($uiconfig['type']);
        return "app\\modules\\build\\views\\code\\{$frontend}\\{$ui}_{$frontendFramework}\\{$type}_View";
    }
    /**
     * 当前UI组件是否会迭代输出
     * @return bool
     */
    public function has_iterate() {
        return $this->need_iterate_data($iterateOutputAs, $outputDataName, $iterateDataName);
    }
    protected function get_page_url($page) {
        return $page->id == $this->build->get_project()->home_page_id ? '/' : ($page->url?:"/page{$page->id}");
    }


    protected function output_data_output_bind(){
        $outputDatas = $this->get_output_datas($dataName);
        if (!$outputDatas) return;

        foreach ($outputDatas as $outputAS => $outputData){
            $outputDataName = $this->get_output_data_name($outputAS, $outputData, $dataName[$outputAS]);
            echo $this->wrap_output($this->output_as_prop($outputAS, $outputData), $outputDataName);
        }
    }

    protected function get_event_listen_props(){
        $myid = $this->myid();
        $events = $this->build->get_events($myid);
        $eventHandlers = [];
        foreach ($events as $event){
            if($this->isLifeCycleEvent($event->event)) continue;
            $name = $event->uicomponent_event_id ? $event->event : $this->eventName($event->event);
            if (!$name) continue;
            $modifiers = $event->modifier ? explode(',', $event->modifier) : [];
            if(in_array('throttle', $modifiers) || in_array('debounce', $modifiers) && $event->timeout){
                $modifiers[] = $event->timeout."ms";
            }
            if($event->custom_key){
                $modifiers[] = trim(strtolower($event->custom_key));
            }
            if($event->immediate && in_array('debounce', $modifiers)){
                $modifiers[] = 'immediate';
            }
            $eventHandlers['v-event:y-'.$name.($modifiers ? '.'.join('.', $modifiers) : '')] = $myid . ucfirst($name);
        }
        return $eventHandlers;
    }


    public function build_popup_ui(&$outputPopupIds=[]){
        // 把弹窗作为telport的组件输出
        $events = $this->build->get_custom_events();

        foreach ($events as $event){
            if(!$event->uicomponent_event_id) continue;
            $popupPage = $event->get_uicomponent_event()->get_page();
            $name = $event->event;
            if (!$name) continue;
            $popup_events[$popupPage->uuid]["@{$name}"] = $this->get_event_function_name($name);
        }
        $popupPageIds = [];
        $this->build->get_page()->fetchPopupPageIds($popupPageIds);
        foreach ($popupPageIds as $popupPageId){
            $outputPopupIds[] = $popupPageId;
            $page = $this->get_page($popupPageId);
            $page_config = json_decode(html_entity_decode($page->config), true);

            $esc = boolval(@$page_config['meta']['custom']['esc']);
            echo $this->indent(1).'<'.$popupPageId.' v-if="'.$popupPageId.'Visible"';
            if ($page->page_type=='popup'){
                echo $this->wrap_output('@close', "{$popupPageId}Visible=false");
                echo $this->wrap_output(':esc', ($esc ? 'true' : 'false'));
                $backdrop = $page_config['meta']['custom']['backdrop'];
                if ($backdrop=='static'){
                    echo $this->wrap_output('backdrop',  'static');
                }elseif ($backdrop=='no'){
                    echo $this->wrap_output(':backdrop',  'false');
                }else{
                    echo $this->wrap_output(':backdrop',  'true');
                }
            }
            echo $this->wrap_output(':param', $popupPageId.'Param');
            foreach ($popup_events[$popupPageId] as $name => $handler){
                echo $this->wrap_output($name, $handler);
            }
            echo '></'.$popupPageId.'>'.PHP_EOL;
        }
    }

    protected function lifeCycleEvent(){
        return [
            'onload',
            'onunload',
        ];
    }

    protected function get_event_function_name($html_event_name, $uiconfig=null){
        $myid = $uiconfig ? $uiconfig->meta->id : $this->myid();
        return "{$myid}".ucfirst($html_event_name);
    }

    public function build_code(): Base_Code_Fragment{
        $fragment = $this->get_code_fragment();
        if ($this->data['subPageDeleted']){
            return $fragment;
        }
        // 幻灯片，组件
        $isTopPage = !$this->find_parent($this->myid());
        // 顶级元素构建vue代码结构主体
        if ($isTopPage){
            $this->init_page_scope_variable();
            $this->build_page_data_code(1);
            $this->build_custom_event_code();

            // 弹窗页面，在vue中作为组件引入
            if ($this->get_build()->get_page()->page_type == 'popup'){
                $fragment->add_import('vue', ['defineProps','defineEmits','onMounted', 'nextTick']);
                $fragment->add_import('bootstrap', [], '* as bootstrap');
                $fragment->add_emit('close');

                $fragment->add_prop('esc', 'Boolean');
                $fragment->add_prop('backdrop', ["Boolean","String"]);
                $fragment->add_let('myModal', "null", 'any');
                $myid = $this->myid();
                $onMounted = <<< MOUNTED
myModal = new bootstrap.Modal('#{$myid}', {
  keyboard: esc,
  backdrop: backdrop,
  focus: true
})
nextTick(() => {
    if(myModal) myModal.show()
})
const myModalEl = document.getElementById('{$myid}')
myModalEl?.addEventListener('hidden.bs.modal', event => {
  myModal.dispose()
  emit('close')
})
MOUNTED;
                $close = <<< CLOSE
function close(){
    if(myModal) {
        myModal.hide()
        emit('close')
    }
}
CLOSE;

                $fragment->add_lifecycle('onMounted', $onMounted);
                $fragment->add_code(Vue_Code_Fragment::SECTION_FUNCTION, $close);
            }
        }

        $this->build_event_code();
        foreach ((array)@$this->childViews as $view){
            $view->build_code();
            $fragment->merge($view->get_code_fragment());
        }
        $this->build_initialize_code();

        return $fragment;
    }
    protected function output_form_attrs ($includeName = true, $includeUuid=true) {
        $myid = $this->myid();
        if ($includeName) {
            echo $this->wrap_output('name', $myid);
        }

        $this->output_base_form_attrs($includeUuid);
    }
    /**
     * 对表单组件或者值列表组件构建输入数据对初始化代码
     *
     * - 如果是表单ui并且没有绑定输入数据，则生成一个ID_value的数据进行绑定
     * - 如果是值列表ui并且没有绑定输入数据，则生成一个ID_value的数据进行绑定；
     * - 如果是值列表ui，则生成一个ID_checkedName的数据，表示当前选择值的名称；同时生成watch代码，观察绑定的输入数据以便自动更新checkName
     *
     * @return void
     */
    public function build_initialize_code() {
        if (!$this->is_input_ui()) return;

        $fragment = $this->get_code_Fragment();
        $this->get_input_data($inputDataName);
        $this->get_output_datas($outputDataNames);
        // 如果没有数据绑定的话，定义一个临时数据
        if (!$inputDataName) {
            $fragment->add_ref($this->myid() . '_value', $this->default_value());
        }
    }
    protected function default_value() {
        $value = parent::default_value();
        if (!$value) return '';
        if(is_array($value)) return $value;
        return "'".$value."'";
    }


    protected function need_iterate_data(&$iterateOutputAs=null, &$dataName=null, &$iterateDataName=null){
        return Preview_View::need_iterate_data($iterateOutputAs, $dataName, $iterateDataName);
    }

    protected function get_output_data_name($outputAs, $outputData, $outputDataName){
        return Preview_View::get_output_data_name($outputAs, $outputData, $outputDataName);
    }
    protected function build_custom_event_code() {
        $eventCodes = array_merge($this->get_custom_event_action_codes(), $this->get_lifecycle_event_action_codes());
        if (!$eventCodes) return;

        $codeLines = [];
        $mounted = [];
        foreach ($eventCodes as $html_event_name => $eventInfo){
            list('args'=>$args, 'code'=>$codeBlocks, 'comment'=>$comment) = $eventInfo;
            if (!$codeBlocks) continue;
            $lines = [''];
            if ($comment){
                $comments = explode(PHP_EOL, $comment);
                $lines[] = "/**";
                $lines[] = " * ".join(PHP_EOL." * ", $comments);
                $lines[] = " */";
            }
            $funcArgs = [];
            foreach ($args as $name => $config){
                $funcArgs[] = $name.":".$config['type'];
            }
            $funcName = "function ".$this->get_event_function_name($html_event_name)."(".join(',', $funcArgs).") {";
            $lines[] = $funcName;
            foreach ($codeBlocks as $codes){
                $lines = array_merge($lines, $this->build->indent_code(1, $codes));
            }
            $lines[] = "}";
            $codeLines[] = join(PHP_EOL, $lines);

            if ($this->isLifeCycleEvent($html_event_name)){
                $mounted[] = $this->get_event_function_name($html_event_name)."()";
            }
        }
        if($mounted) {
            $this->get_code_fragment()->add_import('vue' ,['onMounted']);
            $this->get_code_Fragment()->add_lifecycle('onMounted', join(PHP_EOL, $mounted) . PHP_EOL);
        }
        if($codeLines) $this->get_code_Fragment()->add_code(Vue_Code_Fragment::SECTION_FUNCTION, join(PHP_EOL,$codeLines).PHP_EOL);
    }

    protected function build_event_code() {
        $eventCodes = $this->get_event_action_codes();
        if (!$eventCodes) return;

        $codeLines = [];

        foreach ($eventCodes as $html_event_name => $eventInfo){
            list('args'=>$args, 'code'=>$codeBlocks, 'comment'=>$comment) = $eventInfo;
            if (!$codeBlocks) continue;
            $lines = [''];
            if ($comment){
                $comments = explode(PHP_EOL, $comment);
                $lines[] = "/**";
                $lines[] = " * ".join(PHP_EOL." * ", $comments);
                $lines[] = " */";
            }
            $funcArgs = [];
            foreach ($args as $name => $config){
                $funcArgs[] = $name.":".$config['type'];
            }
            $funcName = "function ".$this->get_event_function_name($html_event_name)."(" .join(", ", $funcArgs).") {";
            $lines[] = $funcName;
            foreach ($codeBlocks as $codes){
                $lines = array_merge($lines, $this->build->indent_code(1, $codes));
            }
            $lines[] = "}";
            $codeLines[] = join(PHP_EOL, $lines);
        }

        if($codeLines) $this->get_code_Fragment()->add_code(Vue_Code_Fragment::SECTION_FUNCTION, join(PHP_EOL, $codeLines));
    }
    protected function isLifeCycleEvent($eventName){
        return in_array(strtolower($eventName), $this->lifeCycleEvent()) || in_array(strtolower('on'.$eventName), $this->lifeCycleEvent());
    }
    protected function build_api_output(Page_Bind_Api_Model $bind_api, Action_Model $action, &$codeLines){
        if ($action->type != 'output') return;
        $bindVariables = $this->build->get_bind_variables('from', Page_Bind_Api_Model::CLASS_NAME, $bind_api->uuid);
        foreach ($bindVariables as $bindVariable){
            $expression = $bindVariable->get_expression();
            if (!$expression) continue;
            $expression_code = $expression->get_expression_code(false);
            $codeLines[] = $this->append_vue_value($bindVariable->to_data_path) . ' = ' . $expression_code;
        }
    }

    /**
     * 把代码中的数据加上value访问符：
     *
     * 把xxx的调整为xxx.value
     *
     * 把page.xxx的调整为xxx.value
     *
     * 把page.xxx.yyy 调整为 xxx.value.yyy
     *
     * 把xxx.yyy 调整为 xxx.value.yyy
     *
     *
     * 如果代码中有xxx()调用，而xxx是表达式数据，则移除(), 因为在vue中表达式是computed
     *
     * @param $code string
     * @return string
     */
    private function append_vue_value($code){
        $code =  trim($code);
        $dataNames = self::pick_data($code);
        if (!$dataNames) return $code;
        $fix = 0;// 替换后导致原来位置有偏移，改变量记录应该偏移多少

        foreach ($dataNames as $item){
            list($dataName, $position) = $item;
            if (!$this->is_page_scope_variable($dataName)) continue;

            preg_match("/^(?P<v>(page\.)?\w+(?<!rst))(\.|$)/", $dataName,$matches); //rst 为api返回的数据

            if (!$matches['v']) { // xxx的情况
                continue;
            }
            $v = $matches['v'];
            $name = preg_replace("/page\./", '', $v).".value";
            $newDataName = preg_replace("/^{$v}/", $name, $dataName);
//            var_dump("-",$dataName,$name,$newDataName);
//            echo $code.' at '.($position + $fix).'('.$position.', fix '.$fix.') to '.$newDataName.PHP_EOL;
            $currPosition = $position + $fix;
            $code = substr_replace($code, $newDataName, $currPosition, strlen($dataName));
            $fix += strlen($newDataName) - strlen($dataName);

            // 删除自定义代码中表达式数据的()
            $hasBracket = preg_match("/(?P<w>{$newDataName}\s*\(\s*\))/", $code, $bracketMatches);
            if ($hasBracket){ // 删除表达式数据的括号
                $word = $bracketMatches['w'];
                $code = substr_replace($code, $newDataName, $currPosition, strlen($word));
                $fix += strlen($newDataName) - strlen($word);
            }
        }

        return $code;
    }

    protected function output_data_input_bind(){
        // 表单组件绑定x-model
        if (!$this->is_input_ui()) return;
        $inputDataName = $this->get_input_data_name($inputIsArr);
        if ($inputDataName){
            echo $this->wrap_output('v-model.trim', $inputDataName);
        }
    }

    protected function output_component(){
        $iterateDataName = null;
        $dataName = null;
        $hasIteral = $this->need_iterate_data($outputAS, $dataName, $iterateDataName);
        $indent = $this->build->get_indent();
        if ($hasIteral){//  迭代输出数据
            echo $this->indent();
            echo '<template v-for="(itemOf'.$iterateDataName.', idxOf'.$iterateDataName.') in '.$dataName.'" :key="idxOf'.$iterateDataName.'">'.PHP_EOL;
            $this->set_iterator_index_name("idxOf{$iterateDataName}");
            $this->set_iterator_data_name("itemOf{$iterateDataName}");
        }

        $this->init_page_scope_variable();
        $this->build_ui();

        if ($hasIteral){
            echo $this->indent($indent, true);
            echo '</template>'.PHP_EOL;
        }
    }
    protected function build_valuelist_iterator($outputData, $outDataName, $iteratorName, $itemName, $is2D=false, $firstIndex=''){
        $this->build_ui_begin($iteratorName);

        echo $this->indent(1).'<template v-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '
            .$iteratorName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;
        $this->set_iterator_index_name("idxOf{$itemName}");
        $this->set_iterator_data_name("itemOf{$itemName}");

        $this->build_valuelist($is2D ? $outputData['item'] : $outputData, $itemName);
        echo $this->indent(1).'</template>'.PHP_EOL;

        $this->build_ui_end();
    }

    /**
     * 返回输出格式在前端的绑定指令， 如果返回假值则表示不输出
     * @param $outputAs
     * @return string
     */
    protected function output_as_prop($outputAs, $outputData){
        if ($outputAs=='HTML'){
            return 'v-html';
        }else if ($outputAs=='NONE'||$outputAs=='VALUELIST'){
            return null;
        }else if($outputAs=='STYLE'){
            if ($this->is_scale($outputData)) return ":style";
            if ($this->is_2d_scale_array($outputData)) return ':style';
            if ($this->is_iteration($outputData)) return ":style";
            return null;
        }else if($outputAs=='CSS'){
            if($this->is_scale($outputData)) return ':class';
            if($this->is_1d_scale_array($outputData) || $this->is_2d_scale_array($outputData)) return ':class';
            return null;
        }else if($outputAs=='TITLE'){
            return ':title';
        }else if($outputAs=='ALT'){
            return ':alt';
        }else if($outputAs=='KEYVALUE'){
            if($this->is_scale($outputData)) return null;
            if($this->is_1d_scale_array($outputData)) return null;
            if($this->is_2d_array($outputData)) return null;
            return 'v-attr';
        }else{
            return null;
        }
    }

    /**
     * UI绑定的输入数据的访问名称，如果没有定义输入数据，则返回默认的ID_value
     *
     * 以下情况表示绑定的数据是数组，返回的输入数据名称会加上[-1], 其中-1需要前端通过循环输出的UI上面的data-index来动态构建访问路径
     *
     * 1. 明确绑定了数组数据
     * 2. 未明确绑定数据时：UI被迭代了或则是checkbox，或者是multiple的select
     *
     * 前端可通过alpinejs_get_value,alpinejs_set_value来操作
     *
     * @param $inputIsArr boolean 当前绑定时输入数据是否是数组格式
     * @return mixed|string
     */
    public function get_input_data_name(&$inputIsArr=false, &$inputDataConfig = null) {
        $inputData = $this->get_input_data($inputDataName);
        $myid = $this->myid();
        $inputIsArr = false;
        $inputDataConfig = $inputData;
        $uiType = strtolower($this->data['type']);
        $isArray = $this->has_iterate() || $uiType == 'checkbox'
            || ($uiType=='select' && $this->data['meta']['custom']['multiple'])
            || ($uiType=='file' && $this->data['meta']['custom']['multiple']);

        if ($inputDataName){
            // 绑定数据输入的情况下，绑定的数据明确是数组时才输出为数组
            if ($this->is_array($inputData)){
                $inputIsArr = true;
            }
            return $inputDataName;
        }

        if ($isArray){
            $inputIsArr = true;
        }
        return "{$myid}_value";
    }
    protected function output_css_attrs(){
        $css = trim($this->get_css());
        $cssVariable = $this->css_of_state();
        $xShownExpression = $this->show_state_expression();
        if ($xShownExpression){
            echo $this->wrap_output('v-if', $xShownExpression);
        }
        if ($cssVariable){
            if ($css) $cssVariable[] = "'{$css}': true";
            echo $this->wrap_output(':class', "{" . join(',', $cssVariable) . "}");
        }else{
            echo $this->wrap_output('class', $css?:NULL);
        }
    }

    protected function body_text()
    {
        $text = parent::body_text();
        $outputDatas = $this->get_output_datas($dataName);
        if ($outputDatas) {
            $textDataName = $this->get_output_data_name('TEXT', $outputDatas['TEXT'], $dataName['TEXT']);
            $htmlDataName = $this->get_output_data_name('HTML', $outputDatas['HTML'], $dataName['HTML']);
        }

        if (!$htmlDataName){
            return $textDataName ? "{{{$textDataName}}}" : $text;
        }
        return '';
    }

    /**
     * 用户v-attr绑定
     * @return array
     */
    protected function get_base_data_attrs(){
        $attrs = [];

        foreach ($this->_attrs as $name => $value){
            $attrs[] = "'{$name}':'".$value."'";
        }
        $attrs[] = "'data-uiid':'".$this->myid()."'"; // css的选择器
        $outputDatas = $this->get_output_datas($dataName);
        if ($outputDatas) {
            foreach ($outputDatas as $outputAS => $outputData){
                // :class class, style不在这里输出，单独用vue的组件属性，
                if (in_array(strtoupper($outputAS), ['CSS', 'STYLE', 'HTML', 'KEYVALUE'])) continue;
                $outputDataName = $this->get_output_data_name($outputAS, $outputData, $dataName[$outputAS]);
                $output = $this->output_as_prop($outputAS, $outputData);
                if($output) $attrs[] = preg_replace("/^:/", "",$output).":".$outputDataName;
            }
        }

//        $attrs[] = "'data-root':'".$this->myid()."'";
        return $attrs;
    }
    /**
     * 该指令用于两种绑定的model是数组的情况：
     * 1. 组件被迭代输出并且绑定了数组model
     * 2. 组件没有被循环，但是单值组件，绑定了数组model
     *
     * 用了v-input绑定的数据一定时数组
     *
     * 组件被迭代但绑定但确实单值model，
     * 多值组件如checkbox，select（multiple），file（multiple）没有别迭代时，
     * 等其它情况直接使用v-model
     */
    protected function output_v_model(){
        $needIterate = $this->get_iterator_index_names();
        $inputDataName = $this->get_input_data_name($inputIsArr, $inputDataConfig);
        echo ($needIterate && $inputIsArr) || $inputIsArr ? $this->wrap_output('v-input', $inputDataName) : $this->wrap_output('v-model', $inputDataName);
    }

    protected function get_v_attrs(){
        $attrs = $this->get_base_data_attrs();
        if (!$attrs) return null;

        return "{".join(', ', $attrs)."}";
    }
    protected function output_component_props()
    {
        $xShownExpression = $this->show_state_expression();
        echo $this->wrap_output('v-if', $xShownExpression);
        echo $this->wrap_output(":iterateIndex", $this->get_iterator_index_name());
        echo $this->wrap_output(":parentIterateIndex", $this->parentView ? $this->parentView->get_iterator_index_name() : null);
        echo $this->wrap_output(":attrs", $this->get_v_attrs());

        $css = trim($this->get_css());
        $cssVariable = $this->css_of_state();
        $outputDatas = $this->get_output_datas($dataName);
        if ($outputDatas) {
            if($dataName['CSS']){
                $cssDataName = $this->get_output_data_name('CSS', $outputDatas['CSS'], $dataName['CSS']);
                $output = $this->output_as_prop('CSS', $outputDatas['CSS']);
                if($output) $boundCss = $cssDataName;
            }

            if($dataName['STYLE']){
                $styletDataName = $this->get_output_data_name('STYLE', $outputDatas['STYLE'], $dataName['STYLE']);
                $output = $this->output_as_prop('STYLE', $outputDatas['STYLE']);
                if($output) $boundStyle = $styletDataName;
            }
            if($dataName['HTML']){
                $htmlDataName = $this->get_output_data_name('HTML', $outputDatas['HTML'], $dataName['HTML']);
                $output = $this->output_as_prop('HTML', $outputDatas['HTML']);
                echo $this->wrap_output($output, $htmlDataName);
            }
            if($dataName['KEYVALUE']){
                $htmlDataName = $this->get_output_data_name('KEYVALUE', $outputDatas['KEYVALUE'], $dataName['KEYVALUE']);
                $output = $this->output_as_prop('KEYVALUE', $outputDatas['KEYVALUE']);
                echo $this->wrap_output($output, $htmlDataName);
            }
        }

        if ($cssVariable || $boundCss){
            if ($css) $cssVariable[] = "'{$css}': true";
            if ($boundCss) $cssVariable[] = "[{$boundCss}]: true";
            echo $this->wrap_output(':css', "{" . join(',', $cssVariable) . "}");
        }else{
            echo $this->wrap_output('css', $css?:NULL);
        }
        if ($boundStyle){
            echo $this->wrap_output(':style', $boundStyle);
        }

        $boundDataNames = [];
        $boundDatas = $this->get_bound_datas($boundDataNames);
        $hasIterate = $this->need_iterate_data($iterateOutputAs, $dataName, $iterateDataName);
        if($boundDatas){
            foreach ($boundDataNames as $boundType => $boundDataName){
                if ($boundType === 'BOUND'){
                    echo $this->wrap_output(":boundData", $hasIterate && $boundDataName==$dataName ? "itemOf{$iterateDataName}" : $boundDataName);
                }elseif ($boundType === 'VALUE'){
                    echo $this->wrap_output(":boundValue", $hasIterate && $boundDataName==$dataName  ? "itemOf{$iterateDataName}" : $boundDataName);
                }
            }
        }

        $this->output_event_listen_props();
    }

}
