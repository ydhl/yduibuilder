<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Icon_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $css =  parent::css_map();
        $css['icon'] = $this->data['meta']['custom']['icon'];
        return $css;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<i";
        echo $this->build_main_attrs();
        echo "></i>\r\n";
    }
}
