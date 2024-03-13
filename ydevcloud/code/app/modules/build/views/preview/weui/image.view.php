<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Image_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup,Html_Code_Helper;

    protected function style_map()
    {
        $map = parent::style_map();

        // 默认情况下宽度撑满
        if (!@$map['width']){
            $map['width'] = "width:100%";
        }

        return $map;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $imgSrc = @$this->data['meta']['value']?:'/uibuilder.jpg';
        $imgSrc = $this->get_Img_Src($imgSrc);
        echo "{$space}<img";
        echo $this->build_main_attrs();
        echo ' alt="'.(@$this->data['meta']['title']).'"';
        echo ' src="'.($imgSrc).'"';
        echo "/>\r\n";
    }
}
