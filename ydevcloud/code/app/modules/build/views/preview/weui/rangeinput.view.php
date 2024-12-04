<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Alpinejs_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Valuable_View;

/**
 * <pre>
 * <div class="weui-slider-box">
 *  <div class="weui-slider">
 *      <div id="sliderInner" class="weui-slider__inner">
 *          <div id="sliderTrack" style="width: 50%;" class="weui-slider__track"></div>
 *          <div role="slider" aria-label="可调滑块" id="sliderHandler" style="left: 50%;" class="weui-slider__handler weui-wa-hotarea" wah-hotarea="touchstart"></div>
 *      </div>
 *  </div>
 *  <div id="sliderValue" role="alert" class="weui-slider-box__value">50</div>
 * </div>
 * <pre>
 */
class Rangeinput_View extends Preview_View implements Valuable_View {
    use Weui_Popup, Html_Code_Helper, Alpine{
        Alpine::build_code as alpineBuildCode;
    }

    public function build_ui()
    {
        $bgTheme = $this->bgTheme();
        $bgStyle = $this->bgStyle();
        $trackStyle = $this->trackStyle();
        $trackTheme = $this->trackTheme();
        $handleStyle = $this->handleStyle();
        $handleTheme = $this->handleTheme();
        $myId = $this->myid();
        ob_start();
        $this->output_main_attrs();
        $attr = ob_get_clean();
        $inputDataName = $this->get_input_data_name();

        $html = <<<HTML
<div class="weui-slider-box" {$attr}>
    <div class="weui-slider">
        <div class="{$bgTheme}" style="{$bgStyle}">
            <div class="{$trackTheme}" :style="{$trackStyle}"></div>
            <div class="{$handleTheme}" :style="{$handleStyle}"></div>
        </div>
    </div>
    <div x-text="{$inputDataName}" class="weui-slider-box__value"></div>
</div>
HTML;
        echo $this->build->output_code($html, $this->build->get_indent());
    }
    public function build_code(): Base_Code_Fragment
    {
        $fragment = $this->alpineBuildCode();
        $myid = $this->myid();
        $min = @$this->data['meta']['custom']['min']??1;
        $max = @$this->data['meta']['custom']['max']??100;
        $step = @$this->data['meta']['custom']['step']??1;

        $slider = <<<SLIDER
this.\$nextTick(() => {
    weui.slider("[data-uiid='${myid}']", {
        step: {$step},
        defaultValue: this.{$myid}_value,
        onChange: (percent) => {
            this.{$myid}_value = ~~percent;
        }
    });
})
SLIDER;

        $fragment->add_code(Alpinejs_Code_Fragment::SECTION_INIT, $slider);
        return $fragment;
    }

    protected function css_map()
    {
        $map = parent::css_map();
        unset($map['backgroundTheme'],$map['foregroundTheme']);
        return $map;
    }
    protected function style_map($meta = NULL, $state = 'normal')
    {
        $map = parent::style_map();
        unset($map['color'],$map['background-color'],$map['height']);
        return $map;
    }

    protected function default_value() {
        return $this->data['meta']['value']??50;
    }

    //  滑块主题样式
    protected function handleStyle() {
        $inputDataName = $this->get_input_data_name();
        return "`left:\${{$inputDataName}}%`";
    }
    protected function handleTheme() {
        $css = ['weui-slider__handler'];
        return join(" ", $css);
    }

    //  前景色样式，已滑动距离
    protected function trackTheme() {
        $css = ['weui-slider__track'];
        $foregroundTheme = $this->data['meta']['css']['foregroundTheme'];
        if (!$this->data['meta']['style']['color'] && @$foregroundTheme && $foregroundTheme != 'default'){
            $css[] = $this->cssTranslate['backgroundTheme'][$foregroundTheme];
        }
        return join(" ", $css);
    }
    protected function trackStyle() {
        $style = [];
        $inputDataName = $this->get_input_data_name();
        $style[] = "width: \${{$inputDataName}}%";
        if (@$this->data['meta']['style']['color']){
            $style[] = "background-color: ".$this->data['meta']['style']['color'].' !important';
        }
        return "`".join(';', $style)."`";
    }

    //  背景色样式，底色

    protected function bgTheme() {
        $css = ['weui-slider__inner'];
        $cssMap = parent::css_map();
        if ($cssMap['backgroundTheme']){
            $css[] = $cssMap['backgroundTheme'];
        }
        return join(" ", $css);
    }
    protected function bgStyle() {
        $style = [];
        $styleMap = parent::style_map();
        if (@$styleMap['background-color']){
            $style[] = $styleMap['background-color']." !important";
        }
        if (@$styleMap['height']){
            $style[] = $styleMap['height'];
        }
        return join(";", $style);
    }
}
