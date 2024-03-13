<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\Preview_View;

class Page_View extends Preview_View {
    use Wxmp;
    protected function style_map()
    {
        $map = parent::style_map();
        $map['flex-grow']= 'flex-grow:1'; # 17502
        return $map;
    }

    public function build_ui()
    {
        echo $this->indent();
        echo "<view";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ($this->childViews as $view){
            $view->output();
        }

        echo $this->indent();
        echo "</view>";
    }
}
