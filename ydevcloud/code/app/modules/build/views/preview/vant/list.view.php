<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\ValueList_View;


class List_View extends ValueList_View {
    use Vant_Popup,Html_Code_Helper;
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
        echo "{$space}</div>".PHP_EOL;
    }
    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex=null)
    {
        $myid = $this->myid();
        list('name'=>$xText, 'value'=>$xValue, 'checked'=>$checked) = $this->get_bind_name_value($outputData, $itemName);
        if (!$outputData){
            $staticValue = $staticData['value']?:$staticData['name'];
            $xText = "'{$staticData['name']}'";
            $xValue = "'{$staticValue}'";
        }

        $inputDataName = $this->get_input_data_name($isArr);
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;

        echo $this->indent(1) . '<div';
        echo $this->wrap_output('class', $this->list_theme());
        echo $this->wrap_output(':data-value', $xValue);
        if ($outputData){
            echo $this->wrap_output(':data-default', $checked ? "{$checked} ? {$xValue} : ''" : null);
        }else{
            echo $this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null);
        }
        echo $this->wrap_output('data-root', $myid);
        echo ">".PHP_EOL;

        echo $this->indent(2) . "<div";
        echo $this->wrap_output('class', $this->value_theme());
        echo $this->wrap_output('x-text', $xText);
        echo "></div>".PHP_EOL;

        echo $this->indent(2);
        echo '<i';
        echo $this->wrap_output("x-show",$checked ?: "{$inputDataNameString}=={$xValue}");
        echo $this->wrap_output("class",$this->icon_theme());
        echo '></i>'.PHP_EOL;

        echo $this->indent(1) . "</div>".PHP_EOL;
    }
    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $myid = $this->myid();
        $style["[data-uiid={$myid}] .van-badge__wrapper"] = 'font-size: 1.5rem;';
        $style["[data-uiid={$myid}] .van-cell__value, [data-uiid={$myid}] .van-cell,[data-uiid={$myid}] .van-badge__wrapper"] = $this->list_style();
        return $style;
    }

    protected function css_map()
    {
        $arrMap = parent::css_map();
        unset($arrMap['backgroundTheme'], $arrMap['foregroundTheme']);
        $arr = [];
        $arr[] = 'van-list';
        $arrMap['-'] = join(' ', $arr);
        return $arrMap;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta, $state);
        unset($styleArray['color'], $styleArray['background-color']);
        return $styleArray;
    }
    private function theme() {
        $cssMap = parent::css_map();
        $styleMap = parent::style_map();
        $css = [];
        if (@$cssMap['backgroundTheme'] && !$styleMap['background-color']) $css[] = $cssMap['backgroundTheme'];
        if (@$cssMap['foregroundTheme'] && !$styleMap['color']) $css[] = $cssMap['foregroundTheme'];
        return $css;
    }
    private function list_theme() {
        $css = $this->theme();
        $css[] = 'van-cell van-cell--clickable van-align-items-center';
        return join(' ', $css);
    }
    private function value_theme() {
        $css = $this->theme();
        $css[] = 'van-cell__value van-cell__value--alone';
        return join(' ', $css);
    }
    private function icon_theme() {
        $css = $this->theme();
        $css[] = 'van-badge__wrapper van-icon van-icon-success van-text-success';
        return join(' ', $css);
    }
    private function list_style() {
        $styleMap = parent::style_map();
        $style = [];
        if (@$styleMap['color']) {
            $style[] = $styleMap['color']. ' !important';
        }
        if (@$styleMap['background-color']) {
            $style[] = $styleMap['background-color'];
        }
        return join(';', $style);
    }
}
