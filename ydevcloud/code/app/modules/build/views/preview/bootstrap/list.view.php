<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\ValueList_View;
use function yangzie\__;


class List_View extends ValueList_View {
    use Bootstrap_Popup,Html_Code_Helper;

    protected function build_valuelist($outputData, $itemName, $staticData=null)
    {
        list('name'=>$xText, 'value'=>$xValue) = $this->get_bind_name_value($outputData, $itemName);
        // 动态数据
        if ($outputData){
            echo $this->indent(1) . "<a href='javascript:;'";
            echo $this->wrap_output(':class', $this->item_theme($xValue));
            echo $this->wrap_output(':style', $this->item_style($xValue));
            echo $this->wrap_output('x-text', $xText);
            echo $this->wrap_output(':data-value', $xValue);
            echo "></a>".PHP_EOL;
            return;
        }
        // 静态数据
        echo $this->indent(1) . "<a href='javascript:;'"
            .$this->wrap_output(':class', $this->item_theme(null, $staticData['value']?:$staticData['name']))
            .$this->wrap_output(':style', $this->item_style(null, $staticData['value']?:$staticData['name']))
            .$this->wrap_output('data-value', $staticData['value']?:$staticData['name'])
            .">".PHP_EOL;
        echo $this->indent(2) . ($staticData['name']).PHP_EOL;
        echo $this->indent(1) . "</a>".PHP_EOL;
    }
    protected function build_ui_begin()
    {
        $space =  $this->indent();
        $inputDataName = $this->get_input_data_name();
        echo "{$space}<div";
        $this->build_main_attrs();
        echo $this->wrap_output('x-input', $inputDataName);
        echo ">".PHP_EOL;
    }
    protected function build_ui_end()
    {
        $space =  $this->indent();
        echo "{$space}</div>".PHP_EOL;
    }
    protected function css_map()
    {
        $arrMap = parent::css_map();
        unset($arrMap['backgroundTheme'], $arrMap['foregroundTheme']);
        $arr = [];
        if (@$this->data['meta']['custom']['horizontal']){
            $arr[] = 'list-group-horizontal';
        }
        if (@$this->data['meta']['custom']['flush']){
            $arr[] = 'list-group-flush';
        }
        $arr[] = 'list-group';
        $arrMap['-'] = join(' ', $arr);
        return $arrMap;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta, $state);
        unset($styleArray['color'], $styleArray['background-color']);
        return $styleArray;
    }

    private function item_theme($valueName, $staticValue='') {
        $inputDataName = $this->get_input_data_name($isArr);
        $styleMap = parent::style_map();
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;
        $backgroundTheme = $this->data['meta']['css']['backgroundTheme'];
        $cssMap = parent::css_map();
        $value = $valueName?:"'{$staticValue}'";

        $css = ["'list-group-item list-group-item-action': true"];
        $css[] = "'active': {$inputDataNameString}=={$value}";
        if ($backgroundTheme && !$styleMap['background-color'] && $backgroundTheme != 'default'){
            $css[] = "'{$this->cssTranslate['backgroundTheme'][$backgroundTheme]} {$this->cssTranslate['borderColorClass'][$backgroundTheme]}':{$inputDataNameString}=={$value}";
            $css[] = "'list-group-item-{$backgroundTheme}': true";
        }
        if (@$cssMap['foregroundTheme']) {
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
