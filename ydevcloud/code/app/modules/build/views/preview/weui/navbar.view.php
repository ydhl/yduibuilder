<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Navbar_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $css = parent::css_map();
        unset($css['foregroundTheme']);
        $css['-'] = 'weui-tabbar';
        return $css;
    }

    protected function style_map()
    {
        $map = parent::style_map();
        unset($map['color']);
        return $map;
    }
    protected function itemCss($item){
        $css = ['weui-tabbar__label'];
        if (@$item['checked']){
            $cssMap = parent::css_map();
            $css[] = $cssMap['foregroundTheme'] ?: 'text-success';
        }
        return join(' ', $css);
    }
    protected function itemStyle($item){
        $style = [];
        if (@$item['checked']){
            $map = parent::style_map();
            $style[] = $map['color'] ?: '';
        }
        return join(';', $style);
    }
    public function build_ui()
    {

        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '1' ], [ "text"=> 'Sample 2', "value"=> '2', "checked"=> true ]];
        $space =  $this->indent();
        echo "{$space}<nav ";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ((array)@$values as $index => $item){
            echo $this->indent(1) . "<div class='weui-tabbar__item'>\r\n";
            echo $this->indent(2) . "<div class='weui-tabbar_icon'>\r\n";
            if (@$item['image']){
                echo $this->indent(3) . "<div style='background-image: url({$item['image']});background-position:center;background-size:cover;' class='weui-tabbar__icon'></div>\r\n";
            }elseif (@$item['icon']){
                echo $this->indent(3) . "<i class='weui-tabbar__icon {$item['icon']}'></i>\r\n";
            }else{
                echo $this->indent(3) . "<div class='weui-tabbar__icon bg-secondary'></div>\r\n";
            }
            if ($index===0) echo $this->indent(3) . "<span class='weui-badge'>8</span>\r\n";
            echo $this->indent(2) . "</div>\r\n";
            echo $this->indent(2) . "<p ".$this->wrap_output('class', $this->itemCss($item))." ".($this->wrap_output('style', $this->itemStyle($item))).">\r\n";
            echo $this->indent(3) . @$item['text'] . "\r\n";
            echo $this->indent(2) . "</p>\r\n";
            echo $this->indent(1) . "</div>\r\n";
        }

        echo "{$space}</nav>\r\n";
    }
}
