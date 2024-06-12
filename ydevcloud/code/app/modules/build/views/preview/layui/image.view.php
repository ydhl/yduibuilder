<?php
namespace app\modules\build\views\preview\layui;



use app\modules\build\views\preview\Preview_View;

class Image_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<img";
        echo $this->build_main_attrs();
        echo ' alt="'.(@$this->data['meta']['title']).'"';
        echo ' src="'.(@$this->data['meta']['value']?:'/uibuilder.jpg').'"';
        echo "/>\r\n";
    }
}
