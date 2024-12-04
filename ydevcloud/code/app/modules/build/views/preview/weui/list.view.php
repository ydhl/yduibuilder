<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\ValueList_View;

class List_View extends ValueList_View {
    use Weui_Popup,Html_Code_Helper;
    protected function build_ui_begin($iteratorName = null)
    {
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->output_main_attrs();
        echo ">".PHP_EOL;
    }

    protected function build_ui_end()
    {
        $space =  $this->indent();
        echo "{$space}</div>".PHP_EOL;
    }

    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex = null, $iteratorName='')
    {
        $space =  $this->indent();
        $myid = $this->myid();
        $inputDataName = $this->get_input_data_name($inputIsArr);
        list('name'=>$xText, 'value'=>$xValue, 'checked'=>$checked, 'data'=>$boundData) = $this->get_bind_name_value($outputData, $itemName);

        if (!$outputData){
            $staticValue = strlen($staticData['value'])?$staticData['value']:$staticData['name'];
            $xValue = "'{$staticValue}'";
            $xText = "'{$staticData['name']}'";
        }

        echo $this->indent(1) . "<div";
        echo $this->wrap_output(':class', $this->item_theme($inputDataName, $xValue));
        echo $this->wrap_output(':style', $this->item_style($inputDataName,$xValue));
        echo $this->wrap_output(':data-value', $xValue)
            .$this->wrap_output('data-bound', $boundData)
            .$this->wrap_output('data-root', $myid);
        if ($checked){
            echo $this->wrap_output(':data-default', $checked ? "{$checked} ? {$xValue} : ''" : null);
        }else{
            echo $this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null);
        }
        echo ">".PHP_EOL;

        echo $this->indent(2) . '<span class="weui-cell__bd"';
        echo $this->wrap_output('x-text', $xText);
        echo "></span>".PHP_EOL;
        echo $this->indent(2) . '<template x-if="'.$inputDataName.'=='.$xValue.'" ><span class="weui-cell__ft"><i class="weui-icon-success"></i></span></template>'.PHP_EOL;

        echo $this->indent(1) . "</div>".PHP_EOL;
    }

    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $myid = $this->myid();
        $style["[data-uiid={$myid}] .weui-cell"] = 'font-size:inherit;line-height:1.6';
        return $style;
    }

    private function item_theme($valueName, $staticValue='') {
        $inputDataName = $this->get_input_data_name($isArr);
        $styleMap = Preview_View::style_map();
        $cssMap = Preview_View::css_map();
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;
        $value = $valueName?:"'{$staticValue}'";

        $css = ["'weui-cell weui-cell_active': true"];
        $backgroundTheme = $this->data['meta']['css']['backgroundTheme'];
        if (!$styleMap['background-color'] && @$backgroundTheme && $backgroundTheme != 'default'){
            $css[] = "'{$this->cssTranslate['backgroundTheme'][$backgroundTheme]} {$this->cssTranslate['borderColorClass'][$backgroundTheme]}':{$inputDataNameString}=={$value}";
        }

        if (!$styleMap['color'] && @$cssMap['foregroundTheme']) {
            $css[] = "'{$cssMap['foregroundTheme']}': true";
        }
        return "{".join(', ', $css)."}";
    }
    private function item_style($valueName, $staticValue='') {
        $inputDataName = $this->get_input_data_name($isArr);
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;
        $styleMap = parent::style_map();
        $backgroundColor = $this->data['meta']['style']['background-color'];
        $value = $valueName?:"'{$staticValue}'";
        $fixedStyle = [];
        $varStyle = '';
        if (@$styleMap['color']) {
            $fixedStyle[] = $styleMap['color'];
        }
        if (@$styleMap['background-color']) {
            $rgba = $this->get_Rgba_Info($backgroundColor);
            $varStyle = "{$inputDataNameString}=={$value} ? 'background-color:rgba(".$rgba['r'].",".$rgba['g'].",".$rgba['b'].",".($rgba['a'] * 0.75).") !important':'".$styleMap['background-color']."'";
            $fixedStyle[] = "border-color:{$backgroundColor} !important";
        }
        if (!$fixedStyle && !$varStyle) return null;
        if (!$fixedStyle) return $varStyle;
        if (!$varStyle) return "'".join(';',$fixedStyle)."'";
        return "'" . join(';',$fixedStyle) . ";' + ({$varStyle})";
    }

}
