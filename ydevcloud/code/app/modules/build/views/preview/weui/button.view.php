<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\bootstrap\Button_View as Bootstrap_Button_View;

class Button_View extends Bootstrap_Button_View {

    protected function css_map()
    {
        $cssMap = parent::css_map();

        // 如果按钮有背景和前景则用按钮的，否则用上层的
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
        if (@$buttonMeta['css']['buttonSizing']){
            $sizing = $this->cssTranslate['buttonSizing'][$buttonMeta['css']['buttonSizing']];
            if ($sizing){
                $css[] = $sizing;
            }
        }
        $cssMap['-'] = join(' ', $css);
        return $cssMap;
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
        echo PHP_EOL;
        echo $this->indent();
        echo "</div>".PHP_EOL;
    }
}
