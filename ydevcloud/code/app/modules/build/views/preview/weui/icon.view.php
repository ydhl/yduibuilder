<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Icon_View extends Preview_View {
    use Weui_Popup,Html_Code_Helper;

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<i";
        echo $this->build_main_attrs();
        echo "></i>".PHP_EOL;
    }

    protected function css_map()
    {
        $css =  parent::css_map();
        $css['icon'] = $this->data['meta']['custom']['icon'];
        if ($this->data['meta']['style']['color']) unset($css['foregroundTheme']);
        if ($this->data['meta']['style']['background-color']) unset($css['backgroundTheme']);
        return $css;
    }

    protected function style_map($meta = null, $state = 'normal')
    {
        $map = parent::style_map($meta, $state);
        if ($map['font-size']){
            $map['font-size'] = $map['font-size'].' !important';
        }
        return $map;
    }

    protected function output_as_prop($outputAs, $outputData){
        if (!strcasecmp($outputAs,'value')){
            return ':class';
        }
        return parent::output_as_prop($outputAs, $outputData);
    }
}
