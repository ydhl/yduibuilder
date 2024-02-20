<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;

class Text_View extends Preview_View {
    use Html_Event_Binding,Bootstrap_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $map = parent::css_map();
        $css = [];
        if (@$this->data['meta']['custom']['align']){
            $css[] = 'text-' . $this->data['meta']['custom']['align'];
        }
        if (@$this->data['meta']['custom']['italic']){
            $css[] = 'font-italic';
        }
        if (@$this->data['meta']['custom']['bold']){
            $css[] = 'font-weight-'.strtolower($this->data['meta']['custom']['bold']);
        }
        $map['-'] = join(' ', $css);
        return $map;
    }

    protected function style_map()
    {
        $style = parent::style_map();
        return $style;
    }


    public function build_ui()
    {
        $type = strtolower(@$this->data['meta']['custom']['type'] ?: 'div');

        $space =  $this->indent();
        echo "{$space}<{$type}";
        echo $this->build_main_attrs();
        echo ">\r\n";
        echo $this->indent(1);
        echo strlen($this->data['meta']['value']) ? $this->data['meta']['value'] : @$this->data['meta']['title'];
        echo "\r\n{$space}</{$type}>\r\n";
    }
}
