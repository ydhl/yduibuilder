<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Progress_View extends Preview_View {
    use Vant_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $cssMap = parent::css_map();
        unset($cssMap['foregroundTheme']);
        $cssMap['-'] = 'van-progress';
        return $cssMap;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta);
        unset($styleArray['color']);
        return $styleArray;
    }

    private function bar_css() {
        $css = ['van-progress__portion'];
        if (@$this->data['meta']['css']['foregroundTheme']){
            $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['foregroundTheme']];
        }
        if (@$this->data['meta']['custom']['striped']){
            $css[] = "van-progress-bar-striped";
        }
        if (@$this->data['meta']['custom']['animatedStrip']){
            $css[] = "van-progress-bar-animated";
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

    private function label_css() {
        $css = ['van-progress__pivot'];
        if (@$this->data['meta']['css']['foregroundTheme']){
            $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['foregroundTheme']];
        }
        return join(' ', $css);
    }

    private function label_style() {
        $value = $this->data['meta']['value']?:50;
        $style = ["left: {$value}%;transform: translate(-50%, -50%);"];
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
        echo ">".PHP_EOL;

        echo $this->indent(1).'<span';
        echo $this->wrap_output('class', $this->bar_css());
        echo ' role="progressbar"';
        echo $this->wrap_output('style', $this->bar_style());

        $value = $this->data['meta']['value']?:50;
        echo ' aria-valuenow="'.$value.'"';
        echo ' aria-valuemin="'.(@$this->data['meta']['custom']['min'] ?? 0).'"';
        echo ' aria-valuemax="'.(@$this->data['meta']['custom']['max'] ?? 100)."\">".PHP_EOL;
        echo $this->indent(1)."</span>".PHP_EOL;
        if (@$this->data['meta']['custom']['label']){
            echo $this->indent(1).'<span';
            echo $this->wrap_output('class', $this->label_css());
            echo $this->wrap_output('style', $this->label_style());
            echo "\">".PHP_EOL;
            echo $this->indent(2)."{$value}%".PHP_EOL;
            echo $this->indent(1).'</span>';
        }
        echo "{$space}</div>".PHP_EOL;
    }
}
