<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Buttongroup_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $cssArray = parent::css_map();
        unset($cssArray['foregroundTheme'],$cssArray['buttonSizing'], $cssArray['backgroundTheme']);
        $cssArray['-'] = 'weui-btn_group';
        return $cssArray;
    }
    protected function style_map()
    {
        $styleArray = parent::style_map();
        unset($styleArray['color'], $styleArray['background-color']);
        return $styleArray;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo $space."<div role='group' ";
        echo $this->build_main_attrs();
        echo ">\r\n";
        foreach ((array)$this->childViews as $view){
            $view->output();
        }
        echo $space;
        echo "</div>\r\n";
    }
}
