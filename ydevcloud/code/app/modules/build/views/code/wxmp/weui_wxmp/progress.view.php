<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Progress_View as Preview_Progress_View;

class Progress_View extends Preview_Progress_View {
    use Wxmp;

    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $style['[data-uiid=' . $this->myId() . '-bar]'] = $this->theme_style();
        return $style;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $value = $this->data['meta']['value']?:50;
        echo "{$space}<view";
        echo $this->build_main_attrs();
        echo ">\r\n";

        echo $this->indent(1).'<view';
        echo $this->wrap_output('id', $this->myId().'-bar');
        echo $this->wrap_output('data-uiid', $this->myId().'-bar');
        echo $this->wrap_output('class', $this->theme_css());
        echo ">\r\n";

        if (@$this->data['meta']['custom']['label']){
            echo $this->indent(2)."{$value}%\r\n";
        }
        echo $this->indent(1)."</view>\r\n";
        echo "{$space}</view>\r\n";
    }
}
