<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\ValueList_View;

class Nav_View extends ValueList_View {
    use Weui_Popup,Html_Code_Helper;

    protected function build_ui_begin($iteratorName = null)
    {
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;
    }
    protected function build_ui_end()
    {
        $space =  $this->indent();
        foreach ((array)@$this->childViews as $view){
            $view->output();
        }
        echo "{$space}</div>".PHP_EOL;
    }
    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex = null, $iteratorName='')
    {
        list('name'=>$xText, 'value'=>$xValue, 'checked'=>$checked) = $this->get_bind_name_value($outputData, $itemName);
        $myid = $this->myid();
        $staticValue = $staticData ? $staticData['value']?:$staticData['name'] : null;


        echo $this->indent(1) . "<div"
            .$this->wrap_output(':class', $this->item_class($staticValue,$xValue))
            .$this->wrap_output(':style', $this->item_style($staticValue,$xValue))
            .$this->wrap_output('data-root', $myid);
        if ($outputData){
            echo $this->wrap_output(':data-value', $xValue);
            echo $this->wrap_output('x-text', $xText);
            echo $this->wrap_output(':data-default', $checked ? "{$checked} ? {$xValue} : ''" : null);
        }else{
            echo $this->wrap_output('data-value', addslashes($staticValue));
            echo $this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null);
        }
        echo ">{$staticData['name']}</div>".PHP_EOL;
    }

    protected function item_class($staticValue=null, $valueName=null) {
        $inputDataName = $this->get_input_data_name($isArr);
        $value = $staticValue ? "'{$staticValue}'" : $valueName;
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;

        $css = ["'weui-navbar__item':true","'weui-bar__item_on':{$inputDataNameString} === {$value}"];
        $styleMap =  Preview_View::style_map();
        if (!$styleMap['color']) {
            $theme = $this->data['meta']['css']['foregroundTheme'];
            if (!$theme || $theme === 'default') {
                $css[] = "'text-dark':{$inputDataNameString} !== {$value}";
                $css[] = "'bg-light text-dark':{$inputDataNameString} === {$value}";
            }else{
                $css[] = "'{$this->cssTranslate['foregroundTheme'][$theme]}':{$inputDataNameString} !== {$value}";
                $css[] = "'{$this->cssTranslate['backgroundTheme'][$theme]} text-white':{$inputDataNameString} === {$value}";
            }
        }

        return "{".join(',', $css)."}";
    }

    protected function item_style($staticValue=null, $valueName=null) {
        $inputDataName = $this->get_input_data_name($isArr);
        $value = $staticValue ? "'{$staticValue}'" : $valueName;
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;


        $styleMap =  Preview_View::style_map();
        $style = [];
        if (@$styleMap['color']) {
            $style[] = "{$inputDataNameString} != {$value}?'{$styleMap['color']}':'color:#fff;background-color:{$this->data['meta']['style']['color']}'";
        }
        return join('', $style);
    }
    protected function css_map()
    {
        $cssMap = Preview_View::css_map();
        unset($cssMap['foregroundTheme']);
        $cssMap['-'] = 'weui-navbar';

        return $cssMap;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = Preview_View::style_map($meta);
        unset($styleArray['color']);
        return $styleArray;
    }

}
