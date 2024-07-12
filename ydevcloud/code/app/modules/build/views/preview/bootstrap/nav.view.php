<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\ValueList_View;


class Nav_View extends ValueList_View {
    use Bootstrap_Popup,Html_Code_Helper;

    protected function build_valuelist($outputData, $itemName, $staticData=null, $staticDataIndex=null){
        list('name'=>$xText, 'value'=>$xValue, 'checked'=>$checked) = $this->get_bind_name_value($outputData, $itemName);
        $myid = $this->myid();
        $staticValue = $staticData ? $staticData['value']?:$staticData['name'] : null;
        echo $this->indent(1) . '<div class="nav-item">'.PHP_EOL;
        echo $this->indent(2) . "<a";
        echo $this->wrap_output(':class',$this->item_css($staticValue, $xValue));
        echo $this->wrap_output(':style',$this->item_style($staticValue, $xValue));
        echo $this->wrap_output('href', 'javascript:;');
        echo $this->wrap_output('x-text', $xText);
        echo $this->wrap_output('data-root', $myid);
        if ($outputData){
            echo $this->wrap_output(':data-value', $xValue);
            echo $this->wrap_output(':data-default', $checked ? "{$checked} ? {$xValue} : ''" : null);
        }else{
            echo $this->wrap_output('data-value', $staticValue);
            echo $this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null);
        }


        echo '>'.$staticData['name'].'</a>'.PHP_EOL;
        echo $this->indent(1) . "</div>".PHP_EOL;
    }
    protected function css_map()
        {
            $cssMap = parent::css_map();
            $styleMap = parent::style_map();
            unset($cssMap['foregroundTheme']);
            if ($styleMap['background-color']) unset($cssMap['backgroundTheme']);

            $arr = [];
            $arr[] = 'nav';
            if (@$this->data['meta']['custom']['type'] == 'tab'){
                $arr[] =  'nav-tabs';
            }
            if (@$this->data['meta']['custom']['type'] == 'pill'){
                $arr[] =  'nav-pills';
            }
            if (@$this->data['meta']['custom']['justified']){
                $arr[] =  'nav-justified';
            }
            if (@$this->data['meta']['custom']['filled']){
                $arr[] =  'nav-fill';
            }

            $parentUI = $this->get_parent_UI();
            $parentIsCard = in_array(strtolower($parentUI['type']), ['card']);

            if ($parentIsCard && @$this->data['meta']['custom']['type'] == 'tab'){
                $arr[] = 'card-header-tabs';
            }
            if ($parentIsCard && @$this->data['meta']['custom']['type'] == 'pill'){
                $arr[] = 'card-header-pills';
            }

            $cssMap['-'] = join(' ', $arr);
            return $cssMap;
        }
    protected function build_ui_begin($iteratorName=null)
    {
        $inputDataName = $this->get_input_data_name();
        $space =  $this->indent();
        echo "{$space}<div ";
        $this->build_main_attrs();
        echo $this->wrap_output('x-input', $inputDataName);
        echo ">".PHP_EOL;
    }
    protected function build_ui_end()
    {
        foreach ((array)@$this->childViews as $view){
            $view->output();
        }
        $space =  $this->indent();
        echo "{$space}</div>".PHP_EOL;
    }

    private function item_css($staticValue=null, $valueName=null) {
        $inputDataName = $this->get_input_data_name($isArr);
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;

        $css = ["nav-link"];
        $theme = $this->data['meta']['css']['foregroundTheme'];
        if ($theme && $theme !== 'default' && !$this->data['meta']['style']['color']){
            $checkedCss = $this->cssTranslate['backgroundTheme'][$theme].' text-white';
            $uncheckedCss = $this->cssTranslate['foregroundTheme'][$theme];
        }

        $value = $staticValue ? "'{$staticValue}'" : $valueName;
        $css = ["'".join(' ', $css)."': true"];

        $css[] = "'{$checkedCss} active': {$inputDataNameString}=={$value}";
        if ($uncheckedCss) $css[] = "'{$uncheckedCss}': {$inputDataNameString}!={$value}";

        return "{".join(', ', $css)."}";
    }
    private function item_style($staticValue=null, $valueName=null) {
        $inputDataName = $this->get_input_data_name($isArr);
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;

        $color = $this->data['meta']['style']['color'];
        if (!$color){
            return null;
        }
        $value = $staticValue?"'{$staticValue}'":$valueName;

        $checkedStyle = "background-color:{$color} !important;color:#fff;";
        $uncheckedStyle = "color:{$color} !important;";
        return "{$inputDataNameString}=={$value} ? '$checkedStyle' : '{$uncheckedStyle}'";
    }
}
