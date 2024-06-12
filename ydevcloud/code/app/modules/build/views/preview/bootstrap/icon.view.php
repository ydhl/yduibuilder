<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Icon_View extends Preview_View {
    use Bootstrap_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $css =  parent::css_map();
        $css['icon'] = $this->data['meta']['custom']['icon'];
        return $css;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<i";
        echo $this->build_main_attrs();
        echo "></i>".PHP_EOL;
    }

    protected function output_as_prop($outputAs, $outputData){
        if (!strcasecmp($outputAs,'value')){
            return ':class';
        }
        return parent::output_as_prop($outputAs, $outputData);
    }
}
