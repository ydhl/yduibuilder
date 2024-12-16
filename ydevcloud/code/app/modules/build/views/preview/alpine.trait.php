<?php
namespace app\modules\build\views\preview;

use app\modules\build\views\code\Base_Code_Fragment;
use app\project\Page_Model;

/**
 *  html框架下用alpine处理前端的数据输入、输出绑定
 */
trait Alpine {
    use Alpine_Build_Code;
    public function build_code(): Base_Code_Fragment{
        $fragment = $this->get_code_fragment();
        if ($this->data['subPageDeleted']){
            return $fragment;
        }

        // 幻灯片，组件
        $isTopPage = !$this->find_parent($this->myid());
        // 顶级元素构建alpine代码结构主体
        if ($isTopPage){
            $this->init_page_scope_variable();
            $this->build_page_data_code(1);
            $this->build_custom_event_code();
            $this->build_lifecycle_event_code();
        }

        $this->build_event_code();
        foreach ((array)@$this->childViews as $view){
            $subPageId = $view->get_data('subPageId');
            // 子页或组件的js采用module的方式单独加载
            if ($subPageId) {
                if (!$view->get_data('subPageDeleted')){
                    $subPage = $this->get_page($subPageId);
                    $jsPath = $this->build->get_mode() == 'preview'
                        ? '/preview/page/'.$subPageId.'.js?api_env='.$_GET['api_env'].'&subpage=1&mock='.intval($this->build->need_mock()).''
                        : $this->build->get_root_path().'assets/js/'.$subPage->get_export_file_name('html').'.js';
                    $inputData = [];
                    $variables = $this->build->get_bind_variables('from');
                    foreach ($variables as $variable){
                        $expression = $variable->get_expression();
                        if ($variable->to_page_id == $subPage->id && $expression){
                            $inputData[$variable->to_data_path] = $this->remove_page_scope_data_prefix($expression->get_expression_code());
                        }
                    }
                    $fragment->add_subpage_module($subPageId, $jsPath, $inputData);
                }
                continue;
            }
            $view->build_code();
            $fragment->merge($view->get_code_fragment());
        }

        if ($isTopPage){
            // 当前页面的url
            $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, '$url: "'.$this->get_page_url($this->build->get_page()).'",');

            $this->build_component_input(2);

            if (in_array($this->build->get_page()->page_type, ['popup', 'subpage'])){
                $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent(0, true).'this.$store.loadSubPages[this.$url] = encodeURIComponent("'.$this->data['meta']['title'].'")');
            }
            // 如果是子页、组件、Modal弹窗，则作为module加载，不调用Alpine.start（由主页面调用）
            if (!$this->build->is_subpage() && !in_array($this->build->get_page()->page_type, ['popup','subpage'])){
                $store = <<< STORE
Alpine.store('loadSubPages', [])
STORE;

                $fragment->add_code(Html_Code_Fragment::SECTION_END, $this->build->indent_code(0, $store));
                $fragment->add_code(Html_Code_Fragment::SECTION_END, PHP_EOL."Alpine.start()");
            }
        }else{
            $this->build_initialize_code();
        }
        return $fragment;
    }
    private function build_component_input($indent) {
        if (!$this->build->is_subpage()) return;
        $inputCode = <<<INPITCONFIG
if(inputConfig){
    for(const myData in inputConfig){
        this[myData] = this.alpinejs_get_value(this.\$el, inputConfig[myData])
        this.\$watch(inputConfig[myData], (value, oldValue)=>{
            this[myData] = value
        })
    }
}
INPITCONFIG;

        $fragment = $this->get_code_fragment();
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->build->indent_code(0, $inputCode));

    }
    public function build_common_style(){
        $style = parent::build_common_style();
        // alpine 定义x-cloak样式，在alpine没有准备好前，整个页面不显示, 主页面输出
        if ($this->build->get_page()->uuid === $this->myid() && $this->build->get_page()->page_type != Page_Model::PAGE_TYPE_POPUP) {
            $style['[x-cloak]'] = 'display: none !important;';
        }
        return $style;
    }

    private function _build_event_code($eventCodes) {
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
            $lines[] = $this->get_event_function_name($html_event_name)."(".join(', ', array_keys($args)).") {";
            foreach ($codeBlocks as $codes){
                $lines = array_merge($lines, $this->build->indent_code(1, $codes));
            }
            $lines[] = "}";
            $codeLines[] = join(PHP_EOL, $lines);
        }
        if($codeLines) $this->get_code_Fragment()->add_code(Html_Code_Fragment::SECTION_EVENT, join(','.PHP_EOL,$codeLines).','.PHP_EOL);
    }
    protected function build_lifecycle_event_code() {
        $eventCodes = $this->get_lifecycle_event_action_codes();
        if (!$eventCodes) return;
        $this->_build_event_code($eventCodes);

        $myid = $this->myid();
        // page life cycle 事件
        foreach($eventCodes as $eventName=>$eventCode){
            $_event = strtolower(preg_replace("/^on/",'',$eventName));

            if (!strcasecmp($_event, 'load')){
                if (!in_array($this->build->get_page()->page_type, ['popup', 'subpage'])) {
                    $line = "window.addEventListener('{$_event}', (event)=>this.{$myid}_{$eventName}(".join(', ', (array)$eventCode['args'])."))";
                } else {
                    $line = "this.{$myid}_{$eventName}()";
                }
            }else{
                $line = "window.addEventListener('{$_event}', (event)=>this.{$myid}_{$eventName}(".join(', ', (array)$eventCode['args'])."))";
            }

            $this->get_code_Fragment()->add_code(Html_Code_Fragment::SECTION_INIT, $line);
        }
    }
    protected function build_custom_event_code() {
        $eventCodes = $this->get_custom_event_action_codes();
        if (!$eventCodes) return;

        $this->_build_event_code($eventCodes);
    }
    protected function build_event_code() {
        $eventCodes = $this->get_event_action_codes();
        if (!$eventCodes) return;
        $this->_build_event_code($eventCodes);
    }
    protected function output_data_output_bind(){
        // 组件
        if ($this->build->is_subpage()){
            echo $this->wrap_output('x-data', $this->myid());
        }// 顶层页面
        elseif ($this->build->get_page()->uuid === $this->myid()){
            echo $this->wrap_output('x-data', $this->myid());
            echo $this->wrap_output('x-cloak', '', true);
        }
        $myid = $this->myid();
        echo $this->wrap_output('x-id', "['{$myid}']");
        $outputDatas = $this->get_output_datas($dataName);
        if (!$outputDatas) return;

        foreach ($outputDatas as $outputAS => $outputData){
            $outputDataName = $this->get_output_data_name($outputAS, $outputData, $dataName[$outputAS]);
            echo $this->wrap_output($this->output_as_prop($outputAS, $outputData), $outputDataName);
        }
    }
    protected function need_iterate_data(&$iterateOutputAs=null, &$dataName=null, &$iterateDataName=null){
        $outputDatas = $this->get_output_datas($dataNames);
        foreach ($outputDatas as $outputAS => $outputData){
            if ($this->need_iterate_ui($outputAS, $outputData)){
                $iterateOutputAs = $outputAS;
                $dataName = $dataNames[$outputAS];
                $iterateDataName = $outputData['name'] ?: $dataName;
                // alipnejs expression 作为方法调用
                $dataName = $this->append_expression_call($outputData['isExpression'], $dataName);
                return true;
            }
        }
        return false;
    }
    private function append_expression_call($isExpression, $dataName){
        if (!$isExpression) return $dataName;
        return preg_replace("/^([^.]+)?/", '\\1()', $dataName);
    }

    /**
     * expresion 数据作为方法调用，加上(), 同时页面数据page.xxx的替换成xxx
     * @param $outputAs
     * @param $outputData
     * @param $outputDataName
     * @return string|null
     */
    protected function get_output_data_name($outputAs, $outputData, $outputDataName){
        // alipnejs expression 作为方法调用
        $outputDataName = $this->remove_page_scope_data_prefix($outputDataName);
        if ($outputDataName && $outputData['isExpression']){
            $outputDataName = $this->append_expression_call($outputData['isExpression'], $outputDataName);
        }
        $outputDataName = parent::get_output_data_name($outputAs, $outputData, $outputDataName);

        return $outputDataName;
    }
    protected function output_data_input_bind(){
        // 表单组件绑定x-model
        if (!$this->is_input_ui()) return;
        $inputDataName = $this->get_input_data_name($inputIsArr);
        if ($inputDataName){
            echo $this->wrap_output('x-input', $inputDataName);
        }
    }

    protected function get_event_listen_props(){
        $events = $this->build->get_events($this->myid());
        $eventHandlers = [];
        $myid = $this->myid();
        foreach ($events as $event){
            if(!$event->uicomponent_event_id && $this->isLifeCycleEvent($event->event)) continue;
            $name = $event->uicomponent_event_id ? $event->event : $this->eventName($event->event);
            if (!$name) continue;
            if (!$this->is_custom_ui() && !strcasecmp($name,'change')) {
                $inputDataName = $this->get_input_data_name();
                $eventHandlers['x-init'] = "\$watch(alpinejs_get_input_data_name(\$el, '{$inputDataName}'), (value, oldValue) => {$myid}_change(value, oldValue))";
            }else{
                //// html渲染时会把属性xxYYY变成xxxyyy,为了和代码中保持同步，这里统一转换成小写
                $modifiers = $event->modifier ? explode(',', $event->modifier) :[];
                if(in_array('throttle', $modifiers) || in_array('debounce', $modifiers) && $event->timeout){
                    $modifiers[] = $event->timeout."ms";
                }
                if($event->custom_key){
                    $modifiers[] = trim(strtolower($event->custom_key));
                }

                if($event->immediate && in_array('debounce', $modifiers)){
                    $modifiers[] = 'immediate';
                }
                $eventHandlers['@'.strtolower($name).($modifiers ? '.'.join('.', $modifiers) : '')] = $this->myid().'_'.$name;
            }
        }
        return $eventHandlers;
    }

    protected function output_component(){
        $iterateDataName = null;
        $dataName = null;
        $hasIteral = $this->need_iterate_data($outputAS, $dataName, $iterateDataName);
        $indent = $this->build->get_indent();
        if ($hasIteral){//  迭代输出数据
            echo $this->indent();
            echo '<template x-for="(itemOf'.$iterateDataName.', idxOf'.$iterateDataName.') in '.$dataName.'" :key="idxOf'.$iterateDataName.'">'.PHP_EOL;
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
    /**
     * 返回ydecloud对应事件名在html5上对应的事件名
     * @param $eventName
     * @return string
     */
    protected function eventName($eventName){
        $eventName = strtolower($eventName);
        if (preg_match("/^on/", $eventName, $matches)) return preg_replace("/^on/", "", $eventName);
        return $eventName;
    }
    protected function lifeCycleEvent(){
        return [
            'onload',
            'onready',
            'onshow',
            'onhide',
            'onbeforeunload',
            'onunload',
            'onpulldown',
            'onreachbottom',
            'onresize',
        ];
    }
    protected function isLifeCycleEvent($eventName){
        return in_array(strtolower($eventName), $this->lifeCycleEvent());
    }

    /**
     * 当前UI组件是否会迭代输出
     * @return bool
     */
    public function has_iterate() {
        return $this->need_iterate_data($iterateOutputAs, $outputDataName, $iterateDataName);
    }

    /**
     * 对于表单元素，输出表单特有的属性，比如name，disabled，readonly required placeholder等
     *
     * <strong style="color:red">注意这部分内容只能在具体的表单元素的上进行调用输出，比如input，textarea等</strong>
     * @param false $notOutputId 默认输出表单元素id
     */
    protected function output_form_attrs ($includeName = true, $includeUuid=true) {
        $myid = $this->myid();
        if ($includeName) {
            echo $this->wrap_output(':name', "\$id('{$myid}')");
        }

        $this->output_base_form_attrs($includeUuid);
    }
    /**
     * 对表单组件或者值列表组件构建输入数据的初始化代码
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
            $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid() . '_value: '.json_encode($this->default_value()).',');
        }
        if (!is_a($this, ValueList_View::class) || in_array(strtolower($this->data['type']), ['carousel','collapse'])) return;

        $valuesEvent = [$this->myid() . '_values(){'];
        $valuesEvent[] = $this->indent(1, true)."return ".($outputDataNames['VALUELIST']
                ? "this.".$outputDataNames['VALUELIST']
                : json_encode($this->data['meta']['values']?:$this->demo_values(), JSON_UNESCAPED_UNICODE));
        $valuesEvent[] = '},';
        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $valuesEvent);
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
                return "{$inputDataName}[-1]";
            }
            return $inputDataName;
        }

        if ($isArray){
            $inputIsArr = true;
            return "{$myid}_value[-1]";
        }
        return "{$myid}_value";
    }

    /**
     * 如果输出有数据，同时输入没有数据，这时把输出赋值给输入
     * @return void
     */
    protected function init_input_from_output(){
        $inputDataName = $this->get_input_data_name($inputIsArr, $inputData);
        $outputDatas = $this->get_output_datas($outputDataName);

        if ($inputData){
            if ($outputDataName['VALUELIST']){
                echo $this->wrap_output('x-init', "alpinejs_init_input_from_output_data(\$el, '{$inputDataName}',"
                    .$outputDataName['VALUELIST'].",".($inputData['type']=='array' ? 'true' : 'false').")");
            }else{
                $values = $this->data['meta']['values'] ?: $this->demo_values();
                $checked = [];
                foreach ($values as $value){
                    if (!$value['checked']) continue;
                    $checked[] = strlen($value['value']) ? $value['value'] : $value['name'];
                }
                if ($checked){
                    echo $this->wrap_output('x-init', "alpinejs_set_value(\$el, '{$inputDataName}',"
                        .($inputData['type']=='array' ? json_encode($checked) : "'".array_pop($checked)."'").")");
                }
            }
        }
    }
}
