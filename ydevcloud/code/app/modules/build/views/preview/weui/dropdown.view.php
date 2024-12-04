<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\ValueList_View;

class Dropdown_View extends ValueList_View {
    use  Weui_Popup,Html_Code_Helper,Alpine {
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
        $this->output_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1).'<span '.$this->wrap_output(':class', $this->menu_class()).'>'.PHP_EOL;
        echo $this->indent(2).'<div';
        echo $this->wrap_output('@click', "toggle_{$myid}_Menu()");
        if ($iteratorName){
            echo $this->wrap_output('x-text', "alpinejs_checked_name(\$el, {$iteratorName}, '{$inputDataName}') || '{$name}'");
        }else{
            echo $this->wrap_output('x-text', "alpinejs_checked_name(\$el, {$myid}_values(), '{$inputDataName}') || '{$name}'");
        }
        echo '></div>'.PHP_EOL;
        echo $this->indent(1).'</span>'.PHP_EOL;

        #  弹出菜单
        echo $this->indent(1).'<div x-show="alpinejs_get_value($el, \''.$myid.'MenuVisible'.$suffix.'\')">'.PHP_EOL;
        echo $this->indent(2).'<div class="weui-dropdown-body" :style="'.$this->myid().'dropStyle">'.PHP_EOL;

        echo $this->indent(3).'<div class="weui-dropdown-overlay"'
            .$this->wrap_output('@click', "toggle_{$myid}_Menu()")
            .'></div>'.PHP_EOL;
        echo $this->indent(3).'<div :class="'.$myid.'_menu_css">'.PHP_EOL;
        echo $this->indent(4).'<div class="weiui-cells">'.PHP_EOL;

    }
    protected function build_ui_end()
    {
        echo $this->indent(4).'</div>'.PHP_EOL;
        echo $this->indent(3).'</div>'.PHP_EOL;
        echo $this->indent(2).'</div>'.PHP_EOL;
        echo $this->indent(1).'</div>'.PHP_EOL;
        echo $this->indent()."</div>".PHP_EOL;

    }
    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex = null, $iteratorName='')
    {
        $myid = $this->myid();
        list('name'=>$xText, 'value'=>$xValue, 'checked'=>$checked, 'data'=>$boundData) = $this->get_bind_name_value($outputData, $itemName);
        if (!$outputData){
            $staticValue = strlen($staticData['value'])?$staticData['value']:$staticData['name'];
            $xText = "'{$staticData['name']}'";
            $xValue = "'{$staticValue}'";
        }

        $inputDataName = $this->get_input_data_name($isArr);
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;
        $suffix = $isArr?'[-1]':'';

        if (!$outputData){
            if ($staticData['type']=='header'){
                echo $this->indent(4).'</div>'.PHP_EOL;
                echo $this->indent(4).'<div class="weui-cells__title">'.$staticData['text'].'</div>'.PHP_EOL;
                echo $this->indent(4).'<div class="weui-cells">'.PHP_EOL;
            } else if ($staticData['type']=='divider'){
                echo $this->indent(4).'</div>'.PHP_EOL;
                echo $this->indent(4).'<div class="weui-cells">'.PHP_EOL;
            } else if ($staticData['type']=='text'){
                echo $this->indent(4).'<div class="weui-cell">'.$staticData['text'].'</div>'.PHP_EOL;
            }else{
                echo $this->indent(5).'<div role="button"'
                    .$this->wrap_output('@click', "toggle_{$myid}_Menu()")
                    .$this->wrap_output('data-value', $staticValue)
                    .$this->wrap_output('data-root', $myid)
                    .$this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null)
                    .$this->wrap_output('class', "weui-cell  weui-cell_active")
                    .'>'.PHP_EOL;
                echo $this->indent(6).'<span class="weui-cell__bd"><span>'.$staticData['text'].'</span></span>'.PHP_EOL;
                echo $this->indent(6).'<div'
                    .$this->wrap_output('x-show', "{$inputDataNameString} == $xValue")
                    .'class="weui-cell__ft"><span class="weui-dropdown-checked"></span></div>'.PHP_EOL;
                echo $this->indent(5).'</div>'.PHP_EOL;
            }
            return;
        }

        echo $this->indent(4).'<div role="button"'
            .$this->wrap_output('@click', "alpinejs_set_value(\$el, '{$myid}MenuVisible{$suffix}', false)")
            .$this->wrap_output('data-value', $xValue)
            .$this->wrap_output('data-bound', $boundData)
            .$this->wrap_output('data-root', $myid)
            .$this->wrap_output(':data-default', $checked ? "{$checked} ? $xValue : ''" : null)
            .$this->wrap_output('class', "weui-cell  weui-cell_active")
            .'>'.PHP_EOL;
        echo $this->indent(5).'<div class="weui-cell__bd"><span x-text="'.$xText.'"></span></div>'.PHP_EOL;
        echo $this->indent(5).'<div'
            .$this->wrap_output('x-show', "{$inputDataNameString} == $xValue")
            .'class="weui-cell__ft"><span class="weui-dropdown-checked"></span></div>'.PHP_EOL;
        echo $this->indent(4).'</div>'.PHP_EOL;
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
        $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $myid . '_menu_css: "weui-dropdown-menu",');

        $openMenu = <<<OPEN_MENU
toggle_{$myid}_Menu(){
    const dropdown = document.querySelector(`[data-uiid='{$myid}']`)
    if(this.alpinejs_get_value(dropdown, '{$myid}MenuVisible{$suffix}')) {
        this.alpinejs_set_value(dropdown, '{$myid}MenuVisible{$suffix}', false);
        document.body.classList.remove('d-overflow-hidden')
        document.body.removeEventListener('click', (event) => this.close_{$myid}_Menu(event))
        return;
    }
    var style = window.getComputedStyle(dropdown);  
    const top = dropdown.getBoundingClientRect().top
    const height = (parseInt(style.height, 10) || 0) + (parseInt(style.marginTop, 10) || 0) + (parseInt(style.paddingTop, 10) || 0)
        + (parseInt(style.marginBottom, 10) || 0) + (parseInt(style.paddingBottom, 10) || 0)

    // 判断弹出位置
    if (top > window.innerHeight / 2){// 向上弹出
        this.{$myid}dropStyle = "top:0px;bottom:" + (window.innerHeight - top) + "px";
        this.{$myid}_menu_css = "weui-dropdown-menu weui-dropdown-menu--bottom";
    }else{// 向下弹出
        this.{$myid}dropStyle = "bottom:0px;top:" + (top + height) + "px";
        this.{$myid}_menu_css = "weui-dropdown-menu weui-dropdown-menu--top";
    }
    document.body.classList.add('d-overflow-hidden')
    document.body.addEventListener('click', (event) => this.close_{$myid}_Menu(event))
    this.alpinejs_set_value(dropdown, '{$myid}MenuVisible{$suffix}', true)
},
close_{$myid}_Menu(event){
    const dropdown = event.target.closest('[data-uiid]')
    const myid = dropdown?.dataset?.uiid
    if(myid=="{$myid}") return;
    if(this.alpinejs_get_value(dropdown, '{$myid}MenuVisible{$suffix}')){
        this.alpinejs_set_value(dropdown, '{$myid}MenuVisible{$suffix}', false)
        document.body.removeEventListener('click', (event) => this.close_{$myid}_Menu(event))
    }
},
OPEN_MENU;
        $codeFragment->add_code(Html_Code_Fragment::SECTION_EVENT, $this->build->indent_code(0, $openMenu));
        return $codeFragment;
    }

    protected function css_map()
    {
        $map = parent::css_map();
        if ($this->data['meta']['style']['color']){
            unset($map['foregroundTheme']);
        }
        if ($this->data['meta']['style']['background-color']){
            unset($map['backgroundTheme']);
        }

        $map['-'] = 'weui-dropdown';
        return $map;
    }
    private function menu_class() {
        $inputDataName = $this->get_input_data_name($isArr);
        $suffix = $isArr?'[-1]':'';
        $myid = $this->myid();
        $_ = "{'weui-dropdown-title':true,";
        $_ .= "'weui-dropdown-title--active': alpinejs_get_value(\$el, '{$myid}MenuVisible{$suffix}')";
        return $_.'}';
    }
}
