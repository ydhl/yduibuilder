<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;

class Button_View extends Preview_View {
    use Html_Event_Binding, Bootstrap_Popup,Html_Code_Helper;
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

        $css = [];
        // 如果上层是按钮，那么继承他的outline，size属性
        $buttonType = $this->data['meta']['custom']['type'] ?: 'button';
        if ($buttonType != 'link'){
            $css[] = 'btn';
            $isOutline = @$buttonMeta['custom']['isOutline'] ? 'outline-' : '';
            if ($myBackgruondTheme && $myBackgruondTheme!= 'default'){
                $css[] = 'btn-' . $isOutline . $myBackgruondTheme;
            }else{
                $css[] = 'btn-' . $isOutline . 'primary';
            }
        }else{
            $css[] = 'btn btn-link';
        }

        if ($myForegroundTheme && $myForegroundTheme!= 'default'){
            $css[] = $this->cssTranslate['foregroundTheme'][$myForegroundTheme];
        }

        if (@$buttonMeta['css']['buttonSizing'] && $parentIsButtonGroup){
            $sizing = $this->cssTranslate['buttonSizing'][$buttonMeta['css']['buttonSizing']];
            if ($sizing){
                $css[] = $sizing;
            }
        }
        $cssMap['-'] = join(' ', $css);
        return $cssMap;
    }
    protected function style_map()
    {
        $styleArray = parent::style_map();

        $buttonMeta = $this->buttonMeta();
        $selfHasForeground = $this->data['meta']['css']['foregroundTheme'] && $this->data['meta']['css']['foregroundTheme'] !== 'default';
        $selfHasBackground = $this->data['meta']['css']['backgroundTheme'] && $this->data['meta']['css']['backgroundTheme'] !== 'default';
        // 如果按钮有背景和前景则用按钮的，否则用上层的buttongroup
        $color = $this->data['meta']['color'] ?: $buttonMeta['style']['color'];
        $backgroundColor = $this->data['meta']['background-color'] ?: $buttonMeta['style']['background-color'];
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
