<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Nav_View extends Preview_View {
    use Vant_Popup,Html_Code_Helper;
    private function item_css($item) {
        $css = ["van-tab van-tab--card"];
        $style = $this->data['meta']['style'];
        $foretheme = $this->data['meta']['css']['foregroundTheme'];
        $backTheme = $this->data['meta']['css']['backgroundTheme'];

        if ($item['checked']){
            if ($style['color']) return join(' ', $css);
            if (!$foretheme || $foretheme=='default'){
                $css[] = 'van-tab--active';
            }else{
                $css[] = 'van-text-white';
                $css[] = $this->cssTranslate['backgroundTheme'][$foretheme];
            }
            return join(' ', $css);
        }

        if (!$style['color'] && $foretheme && $foretheme!='default'){
            $css[] = $this->cssTranslate['foregroundTheme'][$foretheme];
        }
        if (!$style['background-color'] && $backTheme && $backTheme!='default'){
            $css[] = $this->cssTranslate['backgroundTheme'][$backTheme];
        }
        return join(' ', $css);
    }
    private function item_style($item) {
        $style = $this->data['meta']['style'];
        $_ = [];

        if ($item['checked']){
            return ($style['color'] ? "background-color:".$style['color']." !important;color:#fff;" : '').$this->border_style();
        }
        if ($style['color']){
            $_[] = "color:".$style['color']." !important;";
        }
        if($style['background-color']){
            $_[] = "background-color:".$style['background-color']." !important;";
        }
        $_[] = $this->border_style();
        return join(' ', $_);
    }
    private function border_style(){
        $style = $this->data['meta']['style'];
        $foretheme = $this->data['meta']['css']['foregroundTheme'];

        if ($style['color']) return "border-color:".$style['color'];

        if (!$foretheme || $foretheme == 'default') return '';
        return "border-color:".$this->cssTranslate['themeColor'][$foretheme];
    }
    protected function css_map()
    {
        $cssMap = parent::css_map();
        unset($cssMap['foregroundTheme']);
        unset($cssMap['backgroundTheme']);
        $arr = ['van-tabs van-tabs--card'];

        $cssMap['-'] = join(' ', $arr);
        return $cssMap;
    }
    protected function style_map($meta = null, $state = 'normal')
    {
        $map = parent::style_map($meta, $state);
        unset($map['background-color']);
        return $map;
    }

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "name"=> 'Sample 1', "value"=> '#' ], [ "name"=> 'Sample 2', "value"=> '#', 'checked'=> true ]];
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1).'<div class="van-tabs__wrap">'.PHP_EOL;
        echo $this->indent(2).'<div class="van-tabs__nav van-tabs__nav--card"';
        echo $this->wrap_output('style', $this->border_style());
        echo '>'.PHP_EOL;

        foreach ((array)@$values as $item){
            echo $this->indent(3) . "<div";
            echo $this->wrap_output('class', $this->item_css($item));
            echo $this->wrap_output('style', $this->item_style($item));
            ">".PHP_EOL;
            echo $this->indent(4) . '<span class="van-tab__text van-tab__text--ellipsis">';
            echo $item['text']."</span>".PHP_EOL;
            echo $this->indent(3) . "</div>".PHP_EOL;
        }
        foreach ((array)@$this->childViews as $view){
            $view->output();
        }
        echo $this->indent(2).'</div>'.PHP_EOL;
        echo $this->indent(1).'</div>'.PHP_EOL;
    }
}
