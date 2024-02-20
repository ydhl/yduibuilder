<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;

class Richtext_View extends Preview_View {
    use Html_Event_Binding,Bootstrap_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $map = parent::css_map();
        $map['-'] = 'editor-content-view';
        return $map;
    }
    public function build_ui()
    {

        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";
        echo $this->indent(1);
        echo @$this->data['meta']['value'] ?: @$this->data['meta']['title'];
        echo "\r\n{$space}</div>\r\n";
    }
}
