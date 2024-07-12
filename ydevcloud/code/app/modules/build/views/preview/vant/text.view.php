<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Text_View extends Preview_View {
    use Vant_Popup,Html_Code_Helper;

    public function build_ui()
    {
        $type = strtolower(@$this->data['meta']['custom']['type'] ?: 'div');

        $space =  $this->indent();
        echo "{$space}<{$type}";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;
        echo $this->indent(1);
        echo @$this->data['meta']['value'] ?: @$this->data['meta']['title'];
        echo PHP_EOL."{$space}</{$type}>".PHP_EOL;
    }
    protected function css_map()
    {
        $map = parent::css_map();
        $css = [];
        if (@$this->data['meta']['custom']['align']){
            $css[] = 'van-text-' . $this->data['meta']['custom']['align'];
        }
        if (@$this->data['meta']['custom']['italic']){
            $css[] = 'van-font-italic';
        }
        if (@$this->data['meta']['custom']['bold']){
            $css[] = 'van-font-weight-'.strtolower($this->data['meta']['custom']['bold']);
        }
        $map['-'] = join(' ', $css);
        return $map;
    }

}
