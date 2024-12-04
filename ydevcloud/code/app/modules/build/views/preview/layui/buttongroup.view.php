<?php
namespace app\modules\build\views\preview\layui;



use app\modules\build\views\preview\Preview_View;

class Buttongroup_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    protected function css_map()
    {
        $cssArray = parent::css_map();
        // sizing和theme放到子button里面
        unset($cssArray['buttonSizing'], $cssArray['backgroundTheme']);
        $cssArray['-'] = 'layui-btn-group';
        return $cssArray;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo $space."<div";
        echo $this->output_main_attrs();
        echo ">\r\n";
        foreach ((array)$this->childViews as $view){
            $view->output();
        }
        echo $space;
        echo "</div>\r\n";
    }
}
