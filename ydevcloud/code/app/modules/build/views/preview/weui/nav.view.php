<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Nav_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup,Html_Code_Helper;
    protected function activeItemCss() {
        $styleMap =  parent::style_map();
        if ($styleMap['color']) return '';// 有自定义颜色，则忽略预定义样式
        $theme = $this->data['meta']['css']['foregroundTheme'];
        if (!$theme || $theme === 'default') return 'bg-light text-dark';
        return $this->cssTranslate['backgroundTheme'][$theme] . ' text-white';
    }
    protected function itemCss() {
        $styleMap =  parent::style_map();
        if ($styleMap['color']) return '';// 有自定义颜色，则忽略预定义样式
        $theme = $this->data['meta']['css']['foregroundTheme'];
        if (!$theme || $theme === 'default') return 'text-dark';
        // 转成对应都前景主题
        return $this->cssTranslate['foregroundTheme'][$theme];
    }
    protected function activeItemStyle() {
        $styleMap =  parent::style_map();
        if (@$styleMap['color']) return 'color:#fff;background-color:'.$this->data['meta']['style']['color'];
    }
    protected function itemStyle() {
        $styleMap =  parent::style_map();
        if (@$styleMap['color']) return $styleMap['color'];
    }
    protected function css_map()
    {
        $cssMap = parent::css_map();
        unset($cssMap['foregroundTheme']);
        $cssMap['-'] = 'weui-navbar';

        return $cssMap;
    }
    protected function style_map()
    {
        $styleArray = parent::style_map();
        unset($styleArray['color']);
        return $styleArray;
    }

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '#' ], [ "text"=> 'Sample 2', "value"=> '#', 'checked'=> true ]];
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ((array)@$values as $item){
            echo $this->indent(1) . "<div class='weui-navbar__item "
                .($item['checked'] ? $this->activeItemCss() : $this->itemCss())
                ."' style='".($item['checked'] ? $this->activeItemStyle() : $this->itemStyle())
                ."'>\r\n";
            echo $this->indent(2) . "{$item['text']}\r\n";
            echo $this->indent( 1) . "</div>\r\n";
        }
        foreach ((array)@$this->childViews as $view){
            $view->output();
        }
        echo "{$space}</div>\r\n";
    }
}
