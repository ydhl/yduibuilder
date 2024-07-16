<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Hr_View extends Preview_View {
    use Weui_Popup,Html_Code_Helper;
    public function build_ui()
    {

        $outputData = $this->get_output_datas($outputDataName);
        $htmlOutputData = $outputData['HTML'];
        $textOutputData = $outputData['TEXT'];

        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        // 左侧线段
        echo $this->indent(1)."<div"
            .$this->wrap_output("class", $this->lineCss())
            ."></div>".PHP_EOL;

        // 文字
        if ($htmlOutputData || $textOutputData){
            $outputData = $textOutputData ?: $htmlOutputData;
            $outputDataName = $outputDataName['TEXT'] ?: $outputDataName['HTML'];
            $outputAs = $textOutputData ? 'TEXT': 'HTML';
            $outputDataName = $this->get_output_data_name($outputAs, $outputData, $outputDataName);
            echo $this->indent(1)."<div"
                .$this->wrap_output("class", $this->textCss());
            echo $this->wrap_output(parent::output_as_prop($outputAs, $outputData), $outputDataName);
            echo "></div>".PHP_EOL;
        }else{
            if ($this->data['meta']['value']){
                echo $this->indent(1)."<div"
                    .$this->wrap_output("class", $this->textCss()).">";
                echo $this->data['meta']['value'];
                echo "</div>".PHP_EOL;
            }
        }

        // 右侧线段
        echo $this->indent(1)."<div"
            .$this->wrap_output("class", $this->lineCss())
            ."></div>".PHP_EOL;
        echo "{$space}</div>".PHP_EOL;
    }
    public function build_style($justSelf = true)
    {
        $map = parent::build_style($justSelf);
        $map['[data-uiid='.$this->myId().'] .line'] = $this->lineStyle();
        $map['[data-uiid='.$this->myId().'] .text'] = $this->textStyle();
        return $map;
    }

    protected function css_map()
    {
        $map = parent::css_map();
        unset($map['backgroundTheme']);
        unset($map['foregroundTheme']);
        $map['-'] = 'd-flex justify-content-center align-items-center';
        return $map;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styles = parent::style_map($meta, $state);
        unset($styles['height']);
        unset($styles['background-color']);
        unset($styles['color']);

        if (!$styles['width']) {
            $styles['width'] = 'width:100%';
        }
        return $styles;
    }
    protected function output_as_prop($outputAs, $outputData){
        if (in_array(strtolower($outputAs), ['html', 'text'])){
            return ;// 不在主元素上输出，在子元素上输出，所以这里返回null
        }
        return parent::output_as_prop($outputAs, $outputData);
    }

    private function lineStyle() {
        $styles = parent::style_map();
        $newStyle = [];
        $height = $styles['height'] ?: '1px';
        $style = $this->data['meta']['custom']['style'] ?: 'solid';
        if ($style == 'double' && intval($height) < 3){
            $height = '3px';
        }
        $newStyle[] = "border-top-width:{$height}";
        $newStyle[] = "border-top-style:{$style}";

        if (!@$styles['background-color'] && !@$this->data['meta']['css']['backgroundTheme']) {
            $newStyle[] = 'background-color:rgba(0,0,0,.1)';
        }else if ($styles['background-color']) {
            $newStyle[] = 'border-top-color: '.$this->data['meta']['style']['background-color'];
        }
        return join(';', $newStyle);
    }
    private function lineCss() {
        $css = ['flex-grow-1 line'];
        if (!$this->data['meta']['style']['background-color'] && $this->data['meta']['css']['backgroundTheme']){
            $css[] = $this->cssTranslate['borderColorClass'][$this->data['meta']['css']['backgroundTheme']];
        }
        return join(' ', $css);
    }
    private function textStyle() {
        $styles = parent::style_map();
        $newStyle = [];

        if ($styles['color']) {
            $newStyle[] = $styles['color'].' !important';
        }
        return join(";", $newStyle)?:NULL;
    }
    private function textCss() {
        $map = parent::css_map();
        $css =[ 'flex-shrink-0 pl-2 pr-2 text'];

        if (!$this->data['meta']['style']['color']){
            $css[] = $map['foregroundTheme'];
        }

        return join(' ',$css);
    }
}
