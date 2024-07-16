<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\ValueList_View;


class Nav_View extends ValueList_View {
    use Vant_Popup,Html_Code_Helper;

    protected function build_ui_begin($iteratorName = null)
    {
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1).'<div class="van-tabs__wrap">'.PHP_EOL;
        echo $this->indent(2).'<div class="van-tabs__nav van-tabs__nav--card"';
        echo $this->wrap_output('style', $this->border_style());
        echo '>'.PHP_EOL;
    }

    protected function build_ui_end()
    {
        $space =  $this->indent();
        foreach ((array)@$this->childViews as $view){
            $view->output();
        }
        echo $this->indent(2).'</div>'.PHP_EOL;
        echo $this->indent(1).'</div>'.PHP_EOL;
        echo $space.'</div>'.PHP_EOL;
    }

    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex=null, $iteratorName='')
    {
        list('name'=>$xText, 'value'=>$xValue, 'checked'=>$checked) = $this->get_bind_name_value($outputData, $itemName);
        $staticValue = $staticData ? $staticData['value']?:$staticData['name'] : null;
        $myid = $this->myid();

        echo $this->indent(3) . "<div";
        echo $this->wrap_output(':class', $this->item_css($staticValue, $xValue));
        echo $this->wrap_output('data-root', $myid);
        echo $this->wrap_output('data-value', $xValue?:$staticValue);
        echo $this->wrap_output('data-default', $checked ? "{$checked}?$xValue:''" : ($staticData['checked']?$staticValue:NULL));
        echo ">".PHP_EOL;

        echo $this->indent(4) . '<span class="van-tab__text van-tab__text--ellipsis"';
        echo $this->wrap_output('x-text', $xText?:"'{$staticData['name']}'");
        echo "></span>".PHP_EOL;

        echo $this->indent(3) . "</div>".PHP_EOL;
    }
    public function build_style($justSelf = true)
    {
        $styleMap = parent::build_style($justSelf);
        $myid = $this->myid();

        $style = $this->data['meta']['style'];
        $checked = ($style['color'] ? "background-color:".$style['color']." !important;color:#fff;" : '').$this->border_style();

        $unchecked = [];
        if ($style['color']){
            $unchecked[] = "color:".$style['color']." !important;";
        }
        if($style['background-color']){
            $unchecked[] = "background-color:".$style['background-color']." !important;";
        }
        $unchecked[] = $this->border_style();
        $unchecked = join(';', $unchecked);

        $styleMap["[data-uiid={$myid}] .checked"] = $checked;
        $styleMap["[data-uiid={$myid}] .unchecked"] = $unchecked;
        return $styleMap;
    }

    private function border_style(){
        $style = $this->data['meta']['style'];
        $foretheme = $this->data['meta']['css']['foregroundTheme'];

        if ($style['color']) return "border-color:".$style['color'];

        if (!$foretheme || $foretheme == 'default') return '';
        return "border-color:".$this->cssTranslate['themeColor'][$foretheme];
    }
    protected function css_map()
    {
        $cssMap = parent::css_map();
        unset($cssMap['foregroundTheme']);
        unset($cssMap['backgroundTheme']);
        $arr = ['van-tabs van-tabs--card'];

        $cssMap['-'] = join(' ', $arr);
        return $cssMap;
    }
    protected function style_map($meta = null, $state = 'normal')
    {
        $map = parent::style_map($meta, $state);
        unset($map['background-color']);
        return $map;
    }

    private function item_css($staticValue, $xValue) {
        $inputDataName = $this->get_input_data_name($isArr);
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;
        $value = $staticValue ? "'{$staticValue}'" : $xValue;

        $css = ["'van-tab van-tab--card': true"];
        $style = $this->data['meta']['style'];
        $foretheme = $this->data['meta']['css']['foregroundTheme'];
        $backTheme = $this->data['meta']['css']['backgroundTheme'];

        $checkedCss = ['checked'];
        if (!$style['color']) {
            if (!$foretheme || $foretheme=='default'){
                $checkedCss[] = 'van-tab--active';
            }else{
                $checkedCss[] = 'van-text-white';
                $checkedCss[] = $this->cssTranslate['backgroundTheme'][$foretheme];
            }
        }
        $css[] = "'".join(' ', $checkedCss)."': {$inputDataNameString}=={$value}";

        $uncheckedCss = ['unchecked'];
        if (!$style['color'] && $foretheme && $foretheme!='default'){
            $uncheckedCss[] = $this->cssTranslate['foregroundTheme'][$foretheme];
        }
        if (!$style['background-color'] && $backTheme && $backTheme!='default'){
            $uncheckedCss[] = $this->cssTranslate['backgroundTheme'][$backTheme];
        }
        $css[] = "'".join(' ', $uncheckedCss)."': {$inputDataNameString}!={$value}";
        return "{".join(', ', $css)."}";
    }
}
