<?php
namespace app\modules\build\views\preview;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\Io_Data_Fetch;
use app\project\Action_Model;
use app\project\Page_Bind_API_Action_Model;
use app\project\Page_Bind_Api_Model;
use app\project\Page_Bind_Data_Model;
use app\project\Page_Model;
use function yangzie\__;

/**
 *  html框架下用alpine处理前端的数据输入、输出绑定
 */
trait Alpine {
    public function build_code(): Base_Code_Fragment{
        $fragment = $this->get_code_fragment();
        if ($this->data['subPageDeleted']){
            return $fragment;
        }

        $isTopPage = !$this->find_parent($this->myid()) && !$this->build->is_subpage();

        // 顶级元素构建alpine代码结构主体
        if ($isTopPage){
            $fragment->add_code(Html_Code_Fragment::SECTION_BEGIN,"Alpine.data('".$this->myid()."', () => ({");
            $this->build_page_data_code(1);
        }

        $this->build_event_binding_code();
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
                            $inputData[$variable->to_data_path] = $expression->get_expression_code(true);
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
            // 在主页面记录加载的子页url及其title
            if ($this->build->get_page()->page_type == 'page'){
                $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, '$loadSubPages: {},');
            }
            // 当前页面的url
            $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, '$url: "'.$this->get_popup_page_url($this->build->get_page()).'",');
            $fragment->add_code(Html_Code_Fragment::SECTION_END, '');
            $fragment->add_code(Html_Code_Fragment::SECTION_END, $this->indent(1, true).'init(){');
            $this->build_component_input(2);
            $fragment->add_code(Html_Code_Fragment::SECTION_END, $this->build->indent_code(2, $fragment->get_section_codes(Html_Code_Fragment::SECTION_INIT)));
            if (in_array($this->build->get_page()->page_type, ['popup', 'subpage'])){
                $fragment->add_code(Html_Code_Fragment::SECTION_END, $this->indent(2, true).'this.$data.$loadSubPages[this.$url] = encodeURIComponent("'.$this->data['meta']['title'].'")');
            }
            $fragment->add_code(Html_Code_Fragment::SECTION_END, $this->indent(1, true).'}');
            $fragment->add_code(Html_Code_Fragment::SECTION_END, "}))");
            // 如果是子页、组件、Modal弹窗，则作为module加载，不调用Alpine.start（由主页面调用）
            if (!$this->build->is_subpage() && !in_array($this->build->get_page()->page_type, ['popup','subpage'])){
                $fragment->add_code(Html_Code_Fragment::SECTION_END, PHP_EOL."Alpine.start()");
            }
        }
        return $fragment;
    }
    private function build_component_input($indent) {
        if (!$this->build->is_subpage()) return;
        $fragment = $this->get_code_fragment();
        $fragment->add_code(Html_Code_Fragment::SECTION_END, $this->indent($indent, true).'if(inputConfig){');
        $fragment->add_code(Html_Code_Fragment::SECTION_END, $this->indent($indent+1, true).'for(const myData in inputConfig){');
        $fragment->add_code(Html_Code_Fragment::SECTION_END, $this->indent($indent+2, true).'this.$watch(inputConfig[myData], (value, oldValue)=>{');
        $fragment->add_code(Html_Code_Fragment::SECTION_END, $this->indent($indent+3, true).'this[myData] = value');
        $fragment->add_code(Html_Code_Fragment::SECTION_END, $this->indent($indent+2, true).'})');
        $fragment->add_code(Html_Code_Fragment::SECTION_END, $this->indent($indent+1, true).'}');
        $fragment->add_code(Html_Code_Fragment::SECTION_END, $this->indent($indent, true).'}');

    }
    public function build_common_style(){
        $style = parent::build_common_style();
        // alpine 定义x-cloak样式，在alpine没有准备好前，整个页面不显示, 主页面输出
        if ($this->build->get_page()->uuid === $this->myid() && $this->build->get_page()->page_type != Page_Model::PAGE_TYPE_POPUP) {
            $style['[x-cloak]'] = 'display: none !important;';
        }
        return $style;
    }

    protected function build_event_binding_code() {
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
            $lines[] = $this->myId(true)."_".$html_event_name."(".join(', ', $args).") {";
            $lines[] = $this->indent(1, true)."const page = this";
            foreach ($codeBlocks as $codes){
                $lines = array_merge($lines, $this->build->indent_code(1, $codes));
            }
            $lines[] = "}";
            $codeLines[] = join(PHP_EOL, $lines);
        }
        $this->get_code_Fragment()->add_code(Html_Code_Fragment::SECTION_EVENT, join(','.PHP_EOL,$codeLines).','.PHP_EOL);

        // 如果是onchange事件，则在init中通过watch输入数据来触发，不再dom中绑定@change；自定义组件的onchange除外
        if (!$this->is_custom_ui() && $eventCodes['onchange']){
            $inputData = $this->get_input_data($inputDataName);
            if (!$inputDataName) $inputDataName = $this->myid().'_temp';

            $initCodeLines = [];
            $initCodeLines[] = 'this.$watch("'.$inputDataName.'", (value, oldValue) => {';
//            $initCodeLines[] = $this->indent(1, true)."console.log('{$inputDataName}',value,oldValue)";
            $initCodeLines[] = $this->indent(1, true)."this.".$this->myId(true)."_onchange(".join(', ', $eventCodes['onchange']['args']).");";
            $initCodeLines[] = '})';
            $initCodeLines[] = '';
            $this->get_code_Fragment()->add_code(Html_Code_Fragment::SECTION_INIT, $initCodeLines);
        }
    }
    protected function build_data_output_bind(){
        // 组件
        if ($this->build->is_subpage()){
            echo $this->wrap_output('x-data', $this->myid());
        }// 顶层页面
        elseif ($this->build->get_page()->uuid === $this->myid()){
            echo $this->wrap_output('x-data', $this->myid());
            echo $this->wrap_output('x-cloak', '', true);
        }
        $outputDatas = $this->get_output_datas($dataName);
        if (!$outputDatas) return;

        foreach ($outputDatas as $outputAS => $outputData){
            $outputDataName = $this->get_output_data_name($outputAS, $outputData, $dataName[$outputAS]);
            echo $this->wrap_output($this->output_as_prop($outputAS, $outputData), $outputDataName);
        }
    }

    protected function build_data_input_bind(){
        // 表单组件绑定x-model
        if (!$this->is_input_ui()) return;
        $inputData = $this->get_input_data($inputDataName);
        $bindOutputs = $this->get_output_datas($outputDataName);
        if (!$inputData) return;
        $hasIteral = false;
        $iterateData = null;
        foreach ($bindOutputs as $outputAS => $bindOutput){
            if ($this->need_iterate_ui($outputAS, $bindOutput)){
                $iterateData = $bindOutput;
                $hasIteral = true;
                break;
            }
        }

        // .fill的作用是绑定的数据没有内容时，显示html的value属性内容
        if ($hasIteral && $this->is_array($inputData)) {// 循环输出ui并且绑定的输入数据也是数组
            echo $this->wrap_output('x-model.fill', "{$inputDataName}[idxOf{$iterateData['name']}]");
        }else{
            echo $this->wrap_output('x-model.fill', $inputDataName);
        }
    }
    protected function build_event_listen(){
        $events = $this->build->get_events($this->myid());
        $eventHandlers = [];
        foreach ($events as $event){
            $name = strtolower($event->uicomponent_event_id ? $event->event : $this->eventMap($event->event));
            if (!$this->is_custom_ui() && $name=='onchange') continue;
            $eventHandlers[] = $name;
        }
        $eventHandlers = array_unique($eventHandlers);
        foreach ($eventHandlers as $name){
            echo $this->wrap_output('@'.$name, $this->myid().'_'.$name);
        }
    }
    protected function output_component(){
        $iterateDataName = null;
        $dataName = null;
        $hasIteral = $this->need_iterate_data($outputAS, $dataName, $iterateDataName);
        $indent = $this->build->get_indent();
        if ($hasIteral){//  迭代输出数据
            echo $this->indent();
            echo '<template x-for="(itemOf'.$iterateDataName.', idxOf'.$iterateDataName.') in '.$dataName.'" :key="idxOf'.$iterateDataName.'">'.PHP_EOL;
        }

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
    protected function eventMap($eventName){
        return [
            'onload' => 'load',
            'onready' => 'ready',
            'onshow' =>'show',
            'onhide'=>'hide',
            'onbeforeunload'=>'beforeunload',
            'onunload'=>'unload',
            'onresize'=>'resize',
            'onscroll'=>'scroll',
            'onclick'=>'click',
            'ondblclick'=>'dblclick',
            'onpulldown'=>'pulldown',
            'onreachbottom'=>'reachbottom'
        ][strtolower($eventName)] ?: $eventName;
    }

    /**
     * @param $string string like /{foo}/{bar}
     * @param $inputArgs
     * @return string
     */
    private function replace_param($string, $inputArgs=[]) {
        preg_match_all("/{([^}]+)}/", $string, $matches);
        foreach ($matches[1] as $name){
            $expression = $inputArgs[$name];
            if (!$expression) continue;
            $string = preg_replace("/{{$name}}/", '${'.$expression->get_expression_code().'}', $string);
        }
        return $string;
    }

    protected function build_redirect_code(Action_Model $action, &$codeLines){
        if ($action->type != 'redirect') return;
        $inputArgs = $action->get_expression();

        if ($action->redirect_type=='outside'){
            $redirect = $this->replace_param($action->redirect, $inputArgs);
            $codeLines[] = 'document.location.href=`'.$redirect.'`';
        }else{
            $popupPage = $action->get_popup_page();
            if(!$popupPage) return;

            $bindDatas = Page_Bind_Data_Model::get_page_bind_data($popupPage->id, 'query,path');
            $args = [];
            foreach ($bindDatas as $data){
                $expression = $inputArgs[$data->uuid];
                if (!$expression) continue;
//                $codeLines[] = "const {$data->name} = ".$expression->get_expression_code();
                $args[] = $data->name.'=${'.$expression->get_expression_code().'}';
            }

            $codeLines[] = 'document.location.href=`'.$this->get_popup_page_url($popupPage).'?'.join('&', $args).'`';
        }
    }
    private function build_api_body_data(Page_Bind_Api_Model $bind_api, $formatVariables, $inputBody, &$codeLines) {

        switch (strtolower($bind_api->requestBodyType)){
            case 'none': return;
            case 'form-data':
            case 'x-www-form-urlencoded':
                if(!$inputBody) return;
                $args = [];
                foreach ($inputBody as $dataConfig){
                    $bindVariable = $formatVariables[$dataConfig['uuid']];
                    if (!$bindVariable) continue;
                    $expression = $bindVariable->get_expression();
                    if (!$expression) continue;
                    $args[] = $dataConfig['name'].'=${'.$expression->get_expression_code().'}';
                }
                if($args) $codeLines[] = $this->indent(1, true).'data: `'.join('&', $args).'`,';
                return;
            case 'json':
                $this->build_api_input($formatVariables, 'data', $inputBody, $codeLines);
                return;
            case 'xml': return;
            case 'raw': return;
            case 'binary': return;
            case 'graphql': return;
            case 'msgpack': return;
        }
    }
    protected function build_webapi_code(Action_Model $action, &$codeLines){
        $bind_api = $action->get_bind_api();
        if (!$bind_api){
            $codeLines[] = '// NO WEB API BIND';
            return;
        }
        $actions = Page_Bind_API_Action_Model::get_bind_actions($action->page_id, Page_Bind_Api_Model::CLASS_NAME, $bind_api->uuid);
        $io_data_fetch = new Io_Data_Fetch($this->build);
        $responseType = ['json'=>'json', 'xml'=>'text', 'html'=>'text', 'binary'=>'blob'];
        $inputAuth = $bind_api->get_input_configs('auth');
        $inputCookie = $bind_api->get_input_configs('cookie');
        $inputPath = $bind_api->get_input_configs('path');
        $inputBody = $bind_api->get_input_configs('body');
        $inputParam = $bind_api->get_input_configs('param');
        $inputHeader = $bind_api->get_input_configs('header');

        $bindVariables = $this->build->get_bind_variables('to', Page_Bind_Api_Model::CLASS_NAME, $bind_api->uuid);
        $formatVariables = [];
        foreach ($bindVariables as $bindVariable){
            $formatVariables[$bindVariable->to_data_id] = $bindVariable;
        }

        if ($inputCookie) {
            $args = $this->build_api_input($formatVariables, '', $inputCookie);
            if ($args) $codeLines[] = "YDECloud.setCookie(".$args.");";
        }

        $codeLines[] = "axios({";
        $codeLines[] = $this->indent(1, true)."method: '".strtolower($bind_api->method)."',";
        $codeLines[] = $this->indent(1, true)."withCredentials: true,";
        if ($inputPath){
            $params = $this->get_param_variable($formatVariables, $inputPath);
            $url = $this->replace_param($this->build->get_api_base().$bind_api->path, $params);
            $codeLines[] = $params ? $this->indent(1, true).'url: `'.$url.'`,' : $this->indent(1, true).'url: "'.$url.'",';
        }else{
            $codeLines[] = $this->indent(1, true).'url: "'.$this->build->get_api_base().$bind_api->path.'",';
        }
        $this->build_api_body_data($bind_api, $formatVariables, $inputBody, $codeLines);
        $this->build_api_input($formatVariables, 'params', $inputParam, $codeLines);
        $this->build_api_input($formatVariables, 'headers', $inputHeader, $codeLines);
        $this->build_api_input($formatVariables, 'auth', $inputAuth, $codeLines);

        $codeLines[] = $this->indent(1, true)."responseType: '".($responseType[$bind_api->get_response_type()]?:'json')."'";
        $codeLines[] = " }).then((response) => {";
        $codeLines = array_merge($codeLines, $this->build->indent_code(1, $this->get_post_processor($actions, $io_data_fetch, $bind_api)));
        $codeLines[] = " }).catch((err) =>{";
        $codeLines[] = $this->indent(1, true)."alert(err)";
        $codeLines[] = " })";
    }
    protected function build_popup_event_code(Action_Model $action, &$codeLines){
        switch ($action->popup_type){
            case 'page':
                $this->build_popup_page_code($action, $codeLines);
                break;
            case 'alert':
                $this->build_popup_alert_code($action, $codeLines);
                break;
            default: $codeLines[] ='// NOT DEFINED POPUP TYPE ';
        }
    }
    protected function build_popup_alert_code(Action_Model $action, &$codeLines){
        $expression = $action->get_expression();
        if ($expression->type == 'literal'){
            $dataName = '"'.$expression->literal.'"';
        }elseif ($expression->type == 'connect'){
            $dataName = $this->is_scale_type($expression->data->type) ? "{$expression->data->path}" : "JSON.stringify({$expression->data->path})";
        }else{
            $dataName = $expression->get_expression_code();
        }
        $codeLines[] = "alert({$dataName})";
    }
    protected function build_popup_page_code(Action_Model $action, &$codeLines){
        $pageId = $action->popupPageId;
        $page = $this->get_page($pageId);

        $page_config = json_decode(html_entity_decode($page->config), true);
        $page_type = strtoupper($page->page_type);

        if (!$pageId){
            $codeLines[] = "alert('".__("You not setting popup page, you can open event panel to setting")."');";
            return;
        }

        // 输入参数， html框架下没有path参数
        $inputExpressions = $action->get_expression();
        $bindDatas = Page_Bind_Data_Model::get_page_bind_data($page->id, 'query');
        $queryArgs = [];
        foreach ($bindDatas as $bindData){
            $inputExpression = $inputExpressions[$bindData->uuid];
            if ($inputExpression){
                if ($inputExpression->type == 'literal'){// 字面量
                    $queryArgs[] = $bindData->name.': "'.$inputExpression->literal.'"';
                }elseif ($inputExpression->type=='connect'){ //数据赋值
                    $queryArgs[] = $bindData->name.': '. $inputExpression->data->path;
                }else { //表达式赋值
                    $queryArgs[] = $bindData->name . ': ' . $inputExpression->get_expression_code();
                }
            }else{
                $queryArgs[] = $bindData->name.': '. $this->data_default($bindData->get_data_model());
            }
        }

        if ($page_type == 'POPUP'){// 加载modal
            $esc = boolval(@$page_config['meta']['custom']['esc']);
            $codeLines[] = "YDECloud.openModal({";
            $codeLines[] = $this->indent(1, true)."pageId:'{$pageId}',";
            $codeLines[] = $this->indent(1, true)."url:'".$this->get_popup_page_url($page)."?'+YDECloud.buildQuery({".join(", ", $queryArgs)."}),";
            $codeLines[] = $this->indent(1, true)."esc: ".($esc?'true':'false').",";
            $codeLines[] = $this->indent(1, true)."backdrop:'".(@$page_config['meta']['custom']['backdrop'] ??'yes')."'";
            $codeLines[] = "});";
        }else{
            $codeLines[] = "YDECloud.openPage({";
            $codeLines[] = $this->indent(1, true)."pageId:'{$pageId}',";
            $codeLines[] = $this->indent(1, true)."url:'".$this->get_popup_page_url($page)."?'+YDECloud.buildQuery({".join(", ", $queryArgs)."})";
            $codeLines[] = "});";
        }
    }
    protected function build_call_event_code(Action_Model $action, &$codeLines){

    }
    protected function build_emit_code(Action_Model $action, &$codeLines){
        if ($action->type != 'emit') return;
        $emit_event = $action->get_emit_event();
        if (!$emit_event) return;
        $argConfigs = json_decode(html_entity_decode($emit_event->args), true) ?: [];
        $inputExpressions = $action->get_expression();
        $args = [];
        foreach ($argConfigs as $argConfig){
            $inputExpression = $inputExpressions[$argConfig['uuid']];
            $args[] = $argConfig['name'].": ".$inputExpression->get_expression_code();
        }

        $codeLines[] = 'page.$dispatch("'.strtolower($emit_event->name).'", '.join(' ', ['{',join(', ',$args), '}']).')';
    }
    protected function build_mutation_code(Action_Model $action, &$codeLines){
        $mutations = $action->get_mutations();
        if (!$mutations){
            $codeLines[] = '// NOT DEFINED MUTATION ';
            return;
        }
        foreach ($mutations as $mutation){
            $bindData = $mutation->get_from_data();
            $expression = $mutation->get_expression();
            $dataModel = $bindData->get_data_model();
            $allParents = [];
            $path = [];
            $dataConfig = $bindData->find_data($mutation->mutation_data_id, $dataModel, $allParents, $path);
            $path[] = $dataConfig['name'];
            $path[] = 'page';
            $codes = [join('.', array_reverse($path)), '=', $expression->get_expression_code()];
            $codeLines[] = join(' ', $codes);
        }
    }
    protected function build_closepopup_code(Action_Model $action, &$codeLines){
        if ($action->type!='closepopup'){
            return;
        }
        $codeLines[] = 'YDECloud.closeSelf(event.target)';
    }

    private function build_api_input($formatVariables, $argName, $dataConfigs, &$codeLines=null){
        if(!$dataConfigs) return;
        $args = [];
        foreach ($dataConfigs as $dataConfig){
            $bindVariable = $formatVariables[$dataConfig['uuid']];
            if (!$bindVariable) continue;
            $expression = $bindVariable->get_expression();
            if (!$expression) continue;
            $args[] = '"'.$dataConfig['name'].'": '. $expression->get_expression_code();
        }
        if (isset($codeLines) && $args){
            $codeLines[] = $this->indent(1, true).$argName.': '.join(" ", ['{',join(',', $args),'}']).',';
        }
        return $args ? join(" ", ['{',join(',', $args),'}']) : null;
    }
    private function get_param_variable($formatVariables, $dataConfigs){
        if(!$dataConfigs) return;
        $args = [];
        foreach ($dataConfigs as $dataConfig){
            $bindVariable = $formatVariables[$dataConfig['uuid']];
            if (!$bindVariable) continue;
            $expression = $bindVariable->get_expression();
            if (!$expression) continue;
            $args[$dataConfig['name']] = $expression;
        }
        return $args;
    }
    /**
     * API响应数据输出绑定
     *
     * @param Page_Bind_Api_Model $bind_api
     * @param Action_Model $action
     * @param $codeLines
     * @return void
     */
    private function build_api_output(Page_Bind_Api_Model $bind_api, Action_Model $action, &$codeLines){
        if ($action->type != 'output') return;
        $bindVariables = $this->build->get_bind_variables('from', Page_Bind_Api_Model::CLASS_NAME, $bind_api->uuid);
        foreach ($bindVariables as $bindVariable){
            $expression = $bindVariable->get_expression();
            if (!$expression) continue;
            $codeLines[] = $bindVariable->to_data_path.' = '.$expression->get_expression_code();
        }
    }
    private function get_post_processor_body($indent, Page_Bind_API_Action_Model $bindAction, Io_Data_Fetch $io_data_fetch, Page_Bind_Api_Model $bind_api, &$bodyLines){
        $actions = $bindAction->get_actions();
        foreach ($actions as $action){
            if ($action->type=='output'){
                $codes = [];
                $this->build_api_output($bind_api, $action, $codes);
                $bodyLines = array_merge($bodyLines, $this->build->indent_code($indent, $codes));
            }else if ($action->type=='redirect'){
                $codes = [];
                $this->build_redirect_code($action, $codes);
                $bodyLines = array_merge($bodyLines, $this->build->indent_code($indent, $codes));
            }else if ($action->type=='popup'){
                $codes = [];
                $this->build_popup_event_code($action, $codes);
                $bodyLines = array_merge($bodyLines, $this->build->indent_code($indent, $codes));
            }else if ($action->type=='mutation'){
                $codes = [];
                $this->build_mutation_code($action, $codes);
                $bodyLines = array_merge($bodyLines, $this->build->indent_code($indent, $codes));
            }else if ($action->type=='webapi'){
                // api 主体单独生成一个方法
                $innerMethod = "call_api_inner";
                $bodyLines[] = $this->indent($indent, true).'page.'.$this->myId(true)."_{$innerMethod}(rst)";

                $apicCodes = ['const page = this;'];
                $this->build_webapi_code($action, $apicCodes);
                $apicCodes = $this->build->indent_code(1, $apicCodes);
                array_unshift($apicCodes,$this->myId(true)."_{$innerMethod}(rst) {");
                array_unshift($apicCodes,'');
                $apicCodes[] = '},';
                $this->get_code_Fragment()->add_code(Html_Code_Fragment::SECTION_EVENT, $apicCodes);

                break;
            }else{
                $bodyLines[] = $this->indent($indent, true).'// NOT DEFINED ACTION TYPE '.$action->type;
            }
        }
    }
    private function get_post_processor($bindActions, Io_Data_Fetch $io_data_fetch, Page_Bind_Api_Model $bind_api){
        $codeLines = [];
        $codeLines[] = "const rst = response.data;";
        foreach ($bindActions as $index => $bindAction){
            if ($bindAction->mode == 'code') {
                $codeLines[] = "const promise{$index} = new Promise((resolve) => {";
                $codeLines = array_merge($codeLines, $this->build->indent_code(1, $bindAction->code));
                $codeLines[] = "})";
                $codeLines[] = "promise{$index}.then(() => {";
                $this->get_post_processor_body(1, $bindAction, $io_data_fetch, $bind_api, $codeLines);
                $codeLines[] = "})";
            }else{
                $expression = $bindAction->get_expression();
                if ($expression) $codeLines[] = "if (".$expression->get_expression_code()."){";
                $this->get_post_processor_body($expression ? 1 : 0, $bindAction, $io_data_fetch, $bind_api, $codeLines);
                if ($expression) $codeLines[] = "}";
            }
        }
        return $codeLines;
    }
    private function build_page_data_code($indent) {
        $fragment = $this->get_code_Fragment();
        $build = $this->build;
        $index = 0;
        // 对于弹窗访问，参数在加载js的地址中
        $queryString = $this->build->get_page()->page_type == Page_Model::PAGE_TYPE_POPUP ? ',import.meta.url' : '';
        foreach ($build->get_bound_datas() as $bound_data){
            $dataConfig = $bound_data->get_data_model();
            if ($dataConfig['comment'] || $dataConfig['title'] || $dataConfig['deprecated']){
                $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, "/**");
                if($dataConfig['title']) $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, " * ".$dataConfig['title']);
                if($dataConfig['comment']) $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, " * ".$dataConfig['comment']);
                if($dataConfig['deprecated']) $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, " * ".($dataConfig['deprecated'] ? 'Deprecated' : ''));
                $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, " */");
            }
            $defaultValue = $this->data_default($dataConfig);
            if ($bound_data->data_from==Page_Bind_Data_Model::DATA_FROM_PATH){
                $defaultValue = 'YDECloud.getPathArgValue("'.$bound_data->name.'") || '.$defaultValue;
            }else if ($bound_data->data_from==Page_Bind_Data_Model::DATA_FROM_QUERY){
                $defaultValue = 'YDECloud.getQueryValue("'.$bound_data->name.'"'.$queryString.') || '.$defaultValue;
            }

            if ($defaultValue && $build->need_mock() && $dataConfig['mock']) {
                $defaultValue = 'Mock.mock('.$defaultValue.')';
            }
            $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $bound_data->name.': '.$defaultValue.',');

            $index++;
        }
        $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, "");
        $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, "\$title: \"{$this->data['meta']['title']}\",");
        $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, "\$pageId: \"{$this->myid()}\",");
    }
}
