<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Rangeinput_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup, Html_Code_Helper;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }
    protected function value() {
        return $this->data['meta']['value']??50;
    }

    //  滑块主题样式

    protected function handleStyle() {
        $style = [];
        $style[] = "left: ".$this->value()."%";
        if (@$this->data['meta']['custom']['color']){
            $style[] = "background-color: ".$this->data['meta']['custom']['color'];
        }
        return join(";", $style);
    }
    protected function handleTheme() {
        $css = ['weui-slider__handler weui-wa-hotarea'];
        if (@$this->data['meta']['custom']['theme'] && $this->data['meta']['custom']['theme'] != 'default'){
            $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['custom']['theme']];
        }
        return join(" ", $css);
    }

    //  前景色样式，已滑动距离

    protected function trackTheme() {
        $css = ['weui-slider__track'];
        if (@$this->data['meta']['custom']['theme'] && $this->data['meta']['custom']['theme'] != 'default'){
            $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['custom']['theme']];
        }
        return join(" ", $css);
    }
    protected function trackStyle() {
        $style = [];
        $style[] = "width: ".$this->value()."%";
        if (@$this->data['meta']['custom']['color']){
            $style[] = "background-color: ".$this->data['meta']['custom']['color'];
        }
        return join(";", $style);
    }

    //  背景色样式，底色

    protected function bgTheme() {
        $css = ['weui-slider__inner'];
        return join(" ", $css);
    }
    protected function bgStyle() {
        $style = [];
        if (@$this->data['meta']['custom']['backgroundColor']){
            $style[] = "background-color: ".$this->data['meta']['custom']['backgroundColor'];
        }
        return join(";", $style);
    }

    public function build_ui()
    {
        $space =  $this->indent(4);
        echo "{$space}";
        echo "<div class='weui-slider-box w-100'>\r\n";
        echo $this->indent(5);
        echo "<div class='weui-slider pl-0'>\r\n";
        echo $this->indent(6);
        echo "<div".$this->wrap_output('style', $this->bgStyle()).$this->wrap_output('class', $this->bgTheme()).">\r\n";
        echo $this->indent(7);
        echo "<div".$this->wrap_output('style', $this->trackStyle()).$this->wrap_output('class', $this->trackTheme())."></div>\r\n";
        echo $this->indent(7);
        echo "<div".$this->wrap_output('style', $this->handleStyle()).$this->wrap_output('class', $this->handleTheme())."></div>\r\n";
        echo $this->indent(6);
        echo "</div>\r\n";
        echo $this->indent(5);
        echo "</div>\r\n";
        echo $this->indent(5);
        echo "<div class='weui-slider-box__value'>".$this->value()."</div>\r\n";
        echo $space;
        echo "</div>\r\n";
    }
}
