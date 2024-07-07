<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Richtext_View extends Preview_View {
    use Bootstrap_Popup,Html_Code_Helper;
    public function build_ui()
    {

        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;
        echo $this->indent(1);
        echo @$this->data['meta']['value'] ?: @$this->data['meta']['title'];
        echo PHP_EOL."{$space}</div>".PHP_EOL;
    }
    protected function css_map()
    {
        $map = parent::css_map();
        $map['-'] = 'editor-content-view';
        return $map;
    }
    protected function output_as_prop($outputAs, $outputData)
    {
        if (strtolower($outputAs) == 'value'){
            return "x-html";
        }
        return parent::output_as_prop($outputAs, $outputData);
    }
}
