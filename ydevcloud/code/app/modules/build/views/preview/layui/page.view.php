<?php
namespace app\modules\build\views\preview\layui;

use app\modules\build\views\preview\bootstrap\Page_View as Bootstrap_Page_View;
use app\modules\build\views\preview\Preview_View;

class Page_View extends Bootstrap_Page_View {
    use Layui_Popup,Layui_Code_Helper;

    protected function style_map($meta=null, $state = 'normal')
    {
        $map = Preview_View::style_map($meta, $state);
        $map['flex-grow']= 'flex-grow:1'; # 17502
        return $map;
    }
    protected function css_map()
    {
        $cssMap = Preview_View::css_map();
        $hasForm = false;
        foreach ((array)$this->data['items'] as $item) {
            if ($item['meta']['form']) {
                $hasForm = true;
                break;
            }
        }
        if ($hasForm) {
            $cssMap['form'] = 'layui-form';
        }
        return $cssMap;
    }
}
