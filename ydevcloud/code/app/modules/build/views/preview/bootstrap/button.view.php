<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Button_View extends Preview_View {
    use Bootstrap_Popup,Html_Code_Helper;
    public function build_ui()
    {
        $space =  $this->indent();
        $meta = $this->data['meta'];
        $type = $meta['custom']['type'] ?: "button";
        // 一般按钮
        echo $space;
        if (@$meta['custom']['type']=='link'){
            echo "<a";
            echo $this->wrap_output('href', $this->data['meta']['custom']['linkHref']);
            if (@$this->data['meta']['custom']['disabled']){
                echo ' disabled ';
            }
            $this->build_main_attrs();
            echo '>';
            $this->wrap_icon(function(){
                echo $this->data['meta']['title'] ?: $this->data['type'];
            });
            echo PHP_EOL;
            echo $space."</a>".PHP_EOL;
        }else{
            echo "<button";
            echo $this->wrap_output('type', $type);
            echo $this->wrap_output('title', addslashes($this->data['meta']['title']));
            if (@$this->data['meta']['custom']['disabled']){
                echo ' disabled ';
            }
            $this->build_main_attrs();
            echo '>';
            $this->wrap_icon(function(){
                echo $this->data['meta']['title'] ?: $this->data['type'];
            });
            echo PHP_EOL;
            echo $space."</button>".PHP_EOL;
        }
    }
    protected function css_map()
    {
        $cssMap = parent::css_map();
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
        if (@$buttonMeta['css']['buttonSizing']){
            $sizing = $this->cssTranslate['buttonSizing'][$buttonMeta['css']['buttonSizing']];
            if ($sizing){
                $css[] = $sizing;
            }
        }
        $cssMap['-'] = join(' ', $css);
        return $cssMap;
    }
    protected function style_map($meta=null, $state='normal')
    {
        $meta = $meta??$this->data['meta'];
        $styleArray = parent::style_map($meta, $state);
        $buttonMeta = $this->buttonMeta();
        $selfHasForeground = $meta['css']['foregroundTheme'] && $meta['css']['foregroundTheme'] !== 'default';
        $selfHasBackground = $meta['css']['backgroundTheme'] && $meta['css']['backgroundTheme'] !== 'default';
        // 如果按钮有背景和前景则用按钮的，否则用上层的
        $color = $meta['style']['color'] ?: $buttonMeta['style']['color'];
        $backgroundColor = $meta['style']['background-color'] ?: $buttonMeta['style']['background-color'];
        if (!$selfHasForeground && $color){
            $styleArray['color'] = "color: ${color} !important";
        }
        if (!$selfHasBackground && $backgroundColor){
            $styleArray['background-color'] = "background-color: ${backgroundColor} !important";
            $styleArray['border-color'] = "border-color: ${backgroundColor} !important";
        }
        if (@$buttonMeta['custom']['isOutline'] && $state=='normal') {
            unset($styleArray['background-color'],$styleArray['background-image']);
        }
        return $styleArray;
    }

    private function buttonMeta () {
        $parentUI = $this->get_parent_UI();
        $type = strtolower($parentUI['type']);
        $parentIsNavbar = in_array($type, ['nav']);
        if ($parentIsNavbar) {
            return $parentUI['meta'];
        }
        return $this->data['meta'];
    }
}
