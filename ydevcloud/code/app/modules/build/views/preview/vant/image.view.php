<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Image_View extends Preview_View {
    use  Vant_Popup,Html_Code_Helper;

    protected function style_map($meta=null, $state = 'normal')
    {
        $style = parent::style_map($meta);
        unset($style['object-fit'],$style['object-position']);
        if (!@$this->data['meta']['style']['width']){
            $style['width'] = "100%";
        }
        $style['overflow'] = "hidden";
        return $style;
    }
    protected function css_map()
    {
        $map = parent::css_map();
        $map['-'] = "van-image";
        return $map;
    }

    private function img_Style() {
        $style = parent::style_map();
        $_ = [];
        if ($style['object-fit']) $_[] = $style['object-fit'];
        if ($style['object-position']) $_[] = $style['object-position'];
        return join(";", $_);
    }
    public function build_ui()
    {
        $space =  $this->indent();
        $imgSrc = @$this->data['meta']['value']?:'/uibuilder.jpg';
        $imgSrc = $this->get_Img_Src($imgSrc);
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ' alt="'.(@$this->data['meta']['title']).'"';
        echo ' src="'.($imgSrc).'"';
        echo ">\r\n";

        echo $this->indent(1)."{$space}<img";
        echo $this->wrap_output("class", "van-image__img");
        echo $this->wrap_output("style", $this->img_Style());
        echo $this->wrap_output("alt", $this->data['meta']['title']);
        echo $this->wrap_output("src", $imgSrc);
        echo ">\r\n";

        echo "{$space}</div>";
    }
}
