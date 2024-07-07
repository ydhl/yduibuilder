<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\bootstrap\Container_View as Bootstrap_Container_View;

class Container_View extends Bootstrap_Container_View {
    protected function style_map($meta = null, $state = 'normal')
    {
        $style = parent::style_map($meta, $state);
        if (!$this->data['items']){
            $style['min-height'] = "min-height:100px !important;";
            $style['min-width'] = "min-width:100px !important;";
        }
        return $style;
    }
}
