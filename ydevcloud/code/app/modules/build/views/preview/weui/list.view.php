<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class List_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup,Html_Code_Helper;
    protected function itemTheme($item) {
        $css = parent::css_map();
        $itemCss = [];
        if ($css['backgroundTheme']) $itemCss[] = $css['backgroundTheme'];
        if ($css['foregroundTheme']) $itemCss[] = $css['foregroundTheme'];
        unset($css['backgroundTheme'], $css['foregroundTheme']);
        $itemCss[] = 'weui-cell';
        return join(' ', $itemCss);
    }
    protected function itemStyle() {
        $styleMap = parent::style_map();
        $style = [];
        if (@$styleMap['color']) {
            $style[] = $styleMap['color'];
        }
        if (@$styleMap['background-color']) {
            $style[] = $styleMap['background-color'];
            $style[] = 'border-color:'. $this->data['meta']['style']['background-color'];
        }
        return join(';', $style);
    }
    protected function css_map()
    {
        $arrMap = parent::css_map();
        unset($arrMap['backgroundTheme'], $arrMap['foregroundTheme']);
        return $arrMap;
    }
    protected function style_map()
    {
        $styleArray = parent::style_map();
        unset($styleArray['color'], $styleArray['background-color']);
        return $styleArray;
    }

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '1' ], [ "text"=> 'Sample 2', "value"=> '2' ]];
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ((array)@$values as $item){
            echo $this->indent(1) . "<div ".$this->wrap_output('class', $this->itemTheme($item))
                .$this->wrap_output('style', $this->itemStyle()).">\r\n";
            echo $this->indent(2) . "<span class='weui-cell__bd'>\r\n";
            echo $this->indent(3) . "<span>".@$item['text']."</span>\r\n";
            echo $this->indent(2) . "</span>\r\n";
            if (@$item['checked']){
                echo $this->indent(2) . "<span class='weui-cell__ft'><i class='weui-icon-success'></i></span>\r\n";
            }
            echo $this->indent(1) . "</div>\r\n";
        }

        echo "{$space}</div>\r\n";
    }
}
