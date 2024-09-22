<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Progress_View extends Preview_View {
    use Bootstrap_Popup,Html_Code_Helper;

    public function build_ui()
    {
        $outputDatas = $this->get_output_datas($outputDataNames);

        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1).'<div';
        echo $this->wrap_output('role', 'progressbar');
        echo $this->wrap_output('class', join(' ',$this->bar_css()));

        $value = $this->data['meta']['value']?:50;
        if ($outputDatas['VALUE']){
            $valueDataName = $this->get_output_data_name('VALUE', $outputDatas['VALUE'], $outputDataNames['VALUE']);
            echo $this->wrap_output(':aria-valuenow', $valueDataName);
            echo $this->wrap_output(':style', "`width:\${{$valueDataName}}%`");
        }else{
            echo $this->wrap_output('aria-valuenow', $value);
        }
        if (@$this->data['meta']['custom']['label']) {
            $textDataName = $this->get_output_data_name('TEXT', $outputDatas['TEXT'], $outputDataNames['TEXT']);
            if ($textDataName && $valueDataName){
                echo $this->wrap_output('x-text', "`\${{$textDataName}}:\${{$valueDataName}}%`");
            }else if ($textDataName){
                echo $this->wrap_output('x-text', $textDataName);
            }else if ($valueDataName){
                echo $this->wrap_output('x-text', "`\${{$valueDataName}}%`");
            }
        }
        echo $this->wrap_output('aria-valuemin', @$this->data['meta']['custom']['min'] ?? 0);
        echo $this->wrap_output('aria-valuemax', @$this->data['meta']['custom']['max'] ?? 100);
        echo ">".PHP_EOL;

        if (@$this->data['meta']['custom']['label']){
            echo $this->indent(2)."{$value}%".PHP_EOL;
        }
        echo $this->indent(1)."</div>".PHP_EOL;
        echo "{$space}</div>".PHP_EOL;
    }

    public function build_style($justSelf = true) {
        $style = parent::build_style($justSelf);
        $myid = $this->myid();
        $outputDatas = $this->get_output_datas($outputDataName);

        $value = $this->data['meta']['value']?:50;
        if ($outputDatas['VALUE']){
            $barStyle = [];// build_ui中绑定
        }else{
            $barStyle = ["width: {$value}%"];
        }
        $styleMap = parent::style_map();
        $cssMap = parent::css_map();
        $color = '';
        if ($styleMap['color']){
            $color = $this->data['meta']['style']['color'];
        }else if($cssMap['foregroundTheme']) {
            $color = $this->cssTranslate['themeColor'][$this->data['meta']['css']['foregroundTheme']];
        }
        if ($color) $barStyle[] = "background-color:{$color} !important";
        $style["[data-uiid={$myid}] [role=progressbar]"] = join(';', $barStyle);
        return $style;
    }
    protected function css_map()
    {
        $cssMap = parent::css_map();
        $styleArray = parent::style_map();
        unset($cssMap['foregroundTheme']);
        if($styleArray['background-color']) unset($cssMap['backgroundTheme']);
        $cssMap['-'] = 'progress';
        return $cssMap;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta, $state);
        unset($styleArray['color']);
        return $styleArray;
    }
    protected function output_as_prop($outputAs, $outputData){
        if (!strcasecmp($outputAs,'value') || !strcasecmp($outputAs,'text') ){
            return ;// value 不再主元素上输出，在progressbar上输出，所以这里返回null
        }
        return parent::output_as_prop($outputAs, $outputData);
    }

    private function bar_css() {
        $css = ['progress-bar'];
        $styleMap = parent::style_map();
        if (@$this->data['meta']['css']['foregroundTheme'] && !$styleMap['color']){
            $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['foregroundTheme']];
        }
        if (@$this->data['meta']['custom']['striped']){
            $css[] = "progress-bar-striped";
        }
        if (@$this->data['meta']['custom']['animatedStrip']){
            $css[] = "progress-bar-animated";
        }
        return $css;
    }
}
