<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\bootstrap\Progress_View as Bootstrap_Progress_View;

class Progress_View extends Bootstrap_Progress_View {
    protected $cssPrefix = 'weui-';

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

    protected function bg_css() {
        $css = ['weui-progress__bar'];
        if (@$this->data['meta']['css']['backgroundTheme']){
            $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['backgroundTheme']];
        }
        return $css;
    }

    protected function bg_style() {
        $styleArray = parent::style_map();
        $style = [];
        if($styleArray['height']) $style[] = $styleArray['height'];
        if($styleArray['background-color']) $style[] = $styleArray['background-color'].' !important';
        return  $style;
    }

    protected function front_css() {
        $css = ['weui-progress__inner-bar'];
        if (@$this->data['meta']['css']['foregroundTheme']){
            $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['foregroundTheme']];
        }
        if (@$this->data['meta']['custom']['striped']){
            $css[] = $this->cssPrefix."progress-bar-striped";
        }
        if (@$this->data['meta']['custom']['animatedStrip']){
            $css[] = $this->cssPrefix."progress-bar-animated";
        }
        return $css;
    }

    protected function front_style() {
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
    public function build_ui()
    {
        $space =  $this->indent();
        $value = $this->data['meta']['value']?:50;
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1).'<div';
        echo $this->wrap_output('class', join(' ',$this->bg_css()));
        echo $this->wrap_output('style', join(';', $this->bg_style()));
        echo ">".PHP_EOL;
        echo $this->indent(2).'<div';
        echo $this->wrap_output('class', join(' ',$this->front_css()));
        echo $this->wrap_output('style', join(';', $this->front_style()));
        echo ">".PHP_EOL;

        if (@$this->data['meta']['custom']['label']){
            echo $this->indent(3)."{$value}%".PHP_EOL;
        }
        echo $this->indent(2)."</div>".PHP_EOL;
        echo $this->indent(1)."</div>".PHP_EOL;
        echo "{$space}</div>".PHP_EOL;
    }
}
