<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Valuable_View;


class Rangeinput_View extends Preview_View implements Valuable_View {
    use Bootstrap_Popup,Html_Code_Helper;

    public function build_ui()
    {
        $min = @$this->data['meta']['custom']['min']??1;
        $max = @$this->data['meta']['custom']['max']??100;
        $step = @$this->data['meta']['custom']['step']??1;
        $inputDataName = $this->get_input_data_name($inputIsArr, $inputData);
        $outputDatas = $this->get_output_datas($outputDataName);

        $space =  $this->indent();
        echo $space."<input type='range'";
        echo $this->output_main_attrs();
        echo PHP_EOL.$space;
        echo $this->output_form_attrs();
        echo $this->wrap_output('min', $min);
        echo $this->wrap_output('max', $max);
        echo $this->wrap_output('step', $step);
        echo PHP_EOL.$space;


        if ($inputData){
            echo $this->wrap_output(':value', "alpinejs_get_value(\$el, '{$inputDataName}')");
        }elseif ($outputDataName['VALUE']){
            $valueDataName = $this->get_output_data_name('VALUE', $outputDatas['VALUE'], $outputDataName['VALUE']);
            echo $this->wrap_output(':value', $valueDataName);
        }else{
            echo $this->wrap_output('value', @$this->data['meta']['value']?:0);
        }

        if ($outputDataName['VALUE'] && $inputDataName){
            echo $this->wrap_output('x-init', "alpinejs_set_value(\$el, '{$inputDataName}',"
                .($outputDatas['VALUE']['type']=='array' ? $outputDataName['VALUE'] : '['.$outputDataName['VALUE'].']').", true)");
        }

        echo $this->wrap_output(':style', "`background-size: \${(alpinejs_get_value(\$el, '{$inputDataName}') - $min) / ($max - $min) * 100 || 0}%`");
        echo ">".PHP_EOL;
    }
    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);

        $height = floatval($this->data['meta']['style']['height']);
        $heightUnit = preg_replace("/\d+/", '', $this->data['meta']['style']['height']);
        $thumb = [];
        $color = $this->data['meta']['style']['color'];
        if ($color){
            $thumb[] = 'background-color:'.$color;
        }
        if($height){
            $thumb[] = 'width:'.($height*1.5).$heightUnit;
            $thumb[] = 'height:'.($height*1.5).$heightUnit;
        }
        if ($thumb){
            $style['[data-uiid='.$this->myid().']::-webkit-slider-thumb'] = join(';', $thumb);
        }
        return $style;
    }
    protected function css_map()
    {
        $map = parent::css_map();
        $styleMap = parent::style_map();
        unset($map['foregroundTheme']);
        $formSizing = $this->data['meta']['css']['formSizing'];
        $foregroundTheme = $this->data['meta']['css']['foregroundTheme'];
        if ($styleMap['background-color']) unset($map['backgroundTheme']);

        $_ = ['form-control-range input'];
        if ($formSizing && $formSizing != 'normal') {
            $_[] = 'form-control-range-' . $this->data['meta']['css']['formSizing'];
        }

        $color = $this->data['meta']['style']['color'];
        if ($foregroundTheme && $foregroundTheme!='default' && !$color) {
            $_[] = 'range-' . $this->data['meta']['css']['foregroundTheme'];
        }

        $map['-'] = join(' ', $_);
        return $map;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $meta = $meta??$this->data['meta'];
        $map = parent::style_map($meta, $state);
        $background = [];
        $color = $meta['style']['color'];
        unset($map['color']);

        if ($color) {
            $map['border'] = "border:1px solid {$color}";
            $background[] = "-webkit-linear-gradient(top, {$color}, {$color})";
        }

        if ($background) {
            $map['background-image'] = 'background-image:'.join(',', $background);
        }
        $map['padding'] = 'padding: 0px'; // 去掉内间距，内间距会导致自定义背景计算宽度时不正确
        return $map;
    }

    protected function output_as_prop($outputAs, $outputData)
    {
        if(strtolower($outputAs) == 'value') return null; // value的绑定在上面单独处理
        return parent::output_as_prop($outputAs, $outputData);
    }
}
