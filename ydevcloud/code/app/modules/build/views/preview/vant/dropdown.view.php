<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Dropdown_View extends Preview_View {
    use  Vant_Popup,Html_Code_Helper,Alpine {
        Alpine::build_code as alpineBuildCode;
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
    function build_code(): Base_Code_Fragment
    {
        $this->alpineBuildCode();
        $codeFragment = $this->get_code_Fragment();
        $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid() . 'MenuVisible: false,');
        if ($this->data['meta']['custom']['direction']==='dropup'){
            $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid() . 'Bottom: "",');
        }else{
            $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid() . 'Top: "",');
        }

        $openMenuCodes = [];
        $openMenuCodes[] = 'open_'.$this->myid().'_Menu(el){';
        $openMenuCodes[] = $this->indent(1, true).'if(this.'.$this->myid() . 'MenuVisible) { this.'.$this->myid() . 'MenuVisible = false; return;}';
        if ($this->data['meta']['custom']['direction']==='dropup'){
            $openMenuCodes[] = $this->indent(1, true).'this.'.$this->myid() . 'Bottom = "bottom:" + el.getBoundingClientRect().top + "px"';
        }else{
            $openMenuCodes[] = $this->indent(1, true).'this.'.$this->myid() . 'Top = "top:" + (el.getBoundingClientRect().top + el.getBoundingClientRect().height) + "px"';
        }
        $openMenuCodes[] = $this->indent(1, true).'this.'.$this->myid() . 'MenuVisible = true';
        $openMenuCodes[] = '},';
        $codeFragment->add_code(Html_Code_Fragment::SECTION_EVENT, $openMenuCodes);
        return $codeFragment;
    }

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "name"=> 'Sample 1', "value"=> '#', 'type'=>'action' ], [ "name"=> 'Sample 2', "value"=> '#', 'type'=>'action'  ]];
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1).'<div'.$this->wrap_output(':class', $this->bar_class()).$this->wrap_output('style', $this->bar_style()).'>'.PHP_EOL;
        echo $this->indent(2).'<div role="button" class="van-dropdown-menu__item" @click="open_'.$this->myid().'_Menu($el)">'.PHP_EOL;
        echo $this->indent(3).'<span '.$this->wrap_output(':class', $this->menu_class()).'>'.PHP_EOL;
        echo $this->indent(4).'<div '.$this->wrap_output('class', $this->foreground_class()).$this->wrap_output('style', $this->foreground_style()).'>'.($this->data['meta']['title']?:'Dropdown').'</div>'.PHP_EOL;
        echo $this->indent(3).'</span>'.PHP_EOL;
        echo $this->indent(2).'</div>'.PHP_EOL;
        echo $this->indent(1).'</div>'.PHP_EOL;

        #  弹出菜单
        echo $this->indent(1).'<div x-show="'.$this->myid().'MenuVisible">'.PHP_EOL;
        if ($this->data['meta']['custom']['direction']==='dropup'){
            echo $this->indent(2).'<div class="van-dropdown-item van-dropdown-item--up" :style="'.$this->myid().'Bottom">'.PHP_EOL;
        }else{
            echo $this->indent(2).'<div class="van-dropdown-item van-dropdown-item--down" :style="'.$this->myid().'Top">'.PHP_EOL;
        }
        echo $this->indent(3).'<div class="van-overlay" @click="'.$this->myid() . 'MenuVisible=false" style="z-index: 2005; position: absolute; animation-duration: 0.2s;"></div>'.PHP_EOL;
        echo $this->indent(3).'<div class="van-popup '.($this->data['meta']['custom']['direction']==='dropup' ? 'van-popup--bottom' : 'van-popup--top').' van-dropdown-item__content" style="transition-duration: 0.2s; z-index: 2006;">'.PHP_EOL;

        foreach ($values as $value){
            if ($value['type']=='header'){
                echo $this->indent(4).'<div role="button" tabindex="0" class="van-cell">'.PHP_EOL;
                echo $this->indent(5).'<div class="van-cell__title van-text-muted"><span>'.$value['text'].'</span></div>'.PHP_EOL;
                echo $this->indent(4).'</div>'.PHP_EOL;
                continue;
            } else if ($value['type']=='divider'){
                echo $this->indent(4).'<div>&nbsp;</div>'.PHP_EOL;
                continue;
            } else if ($value['type']=='text'){
                echo $this->indent(4).'<div class="van-cell">'.$value['text'].'</div>'.PHP_EOL;
                continue;
            }
            echo $this->indent(4).'<div role="button" tabindex="0" @click="'.$this->myid() . 'MenuVisible=false" class="van-cell van-cell--clickable van-dropdown-item__option '.($value['checked'] ? 'van-dropdown-item__option--active' : '').'">'.PHP_EOL;
            echo $this->indent(5).'<div class="van-cell__title"><span>'.$value['text'].'</span></div>'.PHP_EOL;
            if ($value['checked']){
                echo $this->indent(5).'<div class="van-cell__value"><i class="van-icon van-icon-success van-dropdown-item__icon"></i></div>'.PHP_EOL;
            }
            echo $this->indent(4).'</div>'.PHP_EOL;

        }

        echo $this->indent(3).'</div>'.PHP_EOL;
        echo $this->indent(2).'</div>'.PHP_EOL;
        echo $this->indent(1).'</div>'.PHP_EOL;

        echo "{$space}</div>".PHP_EOL;
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
        $_ = "{'van-dropdown-menu__title':true,";
        if ($this->data['meta']['custom']['direction']==='dropup') {
            $_ .= "'van-dropdown-menu__title--up van-dropdown-menu__title--active':".$this->myid() . "MenuVisible, 'van-dropdown-menu__title--down':!".$this->myid() . "MenuVisible";
        }else{
            $_ .= "'van-dropdown-menu__title--down van-dropdown-menu__title--active':".$this->myid() . "MenuVisible, 'van-dropdown-menu__title--up':!".$this->myid() . "MenuVisible";
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
        $_ = ['van-dropdown-menu__bar'];
        $parentUI = $this->get_parent_UI();
        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['navbar','nav']);
        $dropdownMeta = $parentIsNavbar ? $parentUI['meta'] : $this->data['meta'];

        $myBackgruondTheme = $this->data['meta']['css']['backgroundTheme']!='default' ? $this->data['meta']['css']['backgroundTheme'] : '';
        $myBackgruondTheme = $myBackgruondTheme ?: $dropdownMeta['css']['backgroundTheme'];
        if ($myBackgruondTheme != 'default') $_[] = $this->cssTranslate['backgroundTheme'][$myBackgruondTheme];
        return "{'".join(' ', $_)."': true,'van-dropdown-menu__bar--opened':".$this->myid() . "MenuVisible}";
    }
}
