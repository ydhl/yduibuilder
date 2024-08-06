<?php
namespace app\modules\build\views\preview\vant;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Valuable_View;


class Rangeinput_View extends Preview_View implements Valuable_View {
    use Vant_Popup,Html_Code_Helper,Alpine{
        Alpine::build_code as alpineBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}";
        echo "<div";
        $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1).'<div class="van-field__control van-field__control--custom">'.PHP_EOL;
        echo $this->indent(2).'<div class="van-slider" @click="'.$this->myid().'_click($el)"';
        echo $this->wrap_output('style', $this->slider_style());
        echo '>'.PHP_EOL;
        // 背景条
        echo $this->indent(3).'<div';
        echo $this->wrap_output(':style', $this->bg_style());
        echo $this->wrap_output('class', $this->bg_class());
        echo '>'.PHP_EOL;
        // 滑块
        echo $this->indent(4).'<div @mousedown="'.$this->myid().'_mousedown($el)"';
        echo $this->wrap_output('class', 'van-slider__button-wrapper van-slider__button-wrapper--right');
        echo '>'.PHP_EOL;

        echo $this->indent(5).'<div';
        echo $this->wrap_output('style', $this->handle_style());
        echo $this->wrap_output('class', $this->handle_class());
        echo $this->wrap_output('x-text', $this->get_input_data_name());
        echo "></div>".PHP_EOL;


        echo $this->indent(4).'</div>'.PHP_EOL;
        echo $this->indent(3).'</div>'.PHP_EOL;
        echo $this->indent(2).'</div>'.PHP_EOL;
        echo $this->indent(1).'</div>'.PHP_EOL;


        echo "{$space}</div>".PHP_EOL;
    }
    public function build_code():Base_Code_Fragment
    {
        $this->alpineBuildCode();
        $fragment = $this->get_code_fragment();
        $min = @$this->data['meta']['custom']['min']??0;
        $max = @$this->data['meta']['custom']['max']??100;
        $step = @$this->data['meta']['custom']['step']??1;
        $myid = $this->myid();

        $inputDataName = $this->get_input_data_name($isArr);
        $suffix = '';
        if($isArr){
            $suffix = '[-1]';
        }
        $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid().'_moving'.$suffix.': false,');

        $click = <<<CLICK
{$myid}_click(el){
    const { x, width } = el.getBoundingClientRect(), clientX = event.clientX;
    const value = Math.ceil(Math.max(clientX - x, 0) / width * 100)
    this.alpinejs_set_value(el, '{$inputDataName}', value > {$max} ? {$max} : (value < {$min} ? {$min} : value));
},
CLICK;

        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $this->build->indent_code(0,$click));

        $mousedown = <<< MOUSEDOWN
{$myid}_mousedown(event){
    this.alpinejs_set_value(this.\$el, '{$myid}_moving{$suffix}', true);
    document.addEventListener("mousemove", (event) => this.{$myid}_mousemove.call(this, event));
    document.addEventListener("mouseup", (event) => this.{$myid}_mouseup.call(this, event));
},
MOUSEDOWN;

        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $this->build->indent_code(0,$mousedown));

        $mousemove = <<< MOUSEMOVE
{$myid}_mousemove(event){
    if(!this.alpinejs_get_value(this.\$el, '{$myid}_moving{$suffix}')) return;
    const { x, width } = document.querySelector("[data-uiid={$myid}]").getBoundingClientRect(), clientX = event.clientX;
    const value = Math.ceil(Math.max(clientX - x, 0) / width * 100);
    this.alpinejs_set_value(this.\$el, '{$inputDataName}', value > {$max} ? {$max} : (value < {$min} ? {$min} : value));
},
MOUSEMOVE;

        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $this->build->indent_code(0,$mousemove));

        $mouseup = <<< MOUSEUP
{$myid}_mouseup(event){
    this.alpinejs_set_value(this.\$el, '{$myid}_moving{$suffix}', false);
    document.removeEventListener("mousemove", (event) => this.{$myid}_mousemove.call(this, event));
    document.removeEventListener("mouseup", (event) => this.{$myid}_mouseup.call(this, event));
},
MOUSEUP;

        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $this->build->indent_code(0,$mouseup));
        return $fragment;
    }
    protected function style_map($meta = null, $state = 'normal')
    {
        $styles = parent::style_map($meta, $state);
        unset($styles['background-color']);
        unset($styles['height']);
        return $styles;
    }

    protected function css_map() {
        $map = parent::css_map();
        unset($map['backgroundTheme']);
        $map['-'] = 'van-h-auto';
        if (@$this->data['meta']['form']['state'] == 'hidden'){
            $map['-'] .= ' van-d-none';
        }
        return $map;
    }

    private function slider_style()
    {
        $styles = parent::style_map();
        $_ = [];
        if ($styles['height']) $_[] = $styles['height'];
        return join(';', $_);
    }

    // 滑块
    private function handle_style(){
        $style = [];
        $meta = $this->data['meta'];
        if ($meta['style']['color']){
            $style[] = 'background-color:'.($meta['style']['color']).';important;';
            $style[] = 'color:#efefef;important;';
        }
        return join(';', $style);
    }
    private function handle_class(){
        $class = ['van-slider__button van-text-center van-user-select-none'];
        $meta = $this->data['meta'];
        if (!$meta['style']['color'] && $meta['css']['foregroundTheme'] && $meta['css']['foregroundTheme'] != 'default'){
            $class[] = $this->cssTranslate['backgroundTheme'][$meta['css']['foregroundTheme']];
            $class[] = $this->cssTranslate['foregroundTheme']['light'];
        }
        if (in_array($meta['form']['state'], ['disabled', 'readonly'])){
            $class[] = 'van-disabled';
        }
        return join(' ', $class);
    }

    // 背景色样式，底色
    private function bg_class(){
        $class = ['van-slider__bar'];
        $meta = $this->data['meta'];
        if (!$meta['style']['background-color'] && $meta['css']['backgroundTheme'] && $meta['css']['backgroundTheme'] != 'default'){
            $class[] = $this->cssTranslate['backgroundTheme'][$meta['css']['backgroundTheme']];
        }
        return join(' ', $class);
    }
    private function bg_style(){
        $style = [];
        $meta = $this->data['meta'];
        $style[] = "'width: '+".$this->myid()."_value+'%;'";
        if ($meta['style']['background-color']){
            $style[] = "'background-color:".($meta['style']['background-color'])."'";
        }
        return join('+', $style);
    }
}
