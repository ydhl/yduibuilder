<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Breadcrumb_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup,Html_Code_Helper;
    protected function values() {
        if (@!$this->data['meta']['values']){
            return [["text"=> 'Page A', "value"=> '#1' ], [ "text"=> 'Page B', "value"=> '#2' ]];
        }
        return $this->data['meta']['values'];
    }

    protected function foregroundCss(){
        $css = [];
        $cssMap = parent::css_map();
        if ($cssMap['foregroundTheme']){
            $css[] = $cssMap['foregroundTheme'];
        }
        return join(' ', $css);
    }
    protected function foregroundStyle() {
        $styleMap = parent::style_map();
        $style = [];
        if ($styleMap['color']) {
            $style[] = $styleMap['color'];
        }
        return $style ? join(";", $style) : '';
    }
    protected function css_map()
    {
        $cssArray = parent::css_map();
        $cssArray['breadcrumb'] = 'weui-breadcrumb';
        unset($cssArray['foregroundTheme']);
        return $cssArray;
    }
    protected function style_map()
    {
        $styleArray = parent::style_map();
        unset($styleArray['color']);
        return $styleArray;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<ol";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ($this->values() as $item){
            echo $this->indent(1);
            echo "<li class='weui-breadcrumb-item";
            echo @$item['checked'] ? ' active' : '';
            echo "'>\r\n";
            if (!@$item['checked']){
                echo $this->indent(2);
                echo "<span ".$this->wrap_output('class', $this->foregroundCss())
                    .$this->wrap_output('style', $this->foregroundStyle()).">{$item['text']}</span>\r\n";
            }else{
                echo $this->indent(2);
                echo "<span>{$item['text']}</span>\r\n";
            }
            echo $this->indent(1);
            echo "</li>\r\n";
        }

        echo $space;
        echo "</ol>\r\n";
    }
}
