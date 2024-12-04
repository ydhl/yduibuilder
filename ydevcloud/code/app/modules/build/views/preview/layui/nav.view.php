<?php
namespace app\modules\build\views\preview\layui;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\ValueList_View;

class Nav_View extends ValueList_View {
    use Layui_Popup,Layui_Code_Helper,Alpine{
        Alpine::build_code as alpineBuildCode;
    }

    protected function build_ui_begin($iteratorName=null)
    {
        $inputDataName = $this->get_input_data_name();
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->wrap_output('class', $this->main_css());
        echo $this->wrap_output('lay-filter', $this->myid());
        echo ">".PHP_EOL;

        echo $this->indent(1) . "<ul";
        $this->output_main_attrs();
        echo $this->wrap_output('x-input', $inputDataName);
        echo ">".PHP_EOL;
    }
    protected function build_ui_end()
    {
        foreach ((array)@$this->childViews as $view){
            $view->output();
        }
        $space =  $this->indent();
        echo $this->indent(1)."</ul>".PHP_EOL;
        echo "{$space}</div>".PHP_EOL;
    }

    protected function build_valuelist($outputData, $itemName, $staticData=null, $staticDataIndex=null, $iteratorName=''){
        list('name'=>$xText, 'value'=>$xValue, 'checked'=>$checked, 'data'=>$boundData) = $this->get_bind_name_value($outputData, $itemName);
        $myid = $this->myid();
        $staticValue = $staticData ? (strlen($staticData['value'])?$staticData['value']:$staticData['name']) : null;
        echo $this->indent(1) . '<li';
        echo $this->wrap_output(':class',$this->item_css($staticValue, $xValue));
        echo $this->wrap_output(':style',$this->item_style($staticValue, $xValue));
        echo $this->wrap_output('x-text', $xText);
        echo $this->wrap_output('data-root', $myid);
        if ($outputData){
            echo $this->wrap_output(':data-value', $xValue);
            echo $this->wrap_output(':lay-id', $xValue);
            echo $this->wrap_output('data-bound', $boundData);
            echo $this->wrap_output(':data-default', $checked ? "{$checked} ? {$xValue} : ''" : null);
        }else{
            echo $this->wrap_output('data-value', $staticValue);
            echo $this->wrap_output('lay-id', $staticValue);
            echo $this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null);
        }

        echo $this->indent(1) . '>'.$staticData['name'].'</li>'.PHP_EOL;
    }

    public function build_code():Base_Code_Fragment
    {
        $this->alpineBuildCode();
        $myid = $this->myId();
        $fragment = $this->get_code_Fragment();

        $inputDataName = $this->get_input_data_name($inputIsArr, $inputDataConfig);
        $outputDataNames = [];
        $outputDatas = $this->get_output_datas($outputDataNames);

        $code = <<<LAYPAGE
this.\$nextTick(() => {
    layui_nav_init("{$myid}");
})
LAYPAGE;
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $code);
        return $fragment;
    }
    protected function main_css()
    {
        $arr = [];
        $arr[] = 'layui-tab';
        if (@$this->data['meta']['custom']['type'] == 'pill'){
            $arr[] =  'layui-tab-brief';
        }

        return join(' ', $arr);
    }
    protected function css_map()
    {
        $cssMap = parent::css_map();
        $styleMap = parent::style_map();
        unset($cssMap['foregroundTheme']);
        if ($styleMap['background-color']) unset($cssMap['backgroundTheme']);

        $arr = [];
        $arr[] = 'layui-tab-title';

        $cssMap['-'] = join(' ', $arr);
        return $cssMap;
    }
    private function item_css($staticValue=null, $valueName=null) {
        $inputDataName = $this->get_input_data_name($isArr);
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;

        $css = ["'layui-nav-item': true"];
        $theme = $this->data['meta']['css']['foregroundTheme'];
        if ($theme && $theme !== 'default' && !$this->data['meta']['style']['color']){
            $checkedCss = $this->cssTranslate['backgroundTheme'][$theme];
            $uncheckedCss = $this->cssTranslate['foregroundTheme'][$theme];
        }

        $value = $staticValue ? "'{$staticValue}'" : $valueName;

        $css[] = "'{$checkedCss} layui-this': {$inputDataNameString}=={$value}";
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
