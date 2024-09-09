<?php
namespace app\modules\build\views\preview;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\Io_Data_Fetch;
use app\project\Action_Model;
use app\project\Page_Bind_API_Action_Model;
use app\project\Page_Bind_Api_Model;
use app\project\Page_Bind_Data_Model;
use app\project\Page_Bind_Event_Model;
use app\project\Page_Bind_Io_Model;
use app\project\Page_Model;
use app\project\Web_Api_Model;
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

        // 幻灯片，组件
        $isTopPage = !$this->find_parent($this->myid());
        // 顶级元素构建alpine代码结构主体
        if ($isTopPage){
            $this->build_page_data_code(1);
            $this->build_custom_event_code();
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
            // 当前页面的url
            $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, '$url: "'.$this->get_popup_page_url($this->build->get_page()).'",');

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

    protected function build_custom_event_code() {
        $eventCodes = $this->get_popup_event_action_codes();
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
            $lines[] = $this->get_event_function_name($html_event_name)."(".join(', ', $args).") {";
            $lines[] = $this->indent(1, true)."const page = this";
            foreach ($codeBlocks as $codes){
                $lines = array_merge($lines, $this->build->indent_code(1, $codes));
            }
            $lines[] = "}";
            $codeLines[] = join(PHP_EOL, $lines);
        }
        if($codeLines) $this->get_code_Fragment()->add_code(Html_Code_Fragment::SECTION_EVENT, join(','.PHP_EOL,$codeLines).','.PHP_EOL);
    }
    protected function build_event_code() {
        $eventCodes = $this->get_event_action_codes();
        if (!$eventCodes) return;
        $myid = $this->myid();

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
            $lines[] = $this->get_event_function_name($html_event_name)."(".join(', ', $args).") {";
            $lines[] = $this->indent(1, true)."const page = this";
            foreach ($codeBlocks as $codes){
                $lines = array_merge($lines, $this->build->indent_code(1, $codes));
            }
            $lines[] = "}";
            $codeLines[] = join(PHP_EOL, $lines);
        }
        if($codeLines) $this->get_code_Fragment()->add_code(Html_Code_Fragment::SECTION_EVENT, join(','.PHP_EOL,$codeLines).','.PHP_EOL);

        // page life cycle 事件
        foreach($this->lifeCycleEvent() as $eventName){
            if (!$eventCodes[$eventName]) continue;
            $_event = preg_replace("/^on/",'',$eventName);

            if (!strcasecmp($_event, 'load')){
                if (!in_array($this->build->get_page()->page_type, ['popup', 'subpage'])) {
                    $line = "window.addEventListener('{$_event}', (event)=>this.{$myid}_{$eventName}(".join(', ', (array)$eventCodes[$eventName]['args'])."))";
                } else {
                    $line = "this.{$myid}_{$eventName}()";
                }
            }else{
                $line = "window.addEventListener('{$_event}', (event)=>this.{$myid}_{$eventName}(".join(', ', (array)$eventCodes[$eventName]['args'])."))";
            }

            $this->get_code_Fragment()->add_code(Html_Code_Fragment::SECTION_INIT, $line);
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
        $inputDataName = $this->get_input_data_name($inputIsArr);
        if ($inputDataName){
            echo $this->wrap_output('x-input', $inputDataName);
        }
    }

    protected function build_event_listen(){
        $eventHandlers = $this->get_event_listen_props();
        foreach ($eventHandlers as $name => $func){
            echo $this->wrap_output($name, $func);
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
            $this->set_iterator_index_name("idxOf{$iterateDataName}");
            $this->set_iterator_data_name("itemOf{$iterateDataName}");
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
    protected function eventName($eventName){
        return [
            'onchange'=>'change',
            'oninput'=>'input',
            'onblur'=>'blur',
            'onfocus'=>'focus',
            'onkeyup'=>'keyup',
            'onkeydown'=>'keydown',
            'onkeypress'=>'keypress',
            'onclick'=>'click',
            'ondblclick'=>'dblclick',
            'onmousedown'=>'mousedown',
            'onmouseup'=>'mouseup',
            'onmouseover'=>'mouseover',
            'onmouseout'=>'mouseout',
            'onmousemove'=>'mousemove',
            'onmouseenter'=>'mouseenter',
            'onmouseleave'=>'mouseleave'
        ][strtolower($eventName)] ?: $eventName;
    }
    private function lifeCycleEvent(){
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
     * @param $string string like /{foo}/{bar}
     * @param $inputArgs
     * @return string
     */
    private function replace_param($string, $inputArgs=[]) {
        preg_match_all("/{([^}]+)}/", $string, $matches);
        foreach ($matches[1] as $name){
            $expression = $inputArgs[$name];
            if (!$expression) continue;
            $string = preg_replace("/{{$name}}/i", '\\${'.$expression->get_expression_code().'}', $string);
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
                $expression_code = $expression->get_expression_code();
                $args[] = $data->name.'=${'.$expression_code.'}';
                $this->add_used_variable($expression_code);
            }

            $codeLines[] = 'document.location.href=`'.$this->get_popup_page_url($popupPage).'?'.join('&', $args).'`';
        }
    }
    protected function build_interval_code(Action_Model $action, &$codeLines){
        if ($action->type != 'interval') return;
        $myid = $this->myid();
        $delay = intval($action->interval_delay)?:1000;
        $duration = intval($action->interval_duration)?:1000;

        $action_ids = array_filter(explode(',', $action->interval_action));
        $complete_ids = array_filter(explode(',', $action->interval_complete));
        $ids = array_filter(array_merge($action_ids, $complete_ids));

        $subActions = $ids ? Action_Model::from()->where('is_deleted = 0 and id in ('.join(',',$ids).')')->select() : [];
        $actionCodes = [];
        $completeCodes = [];
        foreach ($subActions as $subAction){
            if (in_array($subAction->id, $action_ids)){
                $this->get_action_code(1, $subAction, $actionCodes);
            }
            if (in_array($subAction->id, $complete_ids)){
                $this->get_action_code(2, $subAction, $completeCodes);
            }
        }
        $actionCodes = $actionCodes ? join(PHP_EOL, $actionCodes).PHP_EOL : '';
        $completeCodes = $completeCodes ? join(PHP_EOL, $completeCodes).PHP_EOL : '';

        $interval = <<< INTERVAL
if (page.{$myid}_interval) return;
page.{$myid}_remainTime = {$duration};
event.target.setAttribute('disabled', true)
page.{$myid}_interval = setInterval(() => {
    page.{$myid}_remainTime -= {$delay};

    if(page.{$myid}_remainTime<=0){
        clearInterval(page.{$myid}_interval);
        page.{$myid}_interval = null;
        event.target.removeAttribute('disabled')
        page.{$myid}_remainTime = 0

{$completeCodes}
        return;
    }
    
    const remainTime = page.{$myid}_remainTime;
{$actionCodes}
}, {$delay})
INTERVAL;

        $codeLines[] = $interval;
    }
    protected function build_axios_code(Page_Bind_Api_Model $bind_api, &$codeLines, $onUploadProgress = [], $onSuccess = []){
        $bindApiActions = Page_Bind_API_Action_Model::get_bind_actions($bind_api->page_id, Page_Bind_Api_Model::CLASS_NAME, $bind_api->uuid);
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
            $args = $this->build_api_form_data($formatVariables, $inputCookie, $codeLines, 'cookie');
            if ($args) $codeLines[] = "YDECloud.setCookie(".$args.");";
        }
        $method = strtolower($bind_api->method);

        $bodyCodes = $this->build_api_body_data($bind_api, $formatVariables, $inputBody, $codeLines);
        $paramCode = $this->build_api_form_data($formatVariables,  $inputParam, $codeLines, 'params');
        $headerCode = $this->build_api_form_data($formatVariables, $inputHeader, $codeLines, 'headers');
        $authCode = $this->build_api_form_data($formatVariables,  $inputAuth, $codeLines, 'auth');

        $codeLines[] = "axios({";
        $codeLines = array_merge($codeLines, $bodyCodes);
        if ($paramCode) $codeLines[] = $this->indent(1, true)."params: {$paramCode},";
        if ($headerCode) $codeLines[] = $this->indent(1, true)."headers: {$headerCode},";
        if ($authCode) $codeLines[] = $this->indent(1, true)."auth: {$authCode},";

        $codeLines[] = $this->indent(1, true)."responseType: '".($responseType[$bind_api->get_response_type()]?:'json')."',";

        if ($onUploadProgress){
            $codeLines[] = $this->indent(1, true)."onUploadProgress: (progressEvent) => {";
            $codeLines[] = $this->indent(2, true)."const progress = progressEvent.total > 0 ? ~~(progressEvent.loaded / progressEvent.total * 100) : 0;";
            $codeLines = array_merge($codeLines, $this->build->indent_code(2, $onUploadProgress));
            $codeLines[] = $this->indent(1, true)."},";
        }

        $codeLines[] = $this->indent(1, true).'method: "'.$method.'",';
        $codeLines[] = $this->indent(1, true)."withCredentials: true,";
        if ($inputPath){
            $params = $this->get_param_variable($formatVariables, $inputPath);
            $url = $this->replace_param($this->build->get_api_base().$bind_api->path, $params);
            $codeLines[] = $params ?
                $this->indent(1, true).'url: `'.$url.'`,' :
                $this->indent(1, true).'url: "'.$url.'"';
        }else{
            $codeLines[] = $this->indent(1, true).'url: "'.$this->build->get_api_base().$bind_api->path.'"';
        }

        $codeLines[] = " }).then((response) => {";
        $codeLines = array_merge($codeLines, $this->build->indent_code(1, $this->get_post_processor($bindApiActions, $io_data_fetch, $bind_api)));
        if ($onSuccess) {
            $codeLines = array_merge($codeLines, $this->build->indent_code(1, $onSuccess));
        }
        $codeLines[] = " }).catch((err) =>{";
        $codeLines[] = $this->indent(1, true)."alert(err)";
        $codeLines[] = " })";
    }
    protected function build_webapi_code(Action_Model $action, &$codeLines){
        $bind_api = $action->get_bind_api();
        if (!$bind_api){
            $codeLines[] = '// NO WEB API BIND';
            return;
        }

        $this->build_axios_code($bind_api, $codeLines);
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
            $dataName = $expression->literal;
        }elseif ($expression->type == 'connect'){
            $dataName = $this->is_scale_type($expression->data->type) ? "{$expression->data->path}" : "JSON.stringify({$expression->data->path})";
        }else{
            $dataName = $expression->get_expression_code();
            $this->add_used_variable($dataName);
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
                    $expression_code = $inputExpression->get_expression_code();
                    $this->add_used_variable($expression_code);
                    $queryArgs[] = $bindData->name . ': ' . $expression_code;
                }
            }else{
                $queryArgs[] = $bindData->name.': '. $this->data_default($bindData->get_data_model());
            }
        }
        $popup_events = [];
        $page_uuid = $this->get_page_uuid();
        $events = $this->build->get_popup_events();
        foreach ($events as $event){
            if(!$event->uicomponent_event_id || $event->get_uicomponent_event()->page_id !== $page->id) continue;
            $name = strtolower($event->event);
            if (!$name) continue;
            $popup_events['@'.$name] = $page_uuid.'_'.$name;
        }

        if ($page_type == 'POPUP'){// 加载modal
            $esc = boolval(@$page_config['meta']['custom']['esc']);
            $codeLines[] = "YDECloud.openModal({";
            $codeLines[] = $this->indent(1, true)."currPageId:'".$this->build->get_page()->uuid."',";
            $codeLines[] = $this->indent(1, true)."pageId:'{$pageId}',";
            $codeLines[] = $this->indent(1, true)."url:'".$this->get_popup_page_url($page)."?'+YDECloud.buildQuery({subpage:1, ".join(", ", $queryArgs)."}),";
            $codeLines[] = $this->indent(1, true)."esc: ".($esc?'true':'false').",";
            $codeLines[] = $this->indent(1, true)."backdrop:'".(@$page_config['meta']['custom']['backdrop'] ??'yes')."',";
            $codeLines[] = $this->indent(1, true)."events:".json_encode($popup_events);
            $codeLines[] = "});";
        }else{
            $codeLines[] = "YDECloud.openPage({";
            $codeLines[] = $this->indent(1, true)."currPageId:'".$this->build->get_page()->uuid."',";
            $codeLines[] = $this->indent(1, true)."pageId:'{$pageId}',";
            $codeLines[] = $this->indent(1, true)."url:'".$this->get_popup_page_url($page)."?'+YDECloud.buildQuery({subpage:1, ".join(", ", $queryArgs)."}),";
            $codeLines[] = $this->indent(1, true)."esc: true,";
            $codeLines[] = $this->indent(1, true)."backdrop:'yes',";
            $codeLines[] = $this->indent(1, true)."events:".json_encode($popup_events);
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
            $expression_code = $inputExpression->get_expression_code();
            $this->add_used_variable($expression_code);
            $args[] = $argConfig['name'].": ".$expression_code;
        }

        $codeLines[] = 'page.$dispatch("'.strtolower($emit_event->name).'", '.join(' ', ['{',join(', ',$args), '}']).')';
    }
    protected function build_mutation_code(Action_Model $action, &$codeLines){
        $mutations = $action->get_mutations();
        if (!$mutations){
            $codeLines[] = '// NOT DEFINED MUTATION ';
            return;
        }
//        $codeLines[] = 'debugger ';
        foreach ($mutations as $mutation){
            $bindData = $mutation->get_from_data();
            $expression = $mutation->get_expression();
            $dataModel = $bindData->get_data_model();
            $allParents = [];
            $path = [];
            $dataConfig = $bindData->find_data($mutation->mutation_data_id, $dataModel, $allParents, $path);
            $path[] = 'page';
            $path = array_reverse($path);
            $path[] = $dataConfig['name'];
            $rightValue = $expression->get_expression_code();
            $operatior = $mutation->mutation_operator ?: ' = ';
            $operatior = preg_replace("/@/", $rightValue, $operatior);
            $codes = [join('.', $path), $operatior];
            $this->add_used_variable($rightValue);
            $codeLines[] = join('', $codes);
        }
    }
    protected function build_closepopup_code(Action_Model $action, &$codeLines){
        if ($action->type!='closepopup'){
            return;
        }
        $codeLines[] = 'YDECloud.closeSelf(event.target)';
    }
    protected function build_break_code(Action_Model $action, &$codeLines)
    {
        $codeLines[] = 'return;';
    }
    protected function build_validate_code(Action_Model $action, &$codeLines){
        $validates = $action->get_validate_datas();
        $subActions = $action->get_sub_condition_action();
        if ($subActions)  $codeLines[] = 'let hasError = false;';
        foreach ($validates as $validate) {
            $data = $validate->get_validate_data();
            $fullName = $data['fullName'];
            $noPrefix = preg_replace("/^page./", "", $fullName);
            if ($data['validRegular']){
                $regular = trim($data['validRegular'], '/');
                $this->_build_validate_code($codeLines, "{$fullName}.match(/{$regular}/)", $noPrefix, $data['invalidMsg'], $subActions);
            }else if (strtolower($data['validRule'])=='notempty'){
                $this->_build_validate_code($codeLines, "{$fullName}", $noPrefix, $data['invalidMsg'], $subActions);
            }
        }
        if ($subActions['true']){
            $codeLines[] = "if(!hasError){";
            foreach ($subActions['true'] as $subAction){
                $this->get_action_code(1, $subAction, $codeLines);
            }
            $codeLines[] = "}";
        }

        if ($subActions['false']){
            $codeLines[] = "if(hasError){";
            foreach ($subActions['false'] as $subAction){
                $this->get_action_code(1, $subAction, $codeLines);
            }
            $codeLines[] = "}";
        }
    }
    private function _build_validate_code(&$codeLines, $check, $errorKey, $msg, $subActions){
        $codeLines[] = "if({$check}){";
        $codeLines[] = $this->indent(1, true)."delete page.error['{$errorKey}']";
        $codeLines[] = "} else{";
        if ($subActions) $codeLines[] = $this->indent(1, true)."hasError = true";
        $codeLines[] = $this->indent(1, true)."page.error['{$errorKey}'] =  '{$msg}'";
        $codeLines[] = "}";
    }

    /**
     * 当前UI组件是否会迭代输出
     * @return bool
     */
    public function has_iterate() {
        return $this->need_iterate_data($iterateOutputAs, $outputDataName, $iterateDataName);
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

    private function build_api_body_data(Page_Bind_Api_Model $bind_api, $formatVariables, $inputBody, &$codeLines) {
        if(!$inputBody) return [];

        switch (strtolower($bind_api->requestBodyType)){
            case 'none': return [];
            case 'x-www-form-urlencoded':
                $codeLines[] = "const formData = [];";
                foreach ($inputBody as $dataConfig){
                    $bindVariable = $formatVariables[$dataConfig['uuid']];
                    if (!$bindVariable) continue;
                    $expression = $bindVariable->get_expression();
                    if (!$expression) continue;
                    $exp = $expression->get_expression_code();
                    if ($dataConfig['type'] == 'array'){
                        $codeLines[] = "if({$exp} !== undefined){";
                        $codeLines[] = $this->indent(1, true)."for(const item of {$exp}) {";
                        $codeLines[] = $this->indent(2, true).'formData.push(`'.$dataConfig['name'].'[]=${item}`);';
                        $codeLines[] = $this->indent(1, true)."}";
                        $codeLines[] = '}';
                    }else{
                        $codeLines[] = 'formData.push(`'.$dataConfig['name'].'=${'.$exp.'}`);';
                    }
                    $this->add_used_variable($exp);
                }
                return [$this->indent(1, true)."data: formData.join('&'),"];
            case 'form-data':
                $codeLines[] = "const formData = new FormData();";
                foreach ($inputBody as $dataConfig){
                    $bindVariable = $formatVariables[$dataConfig['uuid']];
                    if (!$bindVariable) continue;
                    $expression = $bindVariable->get_expression();
                    if (!$expression) continue;
                    $expression_code = $expression->get_expression_code();
                    $this->add_used_variable($expression_code);

                    $codeLines[] = 'if('.$expression_code.' !== undefined){';
                    if ($dataConfig['type'] == 'array'){
                        $codeLines[] = $this->indent(1, true)."for(const item of ".$expression_code.") {";
                        $codeLines[] = $this->indent(2, true).'formData.append("'.$dataConfig['name'].'[]", item);';
                        $codeLines[] = $this->indent(1, true)."}";
                    }else{
                        $codeLines[] = $this->indent(1, true).'formData.append("'.$dataConfig['name'].'", '.$expression_code.');';
                    }
                    $codeLines[] = '}';
                }
                return [$this->indent(1, true).'data: formData,'];
            case 'json':
                return ['data:' . $this->build_api_json_data($formatVariables, $inputBody, $codeLines, 'jsonData').','];
            case 'xml': return [];
            case 'raw': return [];
            case 'binary': return [];
            case 'graphql': return [];
            case 'msgpack': return [];
            default: return [];
        }
    }
    private function build_api_form_data($formatVariables, $dataConfigs, &$codeLines=[], $dataName=''){
        if(!$dataConfigs) return null;
        foreach ($dataConfigs as $dataConfig){
            $bindVariable = $formatVariables[$dataConfig['uuid']];
            if (!$bindVariable) continue;
            $expression = $bindVariable->get_expression();
            if (!$expression) continue;
            $expression_code = $expression->get_expression_code();
            $this->add_used_variable($expression_code);

            if ($dataConfig['type'] == 'array'){
                $myCodes[] = "if({$expression_code} !== undefined){";
                $myCodes[] = $this->indent(1, true)."for(const index in {$expression_code}) {";
                $myCodes[] = $this->indent(2, true)."const item = {$expression_code}[index];";
                $myCodes[] = $this->indent(2, true)."_{$dataName}[`{$dataConfig['name']}[\${index}]`] = item;";
                $myCodes[] = $this->indent(1, true)."}";
                $myCodes[] = '}';
            }else{
                $myCodes[] = "_{$dataName}[\"{$dataConfig['name']}\"] = $expression_code";
            }
        }
        if ($myCodes){
            array_unshift($myCodes, "const _{$dataName} = {};");
            $codeLines = array_merge($codeLines, $myCodes);
            return "_{$dataName}";
        }else{
            return null;
        }
    }
    private function build_api_json_data($formatVariables, $dataConfigs, &$codeLines=[], $dataName=''){
        if(!$dataConfigs) return null;
        $codeLines[] = "const _{$dataName} = {};";
        foreach ($dataConfigs as $dataConfig){
            $bindVariable = $formatVariables[$dataConfig['uuid']];
            if (!$bindVariable) continue;
            $expression = $bindVariable->get_expression();
            if (!$expression) continue;
            $expression_code = $expression->get_expression_code();

            $codeLines[] = "if({$expression_code} !== undefined){";
            $codeLines[] = $this->indent(1, true)."_{$dataName}[{$dataConfig['name']}] = {$expression_code}";
            $codeLines[] = '}';
            $this->add_used_variable($expression_code);
        }

        return "_{$dataName}";
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
            $expression_code = $expression->get_expression_code();
            $codeLines[] = $bindVariable->to_data_path . ' = ' . $expression_code;
            $this->add_used_variable($expression_code);
        }
    }

    /**
     * @param $indent
     * @param $action
     * @param $bodyLines
     * @param $bind_api Page_Bind_Api_Model 触发该action的 bind api
     * @return void
     */
    private function get_action_code($indent, $action, &$bodyLines=[], $bind_api=null){
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
        }else if ($action->type=='interval'){
            $codes = [];
            $this->build_interval_code($action, $codes);
            $bodyLines = array_merge($bodyLines, $this->build->indent_code($indent, $codes));
        }else if ($action->type=='webapi'){
            $apicCodes = [];
            $this->build_webapi_code($action, $apicCodes);
            $bodyLines = array_merge($bodyLines, $this->build->indent_code($indent, $apicCodes));
        }else if ($action->type=='validate'){
            $apicCodes = [];
            $this->build_validate_code($action, $apicCodes);
            $bodyLines = array_merge($bodyLines, $this->build->indent_code($indent, $apicCodes));
        }else if ($action->type=='break'){
            $apicCodes = [];
            $this->build_break_code($action, $apicCodes);
            $bodyLines = array_merge($bodyLines, $this->build->indent_code($indent, $apicCodes));
        }else{
            $bodyLines[] = $this->indent($indent, true).'// NOT DEFINED ACTION TYPE '.$action->type;
        }
    }
    private function get_post_processor_body($indent, Page_Bind_API_Action_Model $bindAction, Io_Data_Fetch $io_data_fetch, Page_Bind_Api_Model $bind_api, &$bodyLines, $trueFalse='true'){
        $actions = $bindAction->get_actions();
        foreach ($actions as $action){
            if ($action->bind_condition == $trueFalse){
                $this->get_action_code($indent, $action, $bodyLines, $bind_api);
            }
        }
    }
    private function get_post_processor($bindActions, Io_Data_Fetch $io_data_fetch, Page_Bind_Api_Model $bind_api){
        $codeLines = [];
        $codeLines[] = "const rst = response.data;";
        $index = 0;
        foreach ($bindActions as $bindAction){
            if ($bindAction->mode == 'code') {
                $codeLines[] = "const promise{$index} = new Promise((resolve) => {";
                $codeLines = array_merge($codeLines, $this->build->indent_code(1, $bindAction->code));
                $codeLines[] = "})";
                $codeLines[] = "promise{$index}.then(() => {";
                $this->get_post_processor_body(1, $bindAction, $io_data_fetch, $bind_api, $codeLines, 'true');
                $codeLines[] = "}).catch((e) => {";
                $this->get_post_processor_body(1, $bindAction, $io_data_fetch, $bind_api, $codeLines, 'false');
                $codeLines[] = "})";
            }else{
                $expression = $bindAction->get_expression();
                if ($expression) {
                    $expression_code = $expression->get_expression_code();
                    $codeLines[] = "if ({$expression_code}){";
                    $this->add_used_variable($expression_code);
                }
                $this->get_post_processor_body($expression ? 1 : 0, $bindAction, $io_data_fetch, $bind_api, $codeLines);
                if ($expression) $codeLines[] = "}";
            }
            $index ++;
        }
        return $codeLines;
    }
    private function build_page_data_code($indent) {
        $fragment = $this->get_code_Fragment();
        $build = $this->build;
        $index = 0;
        // 对于弹窗访问，参数在加载js的地址中
        $queryString = $this->build->is_subpage() ? ',import.meta.url' : '';
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
        $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, 'error: {},');
        $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, "");
        $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, "\$title: \"{$this->data['meta']['title']}\",");
        $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, "\$pageId: \"{$this->myid()}\",");
    }

}
