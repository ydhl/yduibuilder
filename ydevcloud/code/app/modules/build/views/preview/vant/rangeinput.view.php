<?php
namespace app\modules\build\views\preview\vant;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Rangeinput_View extends Preview_View {
    use Vant_Popup,Html_Code_Helper;
    protected function style_map($meta = null, $state = 'normal')
    {
        $styles = parent::style_map($meta, $state);
        unset($styles['background-color']);
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
    public function build_ui()
    {
        $space =  $this->indent(2);
        echo "{$space}";
        echo "<div";
        $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(3).'<div class="van-field__control van-field__control--custom">'.PHP_EOL;
        echo $this->indent(4).'<div class="van-slider"';
        echo '>'.PHP_EOL;
        // 背景条
        echo $this->indent(5).'<div';
        echo $this->wrap_output('style', $this->bg_style());
        echo $this->wrap_output('class', $this->bg_class());
        echo '>'.PHP_EOL;
        // 滑块
        echo $this->indent(6).'<div';
        echo $this->wrap_output('class', 'van-slider__button-wrapper van-slider__button-wrapper--right');
        echo '>'.PHP_EOL;

        echo $this->indent(7).'<div';
        echo $this->wrap_output('style', $this->handle_style());
        echo $this->wrap_output('class', $this->handle_class());
        echo '>';

        echo $this->data['meta']['value'] ?: 50;
        echo "</div>";


        echo $this->indent(6).'</div>';
        echo $this->indent(5).'</div>';
        echo $this->indent(4).'</div>';
        echo $this->indent(3).'</div>';


        echo "{$space}</div>".PHP_EOL;
    }
    public function build_code():Base_Code_Fragment
    {
        parent::build_code();

        return $this->get_code_fragment();
    }

    // 滑块
    private function handle_style(){
        $style = [];
        $meta = $this->data['meta'];
        $style[] = 'left: '.($meta['value']?:50).'%;important;';
        if ($meta['style']['color']){
            $style[] = 'background-color:'.($meta['style']['color']).';important;';
        }
        return join(';', $style);
    }

    private function handle_class(){
        $class = ['van-slider__button van-text-center'];
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
        $style[] = 'width: '.($meta['value']?:50).'%;important;';
        if ($meta['style']['background-color']){
            $style[] = 'background-color:'.($meta['style']['background-color']).';important;';
        }
        return join(';', $style);
    }
}
