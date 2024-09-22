<?php
namespace app\modules\build\views\preview\layui;

use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\ValueList_View;

/**
 * <pre>
 * <span class="layui-breadcrumb" lay-separator=">">
 * <a href="">首页</a>
 * <a href="">国际新闻</a>
 * <a href="">亚太地区</a>
 * <a><cite>正文</cite></a>
 * </span>
 * </pre>
 */
class Breadcrumb_View extends ValueList_View {
    use Layui_Popup,Layui_Code_Helper;
    protected function build_valuelist($outputData, $itemName, $staticData=null, $staticDataIndex=null, $iteratorName=''){
        if ($outputData){
            list('name'=>$xText, 'value'=>$xValue, 'checked'=>$checked, 'data'=>$boundData) = $this->get_bind_name_value($outputData, $itemName);
        }else{
            $staticValue = strlen($staticData['value'])?$staticData['value']:$staticData['name'];
            $staticName = $staticData['name'];
            $xValue = "'{$staticValue}'";
        }
        $myid = $this->myid();
        $inputDataName = $this->get_input_data_name($isArr);
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;

        echo $this->indent(1) . '<a';
        $this->build_event_listen();
        echo $this->wrap_output('data-root', $myid);
        echo $this->wrap_output('href', 'javascript:void(0)');
        if ($outputData) {
            echo $this->wrap_output(':data-default', $checked ? "{$checked} ? {$xValue} : ''" : null);
            echo $this->wrap_output(':data-value', $xValue);
            echo $this->wrap_output('data-bound', $boundData);
        }else{
            echo $this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null);
            echo $this->wrap_output('data-value', $staticValue);
        }

        echo '>' . PHP_EOL;
        echo $this->indent(2) . '<template';
        echo $this->wrap_output("x-if", "{$inputDataNameString} != {$xValue}");
        echo '>' . PHP_EOL;
        echo $this->indent(2) . "<span";
        echo $this->wrap_output('class', $this->foregroundCss());
        echo $this->wrap_output('style', $this->foregroundStyle());
        echo $this->wrap_output('x-text', $xText);
        echo ">{$staticName}</span>" . PHP_EOL;
        echo $this->indent(2) . "</template>" . PHP_EOL;

        echo $this->indent(2) . '<template';
        echo $this->wrap_output("x-if", "{$inputDataNameString} == {$xValue}");
        echo '>' . PHP_EOL;
        echo $this->indent(2) . '<cite class="layui-text-muted"';
        echo $this->wrap_output('x-text', $xText);
        echo ">{$staticName}</cite>" . PHP_EOL;
        echo $this->indent(2) . "</template>" . PHP_EOL;

        echo $this->indent(2)."<span class='divider layui-text-muted layui-pl-2 layui-pr-2'> / </span>".PHP_EOL;
        echo $this->indent(1)."</a>".PHP_EOL;
    }
    protected function build_ui_begin($iteratorName=null){
        $space =  $this->indent();

        echo "{$space}<span";
        echo $this->build_main_attrs(false);
        echo ">".PHP_EOL;
    }
    protected function build_ui_end(){
        echo $this->indent()."</span>".PHP_EOL;
    }
    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $myid = $this->myid();
        $style["[data-uiid={$myid}] a:last-child .divider"] = "display:none";
        return $style;
    }
    protected function css_map()
    {
        $cssArray = parent::css_map();
        $map = parent::style_map();
        if ($map['background-color']){
            unset($cssArray['backgroundTheme']);
        }
        unset($cssArray['foregroundTheme']);
        return $cssArray;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $map = parent::style_map($meta, $state);
        if($state=='normal') unset($map['color']);
        return $map;
    }
    protected function foregroundCss(){
        $css = [];
        $cssMap = parent::css_map();
        $styleMap = parent::style_map();
        if ($cssMap['foregroundTheme'] && !$styleMap['color']){
            $css[] = $cssMap['foregroundTheme'];
        }
        return join(' ', $css)?:NULL;
    }
    protected function foregroundStyle() {
        $styleMap = parent::style_map();
        $style = [];
        if ($styleMap['color']) {
            $style[] = $styleMap['color'];
        }
        return join(";", $style)?:NULL;
    }
}
