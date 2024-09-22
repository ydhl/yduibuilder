<?php
namespace app\modules\build\views\preview\layui;



use app\modules\build\views\preview\bootstrap\Hr_View as Bootstrap_Hr_View;
use app\modules\build\views\preview\Preview_View;

class Hr_View extends Bootstrap_Hr_View {
    use Layui_Popup,Layui_Code_Helper;
    protected function lineCss() {
        $css = ['layui-flex-grow-1 line'];
        if (!$this->data['meta']['style']['background-color'] && $this->data['meta']['css']['backgroundTheme']){
            $css[] = $this->cssTranslate['borderColorClass'][$this->data['meta']['css']['backgroundTheme']];
        }
        return join(' ', $css);
    }
    protected function textCss() {
        $map = parent::css_map();
        return 'layui-flex-shrink-0 layui-pl-2 layui-pr-2 '.$map['foregroundTheme'];
    }
    protected function css_map()
    {
        $map = Preview_View::css_map();
        unset($map['backgroundTheme']);
        unset($map['foregroundTheme']);
        $map['-'] = 'layui-d-flex layui-justify-content-center layui-align-items-center';
        return $map;
    }
}
