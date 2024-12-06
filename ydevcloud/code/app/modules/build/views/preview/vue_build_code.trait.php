<?php
namespace app\modules\build\views\preview;

use app\modules\build\views\code\Io_Data_Fetch;
use app\project\Action_Model;
use app\project\Page_Bind_Api_Action_Model;
use app\project\Page_Bind_Api_Model;
use app\project\Page_Bind_Data_Model;
use app\project\Page_Model;
use function yangzie\__;

/**
 * 获取html框架下的代码fragment对象, 目前采用alpine，如果需要用其他的，修改这里的代码即可，ui组件中不用修改
 */
trait Vue_Build_Code {
    /**
     * API响应数据输出绑定
     *
     * @param Page_Bind_Api_Model $bind_api
     * @param Action_Model $action
     * @param $codeLines
     * @return void
     */
    protected function build_api_output(Page_Bind_Api_Model $bind_api, Action_Model $action, &$codeLines){
        if ($action->type != 'output') return;
        $bindVariables = $this->build->get_bind_variables('from', Page_Bind_Api_Model::CLASS_NAME, $bind_api->uuid);
        foreach ($bindVariables as $bindVariable){
            $expression = $bindVariable->get_expression();
            if (!$expression) continue;
            $expression_code = $this->append_vue_value($expression->get_expression_code());
            $codeLines[] = $this->append_vue_value($bindVariable->to_data_path) . ' = ' . $expression_code;
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
    protected function get_action_code($indent, $action, &$bodyLines=[], $bind_api=null){
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
    protected function get_post_processor_body($indent, Page_Bind_API_Action_Model $bindAction, Io_Data_Fetch $io_data_fetch, Page_Bind_Api_Model $bind_api, &$bodyLines, $trueFalse='true'){
        $actions = $bindAction->get_actions();
        foreach ($actions as $action){
            if ($action->bind_condition == $trueFalse){
                $this->get_action_code($indent, $action, $bodyLines, $bind_api);
            }
        }
    }
    protected function get_post_processor($bindActions, Io_Data_Fetch $io_data_fetch, Page_Bind_Api_Model $bind_api){
        $codeLines = [];
        $codeLines[] = "const rst = response.data;";
        $index = 0;
        foreach ($bindActions as $bindAction){
            if ($bindAction->mode == 'code') {
                $codeLines[] = "const promise{$index} = new Promise((resolve, reject) => {";
                $expression_code = $this->append_vue_value(html_entity_decode($bindAction->code));
                $codeLines = array_merge($codeLines, $this->build->indent_code(1, $expression_code));
                $codeLines[] = "})";
                $codeLines[] = "promise{$index}.then(() => {";
                $this->get_post_processor_body(1, $bindAction, $io_data_fetch, $bind_api, $codeLines, 'true');
                $codeLines[] = "}).catch((e) => {";
                $this->get_post_processor_body(1, $bindAction, $io_data_fetch, $bind_api, $codeLines, 'false');
                $codeLines[] = "})";
            }else{
                $expression = $bindAction->get_expression();
                if ($expression) {
                    $expression_code = $this->append_vue_value($expression->get_expression_code());
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

    protected function build_page_data_code($indent=0) {
        $fragment = $this->get_code_Fragment();
        $build = $this->build;
        $index = 0;
        foreach ($build->get_bound_datas() as $bound_data){
            $dataConfig = $bound_data->get_data_model();
            $comment = [];
            if ($dataConfig['comment'] || $dataConfig['title'] || $dataConfig['deprecated']){
                $comment[] = "/**";
                if($dataConfig['title']) $comment[] = " * ".$dataConfig['title'];
                if($dataConfig['comment']) $comment[] = " * ".$dataConfig['comment'];
                if($dataConfig['deprecated']) $comment[] = " * ".($dataConfig['deprecated'] ? 'Deprecated' : '');
                $comment[] = " */";
            }
            $defaultValue = $this->data_default($dataConfig);
            $needComputed = false;
            if ($bound_data->data_from==Page_Bind_Data_Model::DATA_FROM_PATH){
                $fragment->add_import('vue-router',['useRoute']);
                $fragment->add_const('route', 'useRoute()');
                $defaultValue = 'route.params.'.$bound_data->name.' || '.$defaultValue;
                $needComputed = true;
            }else if ($bound_data->data_from==Page_Bind_Data_Model::DATA_FROM_QUERY){
                $needComputed = true;
                if (in_array($this->get_build()->get_page()->page_type,['popup','component'])){// 弹窗和组件的输入参数通过属性输入
                    $fragment->add_import('vue',['defineProps']);
                    $fragment->add_prop('param', 'Object');
                    $defaultValue = 'param?.'.$bound_data->name.' || '.$defaultValue;
                }else{
                    $fragment->add_import('vue-router',['useRoute']);
                    $fragment->add_const('route', 'useRoute()');
                    $defaultValue = $this->get_build()->get_page()->page_type.'route.query.'.$bound_data->name.' as string || '.$defaultValue;
                }
            }

            if ($needComputed){
                $fragment->add_import('vue', ['computed']);
                $fragment->add_computed($bound_data->name, "()=>{$defaultValue}", $this->data_type($dataConfig), join(PHP_EOL, $comment));
            }else{
                $fragment->add_ref($bound_data->name, $defaultValue, $this->data_type($dataConfig), join(PHP_EOL, $comment));
            }

            $index++;
        }
        $fragment->add_ref('error', '{}', '<Record<string,string>>');
    }
    private function data_type($data){
        switch ($data['type']){
            case 'array': return "<Array".$this->data_type($data['item']).">";
            case 'string': return "<string>";
            case 'integer':
            case 'number': return "<number>";
            case 'boolean': return "<boolean>";
            case 'object':
            case 'file':
            case 'blob':
            case 'map':
                return '<Record<string, any>>';
            default:
                return '';
        }
    }
    protected function build_api_body_data(Page_Bind_Api_Model $bind_api, $formatVariables, $inputBody, &$codeLines) {
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
                    $exp = $this->append_vue_value($expression->get_expression_code());
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
                    $expression_code = $this->append_vue_value($expression->get_expression_code());
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
    protected function build_api_form_data($formatVariables, $dataConfigs, &$codeLines=[], $dataName=''){
        if(!$dataConfigs) return null;
        foreach ($dataConfigs as $dataConfig){
            $bindVariable = $formatVariables[$dataConfig['uuid']];
            if (!$bindVariable) continue;
            $expression = $bindVariable->get_expression();
            if (!$expression) continue;
            $expression_code = $this->append_vue_value($expression->get_expression_code());
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
    protected function build_api_json_data($formatVariables, $dataConfigs, &$codeLines=[], $dataName=''){
        if(!$dataConfigs) return null;
        $codeLines[] = "const _{$dataName} = {};";
        foreach ($dataConfigs as $dataConfig){
            $bindVariable = $formatVariables[$dataConfig['uuid']];
            if (!$bindVariable) continue;
            $expression = $bindVariable->get_expression();
            if (!$expression) continue;
            $expression_code = $this->append_vue_value($expression->get_expression_code());

            $codeLines[] = "if({$expression_code} !== undefined){";
            $codeLines[] = $this->indent(1, true)."_{$dataName}[{$dataConfig['name']}] = {$expression_code}";
            $codeLines[] = '}';
            $this->add_used_variable($expression_code);
        }

        return "_{$dataName}";
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
                $expression_code = $this->append_vue_value($expression->get_expression_code());
                $args[] = $data->name.'=${'.$expression_code.'}';
                $this->add_used_variable($expression_code);
            }

            $codeLines[] = 'document.location.href=`'.$this->get_page_url($popupPage).'?'.join('&', $args).'`';
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
        $this->get_code_fragment()->add_let("{$myid}Interval", 'null', 'any');
        $this->get_code_fragment()->add_let("{$myid}RemainTime", 0, 'number');

        $interval = <<< INTERVAL
if ({$myid}Interval) return;
{$myid}RemainTime = {$duration};
event.target.setAttribute('disabled', true)
{$myid}Interval = setInterval(() => {
    {$myid}RemainTime -= {$delay};

    if({$myid}RemainTime<=0){
        clearInterval({$myid}Interval);
        {$myid}Interval = null;
        event.target.removeAttribute('disabled')
        {$myid}RemainTime = 0

{$completeCodes}
        return;
    }
    
    const remainTime = {$myid}RemainTime;
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

        $this->get_code_fragment()->add_import('axios', [], 'axios');
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
            $url = $this->replace_param($bind_api->path, $params);
            $codeLines[] = $params ?
                $this->indent(1, true).'url: `${ydecloud.apiBase}'.$url.'`,' :
                $this->indent(1, true).'url: ydecloud.apiBase+"'.$url.'"';
        }else{
            $codeLines[] = $this->indent(1, true).'url: ydecloud.apiBase+"'.$bind_api->path.'"';
        }

        $codeLines[] = " }).then((response: any) => {";
        $codeLines = array_merge($codeLines, $this->build->indent_code(1, $this->get_post_processor($bindApiActions, $io_data_fetch, $bind_api)));
        if ($onSuccess) {
            $codeLines = array_merge($codeLines, $this->build->indent_code(1, $onSuccess));
        }
        $codeLines[] = " }).catch((err: any) =>{";
        $codeLines[] = $this->indent(1, true)."alert(err)";
        $codeLines[] = " })";
        $this->get_code_fragment()->add_import('@/lib/ydecloud',[], 'ydecloud');
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
        if (!$expression) return;
        if ($expression->type == 'literal'){
            $dataName = $expression->literal;
        }elseif ($expression->type == 'connect'){
            $dataName = $this->is_scale_type($expression->data->type) ? "{$expression->data->path}" : "JSON.stringify({$expression->data->path})";
        }else{
            $dataName = $this->append_vue_value($expression->get_expression_code());
            $this->add_used_variable($dataName);
        }
        $dataName = $this->append_vue_value($dataName, $expression->type == 'connect');
        $codeLines[] = "alert({$dataName})";
    }
    protected function build_popup_page_code(Action_Model $action, &$codeLines){
        $pageId = $action->popupPageId;
        $page = $this->get_page($pageId);
        $this->get_code_fragment()->add_import('@/'.$page->get_save_path('vue'),[],$pageId);

        if (!$pageId){
            $codeLines[] = "alert('".__("You not setting popup page, you can open event panel to setting")."');";
            return;
        }

        $inputExpressions = $action->get_expression();
        $bindDatas = Page_Bind_Data_Model::get_page_bind_data($page->id, 'query');
        $queryArgs = [];
        foreach ($bindDatas as $bindData){
            $inputExpression = $inputExpressions[$bindData->uuid];
            if ($inputExpression){
                if ($inputExpression->type == 'literal'){// 字面量
                    $queryArgs[] = $bindData->name.': '.$inputExpression->literal;
                }elseif ($inputExpression->type=='connect'){ //数据赋值
                    $queryArgs[] = $bindData->name.': '. $this->append_vue_value($inputExpression->data->path, true);
                }else { //表达式赋值
                    $expression_code = $this->append_vue_value($inputExpression->get_expression_code());
                    $this->add_used_variable($expression_code);
                    $queryArgs[] = $bindData->name . ': ' . $this->append_vue_value($expression_code);
                }
            }else{
                $queryArgs[] = $bindData->name.': '. $this->data_default($bindData->get_data_model());
            }
        }

        $codeLines[] = "{$pageId}Visible.value = true";
        $codeLines[] = "{$pageId}Param.value = {".join(", ", $queryArgs)."}";

        $this->get_code_fragment()->add_ref("{$pageId}Visible", "false");
        $this->get_code_fragment()->add_ref("{$pageId}Param", "{}", '<Record<string,any>>');
    }
    protected function build_call_event_code(Action_Model $action, &$codeLines){

    }
    protected function build_emit_code(Action_Model $action, &$codeLines){
        if ($action->type != 'emit') return;
        $emit_event = $action->get_emit_event();
        if (!$emit_event) return;
        $this->get_code_fragment()->add_emit($emit_event->name);
        $this->get_code_fragment()->add_import('vue', ['defineEmits']);
        $argConfigs = json_decode(html_entity_decode($emit_event->args), true) ?: [];
        $inputExpressions = $action->get_expression();
        $args = [];
        foreach ($argConfigs as $argConfig){
            $inputExpression = $inputExpressions[$argConfig['uuid']];
            $expression_code = $inputExpression->get_expression_code();
            $this->add_used_variable($expression_code);
            $args[] = $argConfig['name'].": ".$this->append_vue_value($expression_code);
        }

        $codeLines[] = 'emit("'.$emit_event->name.'", '.join(' ', ['{',join(', ',$args), '}']).')';
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
            $path = array_reverse($path);
            $path[] = $dataConfig['name'];
            $rightValue = $this->append_vue_value($expression->get_expression_code());
//            $rightValue = $expression->get_expression_code();
            $operatior = $mutation->mutation_operator ?: ' = ';
            $operatior = preg_replace("/@/", $rightValue, $operatior);
            $codes = [$this->append_vue_value(join('.', $path), true), $operatior];
            $this->add_used_variable($rightValue);
            $codeLines[] = join('', $codes);
        }
    }
    protected function build_closepopup_code(Action_Model $action, &$codeLines){
        if ($action->type!='closepopup'){
            return;
        }
        $codeLines[] = 'close()';
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
        $check = $this->append_vue_value($check, true);
        $codeLines[] = "if({$check}){";
        $codeLines[] = $this->indent(1, true)."delete error.value?.['{$errorKey}']";
        $codeLines[] = "} else{";
        if ($subActions) $codeLines[] = $this->indent(1, true)."hasError = true";
        $codeLines[] = $this->indent(1, true)."error.value['{$errorKey}'] =  '{$msg}'";
        $codeLines[] = "}";
    }

    /**
     * @param $string string like /{foo}/{bar}
     * @param $inputArgs
     * @return string
     */
    protected function replace_param($string, $inputArgs=[]) {
        preg_match_all("/{([^}]+)}/", $string, $matches);
        foreach ($matches[1] as $name){
            $expression = $inputArgs[$name];
            if (!$expression) continue;
            $string = preg_replace("/{{$name}}/i", '\\${'.$this->append_vue_value($expression->get_expression_code()).'}', $string);
        }
        return $string;
    }
    protected function get_param_variable($formatVariables, $dataConfigs){
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
    protected function get_page_url($page) {
        return $page->id == $this->build->get_project()->home_page_id ? '/' : ($page->url?:"/page{$page->id}");
    }

    /**
     * @param $event_name
     * @return \string[][]
     */
    private function get_event_args($event_name) {
        $args = $this->get_base_event_args();
        $_ = [];

        foreach ($args as $name => $arg){
            if (preg_match("/^{$event_name}|^on{$event_name}/i", $name, $matches)) {
                foreach ($arg['args'] as $item){
                    $_[$item['name']] = $item;
                };
            }
        }
        return $_;
    }
    /**
     * 构建事件代码中使用的基础数据，比如bind的value，事件参数等
     * @param $eventModel
     * @param $html_event_name
     * @param $eventCodes
     * @param $actionCodeLines
     * @return void
     */
    protected function build_event_args_code($eventModel, $html_event_name, &$eventCodes, &$actionCodeLines){

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
            if (!$argNames) return;
            $name = '{'.join(',', $argNames).'}';
            $eventCodes[$html_event_name]['args'][$name] = ["type" => 'any', "name" => $name, "uuid" => 'emitData'];
            return;
        }

        $eventCodes[$html_event_name]['args'] = $this->get_event_args($html_event_name);
    }

    protected function output_bound_value(){
        // vue 下bound和value不输出到html结构上
    }
}
