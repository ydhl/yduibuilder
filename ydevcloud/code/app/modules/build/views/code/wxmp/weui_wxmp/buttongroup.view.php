<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Buttongroup_View as Preview_Buttongroup_View;

class Buttongroup_View extends Preview_Buttongroup_View {
    use Wxmp;
    public function build_ui()
    {
        $space =  $this->indent();
        echo $space."<view ";
        echo $this->build_main_attrs();
        echo ">\r\n";
        foreach ((array)$this->childViews as $view){
            $view->output();
        }
        echo $space;
        echo "</view>\r\n";
    }
}
