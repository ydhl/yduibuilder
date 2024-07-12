<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\ValueList_View;

class Dropdown_View extends ValueList_View {
    use  Vant_Popup,Html_Code_Helper,Alpine {
        Alpine::build_code as alpineBuildCode;
    }
    protected function build_ui_begin($iteratorName = null)
    {
        $space =  $this->indent();
        $myid = $this->myid();
        $inputDataName = $this->get_input_data_name($isArr);
        $name = $this->data['meta']['title']?:'Dropdown';
        $suffix = $isArr?'[-1]':'';
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1).'<div'.$this->wrap_output(':class', $this->bar_class()).$this->wrap_output('style', $this->bar_style()).'>'.PHP_EOL;
        echo $this->indent(2).'<div role="button" class="van-dropdown-menu__item" @click="open_'.$this->myid().'_Menu($el)">'.PHP_EOL;
        echo $this->indent(3).'<span '.$this->wrap_output(':class', $this->menu_class()).'>'.PHP_EOL;
        echo $this->indent(4).'<div '.$this->wrap_output('class', $this->foreground_class())
            .$this->wrap_output('style', $this->foreground_style());
        if ($iteratorName){
            echo $this->wrap_output('x-text', "alpinejs_checked_name(\$el, {$iteratorName}, '{$inputDataName}') || '{$name}'");
        }else{
            echo $this->wrap_output('x-text', "alpinejs_checked_name(\$el, {$myid}_values(), '{$inputDataName}') || '{$name}'");
        }
        echo '></div>'.PHP_EOL;
        echo $this->indent(3).'</span>'.PHP_EOL;
        echo $this->indent(2).'</div>'.PHP_EOL;
        echo $this->indent(1).'</div>'.PHP_EOL;
        echo "{$space}</div>".PHP_EOL;


        #  弹出菜单
        echo $this->indent().'<div x-show="alpinejs_get_value($el, \''.$myid.'MenuVisible'.$suffix.'\')">'.PHP_EOL;
        if ($this->data['meta']['custom']['direction']==='dropup'){
            echo $this->indent(1).'<div class="van-dropdown-item van-dropdown-item--up" :style="'.$this->myid().'dropStyle">'.PHP_EOL;
        }else{
            echo $this->indent(1).'<div class="van-dropdown-item van-dropdown-item--down" :style="'.$this->myid().'dropStyle">'.PHP_EOL;
        }
        echo $this->indent(2).'<div class="van-overlay" @click="alpinejs_set_value($el, \''.$myid.'MenuVisible'.$suffix.'\', false)" style="z-index: 2005; position: absolute; animation-duration: 0.2s;"></div>'.PHP_EOL;
        echo $this->indent(2).'<div class="van-popup '.($this->data['meta']['custom']['direction']==='dropup' ? 'van-popup--bottom' : 'van-popup--top').' van-dropdown-item__content" style="transition-duration: 0.2s; z-index: 2006;">'.PHP_EOL;

    }
    protected function build_ui_end()
    {
        echo $this->indent(2).'</div>'.PHP_EOL;
        echo $this->indent(1).'</div>'.PHP_EOL;
        echo $this->indent().'</div>'.PHP_EOL;

    }
    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex = null)
    {
        $myid = $this->myid();
        list('name'=>$xText, 'value'=>$xValue, 'checked'=>$checked) = $this->get_bind_name_value($outputData, $itemName);
        if (!$outputData){
            $staticValue = $staticData['value']?:$staticData['name'];
            $xText = "'{$staticData['name']}'";
            $xValue = "'{$staticValue}'";
        }

        $inputDataName = $this->get_input_data_name($isArr);
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;
        $suffix = $isArr?'[-1]':'';

        if (!$outputData){
            if ($staticData['type']=='header'){
                echo $this->indent(3).'<div role="button" tabindex="0" class="van-cell">'.PHP_EOL;
                echo $this->indent(4).'<div class="van-cell__title van-text-muted"><span>'.$staticData['text'].'</span></div>'.PHP_EOL;
                echo $this->indent(3).'</div>'.PHP_EOL;
            } else if ($staticData['type']=='divider'){
                echo $this->indent(3).'<div>&nbsp;</div>'.PHP_EOL;
            } else if ($staticData['type']=='text'){
                echo $this->indent(3).'<div class="van-cell">'.$staticData['text'].'</div>'.PHP_EOL;
            }else{
                echo $this->indent(3).'<div role="button"'
                    .$this->wrap_output('@click', "alpinejs_set_value(\$el, '{$myid}MenuVisible{$suffix}', false)")
                    .$this->wrap_output('data-value', $staticValue)
                    .$this->wrap_output('data-root', $myid)
                    .$this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null)
                    .$this->wrap_output(':class', "{'van-cell van-cell--clickable van-dropdown-item__option': true, 'van-dropdown-item__option--active':{$inputDataNameString} == {$xValue}}")
                    .'>'.PHP_EOL;
                echo $this->indent(4).'<div class="van-cell__title"><span>'.$staticData['text'].'</span></div>'.PHP_EOL;
                echo $this->indent(4).'<div'
                    .$this->wrap_output('x-show', "{$inputDataNameString} == $xValue")
                    .'class="van-cell__value"><i class="van-icon van-icon-success van-dropdown-item__icon"></i></div>'.PHP_EOL;
                echo $this->indent(3).'</div>'.PHP_EOL;
            }
            return;
        }

        echo $this->indent(3).'<div role="button"'
            .$this->wrap_output('@click', "alpinejs_set_value(\$el, '{$myid}MenuVisible{$suffix}', false)")
            .$this->wrap_output('data-value', $xValue)
            .$this->wrap_output('data-root', $myid)
            .$this->wrap_output(':data-default', $checked ? "{$checked} ? $xValue : ''" : null)
            .$this->wrap_output('class', "{'van-cell van-cell--clickable van-dropdown-item__option': true, 'van-dropdown-item__option--active':{$inputDataNameString} == {$xValue}}")
            .'>'.PHP_EOL;
        echo $this->indent(4).'<div class="van-cell__title"><span x-text="'.$xText.'"></span></div>'.PHP_EOL;
        echo $this->indent(4).'<div'
            .$this->wrap_output('x-show', "{$inputDataNameString} == $xValue")
            .'class="van-cell__value"><i class="van-icon van-icon-success van-dropdown-item__icon"></i></div>'.PHP_EOL;
        echo $this->indent(3).'</div>'.PHP_EOL;
    }
    function build_code(): Base_Code_Fragment
    {
        $this->alpineBuildCode();
        $inputDataName = $this->get_input_data_name($isArr);
        $suffix = $isArr?'[-1]':'';
        $myid = $this->myid();
        $codeFragment = $this->get_code_Fragment();
        $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $myid . 'MenuVisible: '.($isArr?'[]':'false').',');
        $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $myid . 'dropStyle: "",');

        if ($this->data['meta']['custom']['direction']==='dropup'){
            $dropStyle = 'this.'.$myid . 'dropStyle = "bottom:0px"';
        }else{
            $dropStyle = 'this.'.$myid . 'dropStyle = "top:" + (el.getBoundingClientRect().top + el.getBoundingClientRect().height) + "px"';
        }
        $openMenu = <<<OPEN_MENU
open_{$myid}_Menu(el){
    if(this.alpinejs_get_value(el, '{$myid}MenuVisible{$suffix}')) {
        this.alpinejs_set_value(el, '{$myid}MenuVisible{$suffix}', false);
        return;
    }
    this.{$myid}dropStyle = {$dropStyle};
    this.alpinejs_set_value(el, '{$myid}MenuVisible{$suffix}', true)
},
OPEN_MENU;

        $codeFragment->add_code(Html_Code_Fragment::SECTION_EVENT, $this->build->indent_code(0, $openMenu));
        return $codeFragment;
    }

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

    private function foreground_style() {
        $_ = [''];
        $parentUI = $this->get_parent_UI();
        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['navbar','nav']);
        $dropdownMeta = $parentIsNavbar ? $parentUI['meta'] : $this->data['meta'];

        $color = $this->data['meta']['style']['color'] ?:  $dropdownMeta['meta']['style']['color'];
        if ($color) $_[] = "color: {$color} !important";
        return join(';', $_);
    }
    private function foreground_class() {
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
        $inputDataName = $this->get_input_data_name($isArr);
        $suffix = $isArr?'[-1]':'';
        $myid = $this->myid();
        $_ = "{'van-dropdown-menu__title':true,";
        if ($this->data['meta']['custom']['direction']==='dropup') {
            $_ .= "'van-dropdown-menu__title--up van-dropdown-menu__title--active':alpinejs_get_value(\$el, '{$myid}MenuVisible{$suffix}'), 'van-dropdown-menu__title--down':!alpinejs_get_value(\$el, '{$myid}MenuVisible{$suffix}')";
        }else{
            $_ .= "'van-dropdown-menu__title--down van-dropdown-menu__title--active':alpinejs_get_value(\$el, '{$myid}MenuVisible{$suffix}'), 'van-dropdown-menu__title--up':!alpinejs_get_value(\$el, '{$myid}MenuVisible{$suffix}')";
        }
        return $_.'}';
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
        $inputDataName = $this->get_input_data_name($isArr);
        $suffix = $isArr?'[-1]':'';
        $myid = $this->myid();
        $_ = ['van-dropdown-menu__bar'];
        $parentUI = $this->get_parent_UI();
        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['navbar','nav']);
        $dropdownMeta = $parentIsNavbar ? $parentUI['meta'] : $this->data['meta'];

        $myBackgruondTheme = $this->data['meta']['css']['backgroundTheme']!='default' ? $this->data['meta']['css']['backgroundTheme'] : '';
        $myBackgruondTheme = $myBackgruondTheme ?: $dropdownMeta['css']['backgroundTheme'];
        if ($myBackgruondTheme != 'default') $_[] = $this->cssTranslate['backgroundTheme'][$myBackgruondTheme];
        return "{'".join(' ', $_)."': true,'van-dropdown-menu__bar--opened':alpinejs_get_value(\$el, '{$myid}MenuVisible{$suffix}')}";
    }

}
