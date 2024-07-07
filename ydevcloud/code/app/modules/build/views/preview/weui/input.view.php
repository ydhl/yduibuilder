<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\bootstrap\Input_View as Bootstrap_Input_View;


class Input_View extends Bootstrap_Input_View {

    protected function clear_button(){
        return '<i class="weui-icon-clear"></i>';
    }
}
