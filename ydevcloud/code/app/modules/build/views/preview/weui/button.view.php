<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Button_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup,Html_Code_Helper;
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

        $topIsModal =  strtolower($this->get_parent_UI()['type']) === 'modal';

        $css = [];
        $css[] = '';
        if ($topIsModal && $this->build->get_in_Parent_Placement()=='foot') { // 弹窗footer中的按钮
            $css[] = 'weui-dialog__btn border-0';
        } else {
            $css[] = 'weui-btn';
        }
        // 如果上层是按钮，那么继承他的outline，size属性
        $buttonType = $this->data['meta']['custom']['type'] ?: 'button';
        if ($buttonType != 'link'){
            $isOutline = @$buttonMeta['custom']['isOutline'] ? 'outline_' : '';
            if ($myBackgruondTheme && $myBackgruondTheme!= 'default'){
                $css[] = 'weui-btn_' . $isOutline . $myBackgruondTheme;
            }else{
                $css[] = 'weui-btn_' . $isOutline . 'primary';
            }
        }else{
            $css[] = 'weui-btn_link';
            if ($myBackgruondTheme && $myBackgruondTheme!= 'default'){
                $css[] = $this->cssTranslate['backgroundTheme'][$myBackgruondTheme];
            }
        }
        if ($myForegroundTheme && $myForegroundTheme!= 'default'){
            $css[] = $this->cssTranslate['foregroundTheme'][$myForegroundTheme];
        }

        // button group 中的尺寸
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
        // 一般按钮
        echo $space;
        echo "<div";
        echo $this->wrap_output('title',addslashes($this->data['meta']['title']));
        if (@$this->data['meta']['custom']['disabled']){
            echo ' disabled ';
        }
        echo $this->build_main_attrs().'>';
        $this->wrap_icon(function(){
            echo $this->data['meta']['title'] ?: $this->data['type'];
        });
        echo "\r\n";
        echo $this->indent();
        echo "</div>\r\n";
    }
}
