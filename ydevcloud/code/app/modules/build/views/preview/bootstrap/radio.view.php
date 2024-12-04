<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\ValueList_View;
use function yangzie\__;


class Radio_View extends ValueList_View {
    use Bootstrap_Popup,Html_Code_Helper;

    protected $type = 'radio';
    protected function build_valuelist($outputData, $itemName, $staticData=null, $staticDataIndex=null, $iteratorName=''){
        list('name'=>$name, 'value'=>$value, 'checked'=>$checked, 'data'=>$boundData) = $this->get_bind_name_value($outputData, $itemName);
        $staticValue = strlen($staticData['value'])?$staticData['value']:$staticData['name'];
        $myid = $this->myid();
        $inputDataName = $this->get_input_data_name($inputIsArr);

        echo $this->indent(1)."<div";
        echo $this->wrap_output('class', "form-check d-flex me-3 align-items-center");
        echo $this->wrap_output('x-id', "['{$myid}-item']");

        if ($value){
            echo $this->wrap_output(':data-value', $value);
            echo $this->wrap_output('data-bound', $boundData);
        }else{
            echo $this->wrap_output('data-value',$staticValue );
        }
        echo ">".PHP_EOL;
        echo $this->indent(2);
        echo '<input';
        echo $this->wrap_output('type', $this->type);
        echo $this->wrap_output('class', "form-check-input mt-0");
        echo $this->wrap_output(':id', "\$id('{$myid}-item')");
        echo $this->output_form_attrs();
        $eventHandlers = $this->get_event_listen_props();
        if($eventHandlers['@blur']) echo $this->wrap_output("@blur", $eventHandlers['@blur']);
        if($eventHandlers['@focus']) echo $this->wrap_output("@focus", $eventHandlers['@focus']);

        if ($value){
            echo $this->wrap_output(':value', $value);
            echo $this->wrap_output(':data-default', $checked ? "{$checked} ? {$value} : ''" : null);
            echo $this->wrap_output(':checked', "alpinejs_in_array(\$el, '{$inputDataName}', {$value})");
        }else{
            echo $this->wrap_output('value', $staticValue);
            echo $this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null);
            echo $this->wrap_output(':checked', "alpinejs_in_array(\$el, '{$inputDataName}', '{$staticValue}')");
        }
        echo ">".PHP_EOL;

        echo $this->indent(2).'<label';
        echo $this->wrap_output('@click.stop', null, true);
        if ($name){
            echo $this->wrap_output('x-text', $name);
        }
        echo $this->wrap_output('class', "form-check-label");
        echo $this->wrap_output(':for', "\$id('{$myid}-item')");
        echo ">{$staticData['name']}</label>" . PHP_EOL;

        echo $this->indent(1) . "</div>".PHP_EOL;
    }
    protected function build_ui_begin($iteratorName=null)
    {
        $space =  $this->indent();
        echo "{$space}<div";
        $this->output_main_attrs();
        $this->output_init_input();
        echo ">".PHP_EOL;
    }
    protected function build_ui_end()
    {
        $space =  $this->indent();
        echo "{$space}</div>".PHP_EOL;
    }
    protected function css_map() {
        $arr = parent::css_map();
        $style = parent::style_map();
        if ($this->data['meta']['custom']['inline']) {
            $arr[] = 'h-100 d-flex align-items-center';
        } else {
            $arr[] = 'h-auto';
        }
        if ($style['background-color']) unset($arr['backgroundTheme']);
        if ($style['color']) unset($arr['foregroundTheme']);
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing']!='normal'){
            $arr[] = 'form-control-'.$this->data['meta']['css']['formSizing'];
        }
        return $arr;
    }
}
