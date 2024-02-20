<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;
use function yangzie\__;

class Carousel_View extends Preview_View {
    use Html_Event_Binding, Bootstrap_Popup,Html_Code_Helper;
    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo $this->wrap_output('data-ride', 'carousel');
        echo ">\r\n";

        $this->build_indicator();
        $this->build_slide();
        $this->build_control();
        echo $this->indent();
        echo "</div>\r\n";
    }
    protected function css_map()
    {
        $map = parent::css_map();
        $css = ['carousel slide'];
        if ($this->data['meta']['custom']['effect'] == 'crossfade'){
            $css[] = 'carousel-fade';
        }
        $map['-'] = join(' ',$css);
        return $map;
    }
    protected function placeholder_style()
    {
        $map = ['background-color:#777'];
        if (!$this->data['meta']['style']['height'] && !$this->data['meta']['style']['min-height'] ){
            $map[] = 'height:300px';
        }else{
            $map[] = 'height:' . $this->data['meta']['style']['height'];
            $map[] = 'min-height:'.$this->data['meta']['style']['min-height'];
        }
        return join(';', $map);
    }

    private function build_indicator(){
        if ( ! $this->data['meta']['custom']['showIndicator']) return;
        echo $this->indent(1);
        echo '<ol class="carousel-indicators">'."\r\n";
        if ( ! $this->data['items']) {
            echo $this->indent(2);
            echo "<li";
            echo $this->wrap_output("data-target", "#".$this->myid(true));
            echo $this->wrap_output("data-slide-to", 0);
            echo $this->wrap_output("class", 'active');
            echo "></li>\r\n";
        }
        foreach ($this->data['items'] as $index => $item){
            echo $this->indent(2);
            echo "<li";
            echo $this->wrap_output("data-target", "#".$this->myid(true));
            echo $this->wrap_output("data-slide-to", $index);
            echo $this->wrap_output("class", !isset($this->data['meta']['custom']['activeSlide']) && !$index || $this->data['meta']['custom']['activeSlide'] == $index ? 'active' : null);
            echo "></li>\r\n";
        }

        echo $this->indent(1);
        echo "</ol>\r\n";
    }
    private function build_slide(){
        echo $this->indent(1);
        echo "<div class=\"carousel-inner\">\r\n";
        if ( ! $this->data['items']) {
            echo $this->indent(2);
            echo '<div class="carousel-item active">'."\r\n";
            echo $this->indent(3);
            echo '<div class="d-block w-100 d-flex justify-content-center align-items-center"';
            echo $this->wrap_output('style', $this->placeholder_style());
            echo ">\r\n";
            echo $this->indent(4);
            echo '<div class="display-1 text-center">';
            echo __("Slide");
            echo "</div>\r\n";
            echo $this->indent(3);
            echo "</div>\r\n";
            echo $this->indent(2);
            echo "</div>\r\n";
        }

        foreach ((array)@$this->childViews as $index => $view){
            echo $this->indent(2);
            echo "<div";
            echo $this->wrap_output('class', 'carousel-item '.(!isset($this->data['meta']['custom']['activeSlide']) && !$index || $this->data['meta']['custom']['activeSlide'] == $index ? 'active' : null));
            echo ">\r\n";
            $view->increase_indent(2);
            $view->output();

            echo $this->indent(2);
            echo "</div>\r\n";
        }

        echo $this->indent(1);
        echo "</div>\r\n";
    }
    private function build_control(){
        if ( ! $this->data['meta']['custom']['showIndicator']) return;
        echo $this->indent(1);
        echo '<a class="carousel-control-prev" type="button"';
        echo $this->wrap_output("data-target", "#".$this->myid(true));
        echo " data-slide=\"prev\">\r\n";
        echo $this->indent(2);
        echo "<span class='carousel-control-prev-icon' aria-hidden='true'></span>\r\n";
        echo $this->indent(1);
        echo "</a>\r\n";
        echo $this->indent(1);
        echo '<a class="carousel-control-next" type="button"';
        echo $this->wrap_output("data-target", "#".$this->myid(true));
        echo " data-slide='next'>\r\n";
        echo $this->indent(2);
        echo "<span class=\"carousel-control-next-icon\" aria-hidden=\"true\"></span>\r\n";
        echo $this->indent(1);
        echo "</a>\r\n";
    }
}
