<?php
namespace app\modules\build\views\preview\layui;

use app\modules\build\views\preview\bootstrap\List_View as Bootstrap_List_View;
use app\modules\build\views\preview\Preview_View;

class List_View extends Bootstrap_List_View {
    use Layui_Popup,Layui_Code_Helper;
    protected function item_theme($valueName, $staticValue=''){
        $inputDataName = $this->get_input_data_name($isArr);
        $styleMap = Preview_View::style_map();
        $cssMap = Preview_View::css_map();
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;
        $backgroundTheme = $this->data['meta']['css']['backgroundTheme'];
        $value = $valueName?:"'{$staticValue}'";

        $css = ["'layui-list-group-item layui-list-group-item-action': true"];
        $css[] = "'active': {$inputDataNameString}=={$value}";
        if ($backgroundTheme && !$styleMap['background-color'] && $backgroundTheme != 'default'){
            $css[] = "'{$this->cssTranslate['backgroundTheme'][$backgroundTheme]} {$this->cssTranslate['borderColorClass'][$backgroundTheme]}':{$inputDataNameString}=={$value}";
            $css[] = "'layui-list-group-item-{$backgroundTheme}': true";
        }
        if (!$styleMap['color'] && @$cssMap['foregroundTheme']) {
            $css[] = "'{$cssMap['foregroundTheme']}': true";
        }
        return "{".join(', ', $css)."}";
    }
    protected function css_map()
    {
        $arrMap = Preview_View::css_map();
        //前进背景色都放到item上
        unset($arrMap['backgroundTheme'],$arrMap['foregroundTheme']);
        $arr = ['layui-list-group'];
        $arrMap['-'] = join(' ', $arr);
        return $arrMap;
    }
}
