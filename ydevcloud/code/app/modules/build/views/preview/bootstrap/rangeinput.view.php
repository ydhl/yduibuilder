<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Rangeinput_View extends Preview_View {
    use Bootstrap_Popup,Html_Code_Helper;


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
        $_ = ['form-control-range input'];
        if ($this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing'] != 'normal') {
            $_[] = 'form-control-range-' . $this->data['meta']['css']['formSizing'];
        }

        $color = $this->data['meta']['style']['color'];
        if ($this->data['meta']['css']['foregroundTheme'] && $this->data['meta']['css']['foregroundTheme']!='default' && !$color) {
            $_[] = 'range-' . $this->data['meta']['css']['foregroundTheme'];
        }

        $map['-'] = join(' ', $_);
        return $map;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $map = parent::style_map($meta);

        $background = [];
        $backgroundSize = ['50%', '100%'];
        $color = $meta['style']['color'];
        if ($color) {
            $map['border'] = "border:1px solid {$color} !important";
            $background[] = "-webkit-linear-gradient(top, {$color}, {$color})";
        }

        if ($background) {
            $map['background-image'] = 'background-image:'.join(',', $background) . ' !important';
        }

        $min = $this->data['meta']['custom']['min'] ?? 1;
        $default = $this->data['meta']['value'] ?: 50;
        $max = $this->data['meta']['custom']['max'] ?: 100;
        $backgroundSize[0] = (($default - $min) / ($max - $min) * 100) . '%';
        $map['background-size'] = 'background-size:'.join(' ', $backgroundSize);
        return $map;
    }

    public function build_ui()
    {
        $min = @$this->data['meta']['custom']['min']??1;
        $max = @$this->data['meta']['custom']['max']??100;
        $step = @$this->data['meta']['custom']['step']??1;
        $inputData = $this->get_input_data($inputDataName);
        $space =  $this->indent();
        echo $space."<input type='range' ";
        echo $this->build_main_attrs();
        echo $this->build_form_attrs();
        // 如果没有数据绑定的话，定义一个临时数据
        if (!$inputDataName){
            $inputDataName = $this->myid().'_temp';
            echo $this->wrap_output('x-model.fill', $inputDataName);
        }
        echo $this->wrap_output('@change', $this->myid().'_change');
        echo $this->wrap_output('min', $min);
        echo $this->wrap_output('max', $max);
        echo $this->wrap_output('step', $step);

        if($inputData['defaultValue']){
            echo $this->wrap_output(':style', "{ 'background-size': (({$inputDataName} - $min) / ($max - $min) * 100) + '%' }");
        }
        echo ">".PHP_EOL;
    }
    public function build_code():Base_Code_Fragment
    {
        parent::build_code();
        $codeLines = [];
        $codeFragment = $this->get_code_Fragment();
        $this->get_input_data($inputDataName);
        if (!$inputDataName){
            $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid().'_temp: "'.$this->data['meta']['value'].'",');
        }
        $codeLines[] = $this->myid().'_change (event) {';
        $codeLines[] = $this->indent(1, true)."const page = this";
        $codeLines[] = $this->indent(1, true)."const minValue = event.target.min || 1;";
        $codeLines[] = $this->indent(1, true)."const value = event.target.value;";
        $codeLines[] = $this->indent(1, true)."const maxValue = event.target.max || 100;";
        $codeLines[] = $this->indent(1, true)."const percent = ((value - minValue) / (maxValue - minValue) * 100) + '%';";
        $codeLines[] = $this->indent(1, true)."event.target.style.backgroundSize = percent + ' 100%';";
        $codeLines[] = "},";

        $codeFragment->add_code(Html_Code_Fragment::SECTION_EVENT, $codeLines);
        return $codeFragment;
    }
}
