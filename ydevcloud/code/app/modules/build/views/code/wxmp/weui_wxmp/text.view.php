<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Text_View as Preview_Text_View;

class Text_View extends Preview_Text_View {
    use Wxmp;
    protected function css_map()
    {
        $map = parent::css_map();
        if (@$this->data['meta']['custom']['type'] && $this->data['meta']['custom']['type']!='span'){
            $map['-'] .= " ".$this->data['meta']['custom']['type'];
        }
        return $map;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<view";
        echo $this->build_main_attrs();
        echo ">\r\n";
        echo $this->indent(1);
        echo @$this->data['meta']['value'] ?: @$this->data['meta']['title'];
        echo "\r\n{$space}</view>\r\n";
    }
}
