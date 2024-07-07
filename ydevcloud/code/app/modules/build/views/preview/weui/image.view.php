<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\bootstrap\Image_View as Bootstrap_Image_View;

class Image_View extends Bootstrap_Image_View {
    protected function style_map($meta=null, $state = 'normal')
    {
        $map = parent::style_map($meta, $state);

        // 默认情况下宽度撑满
        if (!@$map['width']){
            $map['width'] = "width:100%";
        }

        return $map;
    }
}
