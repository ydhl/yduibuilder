<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Progress_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup, Html_Code_Helper;
    protected function css_map()
    {
        $cssMap = parent::css_map();
        unset($cssMap['foregroundTheme']);
        $cssMap['-'] = 'weui-progress';
        return $cssMap;
    }

    protected function theme_css() {
        $css = ['weui-progress-bar'];
        if (@$this->data['meta']['css']['foregroundTheme']){
            $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['foregroundTheme']];
        }
        if (@$this->data['meta']['custom']['striped']){
            $css[] = 'weui-progress-bar-striped';
        }
        if (@$this->data['meta']['custom']['animatedStrip']){
            $css[] = 'weui-progress-bar-animated';
        }
        return join(' ', $css);
    }
    protected function style_map()
    {
        $styleArray = parent::style_map();
        unset($styleArray['color']);
        return $styleArray;
    }

    protected function theme_style() {
        $value = $this->data['meta']['value']?:50;
        $style = ["width: {$value}%"];
        $styleMap = parent::style_map();

        if ($styleMap['color']){
            $style[] = "background-color:".$this->data['meta']['style']['color'];
        }
        return join(';', $style);
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $value = $this->data['meta']['value']?:50;
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";

        echo $this->indent(1).'<div';
        echo $this->wrap_output('class', $this->theme_css());
        echo $this->wrap_output('style', $this->theme_style());
        echo ">\r\n";

        if (@$this->data['meta']['custom']['label']){
            echo $this->indent(2)."{$value}%\r\n";
        }
        echo $this->indent(1)."</div>\r\n";
        echo "{$space}</div>\r\n";
    }
}
