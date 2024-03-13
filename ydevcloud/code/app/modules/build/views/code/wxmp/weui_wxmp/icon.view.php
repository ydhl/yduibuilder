<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Icon_View as Preview_Icon_View;

class Icon_View extends Preview_Icon_View {
    use Wxmp;

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<view";
        echo $this->build_main_attrs();
        echo "></view>\r\n";
    }
}
