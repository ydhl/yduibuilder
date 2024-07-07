<?php
namespace app\modules\build\views\preview\vant;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Rangeinput_View extends Preview_View {
    use Vant_Popup,Html_Code_Helper,Alpine{
        Alpine::build_code as alpineBuildCode;
    }
    protected function style_map($meta = null, $state = 'normal')
    {
        $styles = parent::style_map($meta, $state);
        unset($styles['background-color']);
        unset($styles['height']);
        return $styles;
    }

    protected function slider_style()
    {
        $styles = parent::style_map();
        $_ = [];
        if ($styles['height']) $_[] = $styles['height'];
        return join(';', $_);
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
    public function build_ui()
    {
        $space =  $this->indent(2);
        echo "{$space}";
        echo "<div";
        $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(3).'<div class="van-field__control van-field__control--custom">'.PHP_EOL;
        echo $this->indent(4).'<div class="van-slider" @click="'.$this->myid().'_click($el)"';
        echo $this->wrap_output('style', $this->slider_style());
        echo '>'.PHP_EOL;
        // 背景条
        echo $this->indent(5).'<div';
        echo $this->wrap_output(':style', $this->bg_style());
        echo $this->wrap_output('class', $this->bg_class());
        echo '>'.PHP_EOL;
        // 滑块
        echo $this->indent(6).'<div @mousedown="'.$this->myid().'_mousedown($el)"';
        echo $this->wrap_output('class', 'van-slider__button-wrapper van-slider__button-wrapper--right');
        echo '>'.PHP_EOL;

        echo $this->indent(7).'<div';
        echo $this->wrap_output('style', $this->handle_style());
        echo $this->wrap_output('class', $this->handle_class());
        echo $this->wrap_output('x-text', $this->myid().'_value');
        echo '>';
        echo "</div>";


        echo $this->indent(6).'</div>';
        echo $this->indent(5).'</div>';
        echo $this->indent(4).'</div>';
        echo $this->indent(3).'</div>';


        echo "{$space}</div>".PHP_EOL;
    }
    public function build_code():Base_Code_Fragment
    {
        $this->alpineBuildCode();
        $fragment = $this->get_code_fragment();
        $min = @$this->data['meta']['custom']['min']??1;
        $max = @$this->data['meta']['custom']['max']??100;
        $step = @$this->data['meta']['custom']['step']??1;

        $this->get_input_data($inputDataName);
        if (!$inputDataName){
            $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid().'_value: '.(floatval($this->data['meta']['value']) ?: 50).',');
        }

        $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid().'_moving: false,');

        $click[] = $this->myid().'_click(el){';
        $click[] = $this->indent(1, true).'const { x, width } = el.getBoundingClientRect(), clientX = event.clientX;';
        $click[] = $this->indent(1, true).'this.'.$this->myid().'_value = Math.ceil(Math.max(clientX - x, 0) / width * 100);';
        $click[] = '},';
        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $click);

        $mousedown[] = $this->myid().'_mousedown(el){';
        $mousedown[] = $this->indent(1, true).'this.'.$this->myid().'_moving = true;';
        $mousedown[] = $this->indent(1, true).'document.addEventListener("mousemove", (event) => this.'.$this->myid().'_mousemove.call(this, event));';
        $mousedown[] = $this->indent(1, true).'document.addEventListener("mouseup", (event) => this.'.$this->myid().'_mouseup.call(this, event));';
        $mousedown[] = '},';
        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $mousedown);

        $mousemove[] = $this->myid().'_mousemove(event){';
        $mousemove[] = $this->indent(1, true).'if(!this.'.$this->myid().'_moving) return;';
        $mousemove[] = $this->indent(1, true).'const { x, width } = document.querySelector("[data-uiid='.$this->myid().']").getBoundingClientRect(), clientX = event.clientX;';
        $mousemove[] = $this->indent(1, true).'const value = Math.ceil(Math.max(clientX - x, 0) / width * 100);';
        $mousemove[] = $this->indent(1, true).'this.'.$this->myid().'_value = value > '.$max.' ? '.$max.' : (value < '.$min.' ? '.$min.' : value);';
        $mousemove[] = '},';
        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $mousemove);

        $mouseup[] = $this->myid().'_mouseup(event){';
        $mouseup[] = $this->indent(1, true).'this.'.$this->myid().'_moving = false;';
        $mouseup[] = $this->indent(1, true).'document.removeEventListener("mousemove", (event) => this.'.$this->myid().'_mousemove.call(this, event));';
        $mouseup[] = $this->indent(1, true).'document.removeEventListener("mouseup", (event) => this.'.$this->myid().'_mouseup.call(this, event));';
        $mouseup[] =  '},';
        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $mouseup);
        return $fragment;
    }

    // 滑块
    private function handle_style(){
        $style = [];
        $meta = $this->data['meta'];
        if ($meta['style']['color']){
            $style[] = 'background-color:'.($meta['style']['color']).';important;';
        }
        return join(';', $style);
    }
    private function handle_class(){
        $class = ['van-slider__button van-text-center van-user-select-none'];
        $meta = $this->data['meta'];
        if ($meta['css']['foregroundTheme'] && $meta['css']['foregroundTheme'] != 'default'){
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
        if ($meta['css']['backgroundTheme'] && $meta['css']['backgroundTheme'] != 'default'){
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
