<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

/**
 * <pre>
 * <div class="weui-progress">
 *  <div class="weui-progress__bar">
 *      <div class="weui-progress__inner-bar js_progress" style="width: 80%;"></div>
 *  </div>
 * </div>
 * </pre>
 */
class Progress_View extends Preview_View {
    use Weui_Popup,Html_Code_Helper;

    public function build_ui()
    {
        $space =  $this->indent();
        $outputDatas = $this->get_output_datas($outputDataName);

        $value = $this->data['meta']['value']?:50;
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1).'<div';
        echo $this->wrap_output('class', join(' ',$this->bg_css()));
        echo ">".PHP_EOL;

        echo $this->indent(2).'<div';
        echo $this->wrap_output('class', join(' ',$this->front_css()));
        if ($outputDatas['VALUE']){
            $outputDataName = $this->get_output_data_name('VALUE', $outputDatas['VALUE'], $outputDataName['VALUE']);
            echo $this->wrap_output(':style', "`width:\${{$outputDataName}}%`");
            echo $this->wrap_output('x-text', "`\${{$outputDataName}}%`");
        }
        echo ">".PHP_EOL;

        if (@$this->data['meta']['custom']['label']){
            echo $this->indent(3)."{$value}%".PHP_EOL;
        }
        echo $this->indent(2)."</div>".PHP_EOL;
        echo $this->indent(1)."</div>".PHP_EOL;
        echo "{$space}</div>".PHP_EOL;
    }
    protected function css_map()
    {
        $cssMap = parent::css_map();
        unset($cssMap['foregroundTheme']);
        unset($cssMap['backgroundTheme']);
        return $cssMap;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta, $state);
        unset($styleArray['color']);
        unset($styleArray['height']);
        unset($styleArray['background-color']);
        return $styleArray;
    }
    protected function output_as_prop($outputAs, $outputData){
        if (!strcasecmp($outputAs,'value')){
            return ;// value 不再主元素上输出，在progressbar上输出，所以这里返回null
        }
        return parent::output_as_prop($outputAs, $outputData);
    }

    public function build_style($justSelf = true) {
        $style = parent::build_style($justSelf);
        $myid = $this->myid();

        $bgstyle = $this->bg_style();
        $frontstyle = $this->front_style();
        if ($bgstyle) $style["[data-uiid={$myid}] .weui-progress__bar"] = join(';', $bgstyle);
        if ($frontstyle) $style["[data-uiid={$myid}] .weui-progress__inner-bar"] = join(';', $frontstyle);
        return $style;
    }
    private function bg_css() {
        $css = ['weui-progress__bar'];
        if (@$this->data['meta']['css']['backgroundTheme']){
            $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['backgroundTheme']];
        }
        return $css;
    }
    private function bg_style() {
        $styleArray = parent::style_map();
        $style = [];
        if($styleArray['height']) $style[] = $styleArray['height'];
        if($styleArray['background-color']) $style[] = $styleArray['background-color'].' !important';
        return  $style ?: NULL;
    }
    private function front_css() {
        $styleMap = parent::style_map();
        $css = ['weui-progress__inner-bar'];
        if (!$styleMap['color'] && @$this->data['meta']['css']['foregroundTheme']){
            $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['foregroundTheme']];
        }
        if (@$this->data['meta']['custom']['striped']){
            $css[] = "weui-progress-bar-striped";
        }
        if (@$this->data['meta']['custom']['animatedStrip']){
            $css[] = "weui-progress-bar-animated";
        }
        return $css;
    }
    private function front_style() {
        $outputDatas = $this->get_output_datas($outputDataName);
        $value = $this->data['meta']['value']?:50;
        if ($outputDatas['VALUE']){
            $style = [];// build_ui中绑定
        }else{
            $style = ["width: {$value}%"];
        }
        $styleMap = parent::style_map();
        if ($styleMap['color']){
            $style[] = "background-color:".$this->data['meta']['style']['color']." !important";
        }
        return $style;
    }

}
