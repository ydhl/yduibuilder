<?php
namespace app\modules\build\views\preview\layui;

use app\modules\build\views\preview\bootstrap\Button_View as Bootstrap_Button_View;

class Button_View extends Bootstrap_Button_View {
    use Layui_Popup,Layui_Code_Helper;


    protected function css_map()
    {
        $cssMap = parent::css_map();
        // 如果按钮有背景和前景则用按钮的，否则用上层的，如buttongroup
        $myBackgruondTheme = $cssMap['backgroundTheme'] ? $this->data['meta']['css']['backgroundTheme'] : '';
        $myForegroundTheme = $cssMap['foregroundTheme'] ? $this->data['meta']['css']['foregroundTheme'] : '';
        unset($cssMap['backgroundTheme']);
        unset($cssMap['foregroundTheme']);
        $meta = $this->buttonMeta();
        $myBackgruondTheme = $myBackgruondTheme ?: $meta['css']['backgroundTheme'];
        $myForegroundTheme = $myForegroundTheme ?: $meta['css']['foregroundTheme'];

        // 如果上层是按钮，那么继承他的outline，size属性
        $buttonType = $this->data['meta']['custom']['type'] ?: 'button';

        $css[] = 'layui-btn';
        if ($buttonType == 'link') {
            $css[] = 'layui-btn-primary layui-border-0';
        }
        if ($meta['custom']['isOutline']){
            // 删除button theme outline 通过边框和前景色来实现
            unset($cssMap['backgroundTheme']);
            $css[] = 'layui-btn-primary'; // outline 效果样式
            $css[] = $this->cssTranslate['borderColorClass'][$myBackgruondTheme];
        } else {
            $css[] = $this->cssTranslate['backgroundTheme'][$myBackgruondTheme];
        }
        if ($myForegroundTheme && $myForegroundTheme!= 'default'){
            $css[] = $this->cssTranslate['foregroundTheme'][$myForegroundTheme];
        }

        if (@$meta['css']['buttonSizing']){
            $sizing = $this->cssTranslate['buttonSizing'][$meta['css']['buttonSizing']];
            if ($sizing){
                $css[] = $sizing;
            }
        }
        $cssMap['-'] = join(' ', $css);
        return $cssMap;
    }
}
