<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\bootstrap\Textarea_View as Bootstrap_Textarea_View;

class Textarea_View extends Bootstrap_Textarea_View {

    protected function clear_button(){
        return '<i class="weui-icon-clear"></i>';
    }
}
