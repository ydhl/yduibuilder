<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Button_View extends Preview_View {
    use  Vant_Popup,Html_Code_Helper;
    private function buttonMeta () {
        $parentUI = $this->get_parent_UI();
        $type = strtolower($parentUI['type']);
        $parentIsButtonGroup = $type == 'buttongroup';
        $parentIsNavbar = in_array($type, ['nav', 'navbar']);
        if ($parentIsButtonGroup || $parentIsNavbar) {
            return $parentUI['meta'];
        }
        return $this->data['meta'];
    }
    protected function css_map()
    {
        $cssMap = parent::css_map();
        $parentUI = $this->get_parent_UI();
        $parentIsButtonGroup = strtolower($parentUI['type']) == 'buttongroup';
        // 如果按钮有背景和前景则用按钮的，否则用上层的，如buttongroup
        $myBackgruondTheme = $cssMap['backgroundTheme'] ? $this->data['meta']['css']['backgroundTheme'] : '';
        $myForegroundTheme = $cssMap['foregroundTheme'] ? $this->data['meta']['css']['foregroundTheme'] : '';
        unset($cssMap['backgroundTheme']);
        unset($cssMap['foregroundTheme']);
        $buttonMeta = $this->buttonMeta();
        $myBackgruondTheme = $myBackgruondTheme ?: $buttonMeta['css']['backgroundTheme'];
        $myForegroundTheme = $myForegroundTheme ?: $buttonMeta['css']['foregroundTheme'];

        $css = ['van-button'];
        // 如果上层是按钮，那么继承他的outline，size属性
        $isOutline = @$buttonMeta['custom']['isOutline'] ? 'outline-' : '';
        if ($myBackgruondTheme && $myBackgruondTheme!= 'default'){
            $css[] = 'van-button--' . $myBackgruondTheme;
        }else{
            $css[] = 'van-button--primary';
        }
        if ($isOutline) {
            $css[] = 'van-button--plain';
        }

        if ($myForegroundTheme && $myForegroundTheme!= 'default'){
            $css[] = $this->cssTranslate['foregroundTheme'][$myForegroundTheme];
        }

        if (@$buttonMeta['css']['buttonSizing']){
            $css[] = $this->cssTranslate['buttonSizing'][$buttonMeta['css']['buttonSizing']] ?: 'van-button--normal';
        }
        $cssMap['-'] = join(' ', $css);
        return $cssMap;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta);

        $buttonMeta = $this->buttonMeta();
        $selfHasForeground = $meta['css']['foregroundTheme'] && $meta['css']['foregroundTheme'] !== 'default';
        $selfHasBackground = $meta['css']['backgroundTheme'] && $meta['css']['backgroundTheme'] !== 'default';
        // 如果按钮有背景和前景则用按钮的，否则用上层的buttongroup
        $color = $meta['style']['color'] ?: $buttonMeta['style']['color'];
        $backgroundColor = $meta['style']['background-color'] ?: $buttonMeta['style']['background-color'];
        if (!$selfHasForeground && $color){
            $styleArray['color'] = "color: ${color} !important";
        }
        if (!$selfHasBackground && $backgroundColor){
            $styleArray['background-color'] = "background-color: ${backgroundColor} !important";
            $styleArray['border-color'] = "border-color: ${backgroundColor} !important";
        }
        return $styleArray;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $parentUI = $this->get_parent_UI();
        $parentIsButtonGroup = strtolower($parentUI['type']) == 'buttongroup';
        $meta = $parentIsButtonGroup ? $parentUI['meta'] : $this->data['meta'];
        $type = $meta['custom']['type'] ?: "button";
        // 一般按钮
        echo $space;
        $tag = @$meta['custom']['type']=='link' ? "a" : "button";
        echo "<{$tag} type='{$type}'";
        if (@$this->data['meta']['custom']['disabled']){
            echo ' disabled ';
        }
        if (@$meta['custom']['type']=='link'){
            echo $this->wrap_output('href', $this->data['meta']['custom']['linkHref']);
        }
        echo $this->build_main_attrs().'>';
        $this->wrap_icon(function(){
            echo $this->data['meta']['title'] ?: $this->data['type'];
        });
        echo "\r\n";
        echo $this->indent();
        echo "</{$tag}>\r\n";
    }
}
