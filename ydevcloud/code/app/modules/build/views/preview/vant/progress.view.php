<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\bootstrap\Progress_View as Bootstrap_Progress_View;


class Progress_View extends Bootstrap_Progress_View {
    protected $cssPrefix = 'van-';

    protected function bar_css() {
        $css[] = parent::bar_css();
        $index = array_search('progress-bar', $css);
        if ($index!==false) {
            unset($css[$index]);
        }
        $css[] = 'van-progress__portion';
        return $css;
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
        echo $this->wrap_output('role', 'progressbar');
        echo $this->wrap_output('class', $this->bar_css());
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
