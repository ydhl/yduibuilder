<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Container_View extends Preview_View {
    use Weui_Popup,Html_Code_Helper;
    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->output_main_attrs();
        echo ">".PHP_EOL;

        foreach ((array)@$this->childViews as $view){
            $view->output();
        }

        echo "{$space}</div>".PHP_EOL;
    }
    protected function style_map($meta = null, $state = 'normal')
    {
        $style = parent::style_map($meta, $state);
        if (!$this->data['items']){
            $style['min-height'] = "min-height:100px !important;";
            $style['min-width'] = "min-width:100px !important;";
        }
        return $style;
    }
}
