<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Dropdown_View extends Preview_View {
    use  Vant_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $map = parent::css_map();
        unset($map['backgroundTheme'],$map['foregroundTheme']);
        $map['-'] = 'van-dropdown-menu';
        return $map;
    }
    protected function style_map($meta = null, $state = 'normal')
    {
        $map = parent::style_map($meta, $state);
        unset($map['background-color'],$map['color']);
        return $map;
    }

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '#', 'type'=>'action' ], [ "text"=> 'Sample 2', "value"=> '#', 'type'=>'action'  ]];
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1).'<div'.$this->wrap_output('class', $this->bar_class()).$this->wrap_output('style', $this->bar_style()).'>'.PHP_EOL;
        echo $this->indent(2).'<div role="button" class="van-dropdown-menu__item">'.PHP_EOL;
        echo $this->indent(3).'<span '.$this->wrap_output('class', $this->menu_class()).'>'.PHP_EOL;
        echo $this->indent(4).'<div '.$this->wrap_output('class', $this->force_class()).$this->wrap_output('style', $this->force_style()).'>'.($this->data['meta']['title']?:'Dropdown').'</div>'.PHP_EOL;
        echo $this->indent(3).'</span>'.PHP_EOL;
        echo $this->indent(2).'</div>'.PHP_EOL;
        echo $this->indent(1).'</div>'.PHP_EOL;

        echo "{$space}</div>".PHP_EOL;
    }
    private function force_style() {
        $_ = [''];
        $parentUI = $this->get_parent_UI();
        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['navbar','nav']);
        $dropdownMeta = $parentIsNavbar ? $parentUI['meta'] : $this->data['meta'];

        $color = $this->data['meta']['style']['color'] ?:  $dropdownMeta['meta']['style']['color'];
        if ($color) $_[] = "color: {$color}";
        return join(';', $_);
    }
    private function force_class() {
        $_ = ['van-ellipsis'];
        $parentUI = $this->get_parent_UI();
        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['navbar','nav']);
        $dropdownMeta = $parentIsNavbar ? $parentUI['meta'] : $this->data['meta'];

        $foregroundTheme = $this->data['meta']['css']['foregroundTheme']!='default' ? $this->data['meta']['css']['foregroundTheme'] : '';
        $foregroundTheme = $foregroundTheme ?: $dropdownMeta['css']['foregroundTheme'];
        if($foregroundTheme != 'default') $_[] = $this->cssTranslate['foregroundTheme'][$foregroundTheme];
        return join(' ', $_);
    }
    private function menu_class() {
        $_ = ['van-dropdown-menu__title'];
        if ($this->data['meta']['custom']['direction']==='dropup') $_[] = "van-dropdown-menu__title--down";
        return join(' ', $_);
    }
    private function bar_style() {
        $_ = [''];
        $parentUI = $this->get_parent_UI();
        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['navbar','nav']);
        $dropdownMeta = $parentIsNavbar ? $parentUI['meta'] : $this->data['meta'];

        $backgroundColor = $this->data['meta']['style']['background-color'] ?:  $dropdownMeta['meta']['style']['background-color'];
        if ($backgroundColor) $_[] = "background-color: {$backgroundColor}";
        return join(';', $_);
    }
    private function bar_class() {
        $_ = ['van-dropdown-menu__bar'];
        $parentUI = $this->get_parent_UI();
        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['navbar','nav']);
        $dropdownMeta = $parentIsNavbar ? $parentUI['meta'] : $this->data['meta'];

        $myBackgruondTheme = $this->data['meta']['css']['backgroundTheme']!='default' ? $this->data['meta']['css']['backgroundTheme'] : '';
        $myBackgruondTheme = $myBackgruondTheme ?: $dropdownMeta['css']['backgroundTheme'];
        if ($myBackgruondTheme != 'default') $_[] = $this->cssTranslate['backgroundTheme'][$myBackgruondTheme];
        return join(' ', $_);
    }
}
