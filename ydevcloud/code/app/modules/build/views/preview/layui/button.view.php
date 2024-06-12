<?php
namespace app\modules\build\views\preview\layui;



use app\modules\build\views\preview\Preview_View;

class Button_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    private function button_meta () {
        $parentUI = $this->get_parent_UI();
        $type = strtolower($parentUI['type']);
        $parentIsButtonGroup = $type == 'buttongroup';
        $parentIsNavbar = in_array($type, ['nav', 'navbar']);
        if ($parentIsButtonGroup || $parentIsNavbar) {
            return $parentUI['meta'];
        }
        return $this->data['meta'];
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta);

        $buttonMeta = $this->button_meta();
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
        $meta = $this->button_meta();
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

        if ($parentIsButtonGroup) {
            $sizing = $this->cssTranslate['buttonSizing'][$meta['css']['buttonSizing']];
            $css[] = $sizing;
        }
        $cssMap['-'] = join(' ', $css);
        return $cssMap;
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
        if (@$meta['custom']['type']=='link'){
            echo "<a href='{$this->data['meta']['custom']['linkHref']}' ";
            if (@$this->data['meta']['custom']['disabled']){
                echo ' disabled ';
            }
            echo $this->build_main_attrs().'>';
            $this->wrap_icon(function(){
                echo $this->data['meta']['title'] ?: $this->data['type'];
            });
            echo "\r\n";
            echo $this->indent();
            echo "</a>\r\n";
        }else{
            echo "<button type='{$type}' title='".addslashes($this->data['meta']['title'])."'";
            if (@$this->data['meta']['custom']['disabled']){
                echo ' disabled ';
            }
            echo $this->build_main_attrs().'>';
            $this->wrap_icon(function(){
                echo $this->data['meta']['title'] ?: $this->data['type'];
            });
            echo "\r\n";
            echo $this->indent();
            echo "</button>\r\n";
        }
    }
}
