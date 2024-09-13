<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Richtext_View extends Preview_View {
    use Weui_Popup, Html_Code_Helper;
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
        echo ">";
        echo @$this->data['meta']['value'] ?: @$this->data['meta']['title'];
        echo "</div>".PHP_EOL;
    }
}
