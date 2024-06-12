<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Image_View extends Preview_View {
    use Bootstrap_Popup,Html_Code_Helper;

    protected function output_as_prop($outputAs,$outputData){
        if (!strcasecmp($outputAs,'value')){
            return ":src";
        }
        return parent::output_as_prop($outputAs, $outputData);
    }
    public function build_ui()
    {
        $space =  $this->indent();
        $imgSrc = @$this->data['meta']['value']?:'/uibuilder.jpg';
        $imgSrc = $this->get_Img_Src($imgSrc);
        echo "{$space}<img";
        echo $this->build_main_attrs();
        echo ' alt="'.(@$this->data['meta']['title']).'"';
        echo ' src="'.($imgSrc).'"';
        echo "/>".PHP_EOL;
    }
}
