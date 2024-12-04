<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\code\Io_Data_Fetch;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Text_View extends Preview_View {
    use Bootstrap_Popup,Html_Code_Helper;
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

    public function build_ui()
    {
        $type = strtolower(@$this->data['meta']['custom']['type'] ?: 'span');

        $space =  $this->indent();
        echo "{$space}<{$type}";
        echo $this->output_main_attrs();
        echo ">".$this->body_text()."</{$type}>".PHP_EOL;
    }
}
