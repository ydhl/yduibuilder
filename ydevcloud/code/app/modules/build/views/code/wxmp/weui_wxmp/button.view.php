<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Button_View as Preview_Button_View;

class Button_View extends Preview_Button_View {
    use Wxmp;


    public function build_ui()
    {
        $space =  $this->indent();
        $topIsModal =  strtolower($this->get_parent_UI()['type']) === 'modal';

        $tag = 'button';
        if ($topIsModal && $this->build->get_in_Parent_Placement()=='foot') { // 弹窗footer中的按钮
            $tag = 'view';
        }
        // 一般按钮
        echo $space;
        echo "<{$tag}";
        if (@$this->data['meta']['custom']['disabled']){
            echo ' disabled ';
        }
        echo $this->build_main_attrs().'>';
        $this->wrap_icon(function(){
            echo $this->data['meta']['title'] ?: $this->data['type'];
        }, null, 'view', 'view');
        echo "\r\n";
        echo $this->indent();
        echo "</{$tag}>\r\n";
    }
}
