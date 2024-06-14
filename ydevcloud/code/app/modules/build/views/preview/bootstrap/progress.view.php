<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Progress_View extends Preview_View {
    use Bootstrap_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $cssMap = parent::css_map();
        unset($cssMap['foregroundTheme']);
        $cssMap['-'] = 'progress';
        return $cssMap;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta);
        unset($styleArray['color']);
        return $styleArray;
    }

    private function bar_css() {
        $css = ['progress-bar'];
        if (@$this->data['meta']['css']['foregroundTheme']){
            $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['foregroundTheme']];
        }
        if (@$this->data['meta']['custom']['striped']){
            $css[] = "progress-bar-striped";
        }
        if (@$this->data['meta']['custom']['animatedStrip']){
            $css[] = "progress-bar-animated";
        }
        return join(' ', $css);
    }

    private function bar_style() {
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
        return join(';', $style);
    }

    public function build_ui()
    {
        $outputDatas = $this->get_output_datas($outputDataName);

        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1).'<div';
        echo $this->wrap_output('role', 'progressbar');
        echo $this->wrap_output('class', $this->bar_css());
        echo $this->wrap_output('style', $this->bar_style());

        $value = $this->data['meta']['value']?:50;
        if ($outputDatas['VALUE']){
            $outputDataName = $this->get_output_data_name('VALUE', $outputDatas['VALUE'], $outputDataName['VALUE']);
            echo $this->wrap_output(':aria-valuenow', $outputDataName);
            echo $this->wrap_output(':style', "'width:' + {$outputDataName} + '%'");
        }else{
            echo $this->wrap_output('aria-valuenow', $value);
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

    protected function output_as_prop($outputAs, $outputData){
        if (!strcasecmp($outputAs,'value')){
            return ;// value 不再主元素上输出，在progressbar上输出，所以这里返回null
        }
        return parent::output_as_prop($outputAs, $outputData);
    }
}
