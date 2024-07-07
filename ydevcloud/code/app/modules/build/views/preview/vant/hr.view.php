<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Hr_View extends Preview_View {
    use  Vant_Popup,Html_Code_Helper;
    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;
        if ($this->data['meta']['value']){
            echo $this->indent(1);
            echo $this->data['meta']['value'];
            echo PHP_EOL;
        }
        echo $this->indent(1);
        echo "</div>".PHP_EOL;
    }

    protected function css_map()
    {
        $map = parent::css_map();
        unset($map['backgroundTheme']);
        unset($map['foregroundTheme']);

        $meta = $this->data['meta'];
        $css = ['van-divider van-divider--hairline van-divider--content-center'];
        if ($meta['css']['backgroundTheme']){
            $css[] = "van-border-".$meta['css']['backgroundTheme'];
        }
        if ($meta['css']['foregroundTheme']){
            $css[] = "van-text-".$meta['css']['foregroundTheme'];
        }
        $map['-'] = join(' ', $css);
        return $map;
    }

    protected function style_map($meta=null, $state = 'normal')
    {
        $meta = $meta??$this->data['meta'];
        $styles = parent::style_map($meta, $state);
        unset($styles['height']);
        unset($styles['background-color']);

        if ($styles['color']) {
            $styles['color'].='!important';
        }
        if (!$meta['width']) {
            $styles['width'] = 'width:100%';
        }
        if ($meta['style']['background-color']) {
            $styles['border-color'] = 'border-color:'. $meta['style']['background-color'].'!important';
        }
        return $styles;
    }
}
