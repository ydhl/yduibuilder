<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\ValueList_View;


class Breadcrumb_View extends ValueList_View {
    use Bootstrap_Popup,Html_Code_Helper;

    protected function build_valuelist($outputData, $itemName, $staticData=null, $staticDataIndex=null, $iteratorName=''){
        if ($outputData){
            list('name'=>$xText, 'value'=>$xValue, 'checked'=>$checked) = $this->get_bind_name_value($outputData, $itemName);
        }else{
            $staticValue = $staticData['value']?:$staticData['name'];
            $staticName = $staticData['name'];
            $xValue = "'{$staticValue}'";
        }
        $myid = $this->myid();
        $inputDataName = $this->get_input_data_name($isArr);
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;

        echo $this->indent(1) . '<li';
        echo $this->wrap_output(':class', "{'breadcrumb-item': true, 'active':{$inputDataNameString}=={$xValue}}");
        echo '>' . PHP_EOL;
        echo $this->indent(2) . '<template';
        echo $this->wrap_output("x-if", "{$inputDataNameString} != {$xValue}");
        echo '>' . PHP_EOL;
        echo $this->indent(2) . "<a href='javascript:;'";
        echo $this->wrap_output('class', $this->foregroundCss());
        echo $this->wrap_output('style', $this->foregroundStyle());
        echo $this->wrap_output('data-root', $myid);
        if ($outputData) {
            echo $this->wrap_output(':data-default', $checked ? "{$checked} ? {$xValue} : ''" : null);
            echo $this->wrap_output(':data-value', $xValue);
            echo $this->wrap_output('x-text', $xText);
        }else{
            echo $this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null);
            echo $this->wrap_output('data-value', $staticValue);
        }

        echo ">{$staticName}</a>" . PHP_EOL;
        echo $this->indent(2) . "</template>" . PHP_EOL;

        echo $this->indent(2) . '<template';
        echo $this->wrap_output("x-if", "{$inputDataNameString} == {$xValue}");
        echo '>' . PHP_EOL;
        echo $this->indent(2) . "<span";
        echo $this->wrap_output('data-root', $myid);
        if ($outputData) {
            echo $this->wrap_output(':data-value', $xValue);
            echo $this->wrap_output(':data-default', $checked ? "{$checked} ? {$xValue} : ''" : null);
            echo $this->wrap_output('x-text', $xText);
        }else{
            echo $this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null);
            echo $this->wrap_output('data-value', $staticValue);
        }
        echo ">{$staticName}</span>" . PHP_EOL;
        echo $this->indent(2) . "</template>" . PHP_EOL;

        echo $this->indent(1)."</li>".PHP_EOL;
    }
    protected function build_ui_begin($iteratorName=null){
        $space =  $this->indent();

        echo "{$space}<ol";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;
    }
    protected function build_ui_end(){
        echo $this->indent()."</ol>".PHP_EOL;
    }
    protected function css_map()
    {
        $cssArray = parent::css_map();
        $map = parent::style_map();
        $cssArray['breadcrumb'] = 'breadcrumb';
        if ($map['background-color']){
            unset($cssArray['backgroundTheme']);
        }
        unset($cssArray['foregroundTheme']);
        return $cssArray;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $map = parent::style_map($meta, $state);
        unset($map['color']);
        return $map;
    }
    private function foregroundCss(){
        $css = [];
        $cssMap = parent::css_map();
        $styleMap = parent::style_map();
        if ($cssMap['foregroundTheme'] && !$styleMap['color']){
            $css[] = $cssMap['foregroundTheme'];
        }
        return join(' ', $css)?:NULL;
    }
    private function foregroundStyle() {
        $styleMap = parent::style_map();
        $style = [];
        if ($styleMap['color']) {
            $style[] = $styleMap['color'];
        }
        return join(";", $style)?:NULL;
    }
}
