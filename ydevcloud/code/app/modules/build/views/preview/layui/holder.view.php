<?php
namespace app\modules\build\views\preview\layui;



use app\modules\build\views\preview\Preview_View;

class Holder_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";
        echo "{$space}</div>\r\n";
    }
}
