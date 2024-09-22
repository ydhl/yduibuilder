<?php
namespace app\modules\build\views\preview\layui;

use app\modules\build\views\preview\bootstrap\Container_View as Bootstrap_Container_View;

class Container_View extends Bootstrap_Container_View {
    use Layui_Popup,Layui_Code_Helper;

    protected function css_map()
    {
        $cssMap = parent::css_map();
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
