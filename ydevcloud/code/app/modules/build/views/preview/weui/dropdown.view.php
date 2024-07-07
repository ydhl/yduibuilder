<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Alpinejs_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\ValueList_View;

class Dropdown_View extends ValueList_View {
    use  Weui_Popup,Html_Code_Helper,Alpine {
        Alpine::build_code as alpineBuildCode;
    }
    protected function css_map()
    {
        $map = parent::css_map();
        $map['-'] = "weui-cell weui-cell_active weui-cell_access";
        if ($this->data['meta']['style']['color']) unset($map['foregroundTheme']);
        if ($this->data['meta']['style']['background-color']) unset($map['backgroundTheme']);
        return $map;
    }

    public function build_ui_static()
    {
        ob_start();
        $this->build_main_attrs();
        $attr = ob_get_clean();
        $myid = $this->myid();

        $staticMenus = [];
        $values = @$this->data['meta']['values']?:[[ "name"=> 'Sample 1', "value"=> '#', 'type'=>'action' ], [ "name"=> 'Sample 2', "value"=> '#', 'type'=>'action'  ]];
        foreach ((array)@$values as $item){
            if (@$item['type']=='action'){
                $staticMenus[] = $this->indent(1, true)."<div @click='{$myid}_menu_visible = false' data-value='{$item['value']}' class='weui-actionsheet__cell'>{$item['text']}</div>";
            }elseif (@$item['type']=='header'){
                $staticMenus[] = $this->indent(1, true)."<div class='weui-actionsheet__title'><p class='weui-actionsheet__title-text'>{$item['text']}</p></div>";
            }elseif (@$item['type']=='divider'){
                $staticMenus[] = $this->indent(1, true).'<div style="background-color: #efefef;height: 10px">&nbsp;</div>';
            }else{
                $staticMenus[] = $this->indent(1, true).'<div><p>'.$item['text'].'</p></div>';
            }
        }
        ob_start();
        $this->build->output_code($staticMenus, 4);
        $staticMenus = ob_get_clean();

        $value = $this->data['meta']['title'] ?: 'Dropdown';
        $html = <<<HTML
<div {$attr} @click="open_{$myid}_menu">
    <div class="weui-cell__bd">
      <p>{$value}</p>
    </div>
    <div class="weui-cell__ft"></div>
</div>
<template x-if="{$myid}_menu_visible">
<div style="opacity: 1;" tabindex="0">
    <div class="weui-mask weui-animate-fade-in" @click="{$myid}_menu_visible = false"></div>
    <div class="weui-half-screen-dialog weui-half-screen-dialog_slide weui-half-screen-dialog_show weui-animate-slide-up" style="transform: translate3d(0px, 0px, 0px);">
        <div class="weui-half-screen-dialog__hd">
          <div class="weui-half-screen-dialog__slide-icon" style="height: 4px; border-radius: 2px;">
            <i class="weui-icon-arrow" style="opacity: 0;"></i>
          </div>
        </div>
        <div class="weui-half-screen-dialog__bd">
          {$staticMenus}
        </div>
    </div>
</div>
</template>
HTML;
        echo $this->build->output_code($html, $this->build->get_indent());
    }

    protected function build_ui_2d_array($bindOutput, $outDataName)
    {
        // TODO: Implement build_ui_2d_array() method.
    }

    protected function build_ui_array($bindOutput, $outDataName)
    {
        // TODO: Implement build_ui_array() method.
    }
    public function build_code(): Base_Code_Fragment
    {
        $this->alpineBuildCode();
        $fragment = $this->get_code_Fragment();
        $myid = $this->myid();
        $openmenu = <<<OPEN_MENU
open_{$myid}_menu(){
    this.{$myid}_menu_visible = true
},

OPEN_MENU;

        $fragment->add_code(Alpinejs_Code_Fragment::SECTION_EVENT, $this->build->indent_code(1, $openmenu));
        return $fragment;
    }
}
