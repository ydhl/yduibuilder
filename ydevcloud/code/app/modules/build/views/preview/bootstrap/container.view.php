<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;
class Container_View extends Preview_View {
    use Html_Event_Binding, Bootstrap_Popup,Html_Code_Helper;
    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ((array)@$this->childViews as $view){
            $view->output();
        }

        echo "{$space}</div>\r\n";
    }
}
