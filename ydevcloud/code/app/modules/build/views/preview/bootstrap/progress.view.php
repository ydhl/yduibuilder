<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;

class Progress_View extends Preview_View {
    use Html_Event_Binding,Bootstrap_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $cssMap = parent::css_map();
        unset($cssMap['foregroundTheme']);
        $cssMap['-'] = 'progress';
        return $cssMap;
    }
    protected function style_map()
    {
        $styleArray = parent::style_map();
        unset($styleArray['color']);
        return $styleArray;
    }

    private function bar_css() {
        $css = ['progress-bar'];
        if (@$this->data['meta']['css']['foregroundTheme']){
            $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['foregroundTheme']];
        }
        if (@$this->data['meta']['custom']['striped']){
            $css[] = "progress-bar-striped";
        }
        if (@$this->data['meta']['custom']['animatedStrip']){
            $css[] = "progress-bar-animated";
        }
        return join(' ', $css);
    }

    private function bar_style() {
        $value = $this->data['meta']['value']?:50;
        $style = ["width: {$value}%"];
        $styleMap = parent::style_map();
        if ($styleMap['color']){
            $style[] = "background-color:".$this->data['meta']['style']['color']." !important";
        }
        return join(';', $style);
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";

        echo $this->indent(1).'<div';
        echo $this->wrap_output('class', $this->bar_css());
        echo ' role="progressbar"';
        echo $this->wrap_output('style', $this->bar_style());

        $value = $this->data['meta']['value']?:50;
        echo ' aria-valuenow="'.$value.'"';
        echo ' aria-valuemin="'.(@$this->data['meta']['custom']['min'] ?? 0).'"';
        echo ' aria-valuemax="'.(@$this->data['meta']['custom']['max'] ?? 100)."\">\r\n";

        if (@$this->data['meta']['custom']['label']){
            echo $this->indent(2)."{$value}%\r\n";
        }
        echo $this->indent(1)."</div>\r\n";
        echo "{$space}</div>\r\n";
    }
}
