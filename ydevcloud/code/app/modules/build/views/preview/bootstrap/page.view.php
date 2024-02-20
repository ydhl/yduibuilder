<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;

class Page_View extends Preview_View {
    use Html_Event_Binding,Bootstrap_Popup,Html_Code_Helper;
    protected function style_map()
    {
        $map = parent::style_map();
        $map['flex-grow']= 'flex-grow:1'; # 17502

        // 当是弹窗页面时，调整样式让里面的modal能居中，拉抻显示
        if (strtolower($this->data['pageType']) == 'popup'){
            $map['display']= 'display:flex';
            $map['justify-content']= 'justify-content:center';
            $map['align-items']= 'align-items:stretch';
        }
        return $map;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">";
        echo "\r\n";
        foreach ($this->childViews as $view){
            $view->output();
        }

        echo "{$space}</div>\r\n";
    }
}
