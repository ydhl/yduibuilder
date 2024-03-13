<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Navbar_View as Preview_Navbar_View;

class Navbar_View extends Preview_Navbar_View {
    use Wxmp;

    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $map = $this->data['meta']['style'];
        if (@$map['color']){
            $style['[data-uiid=' . $this->myId(). '] .text.active'] = 'color:' . $map['color'];
        }

        return $style;
    }

    protected function itemCss($item){
        $css = parent::itemCss($item);
        return $css." text ".(@$item['checked'] ? 'active' : "");
    }


    public function build_ui()
    {

        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '1' ], [ "text"=> 'Sample 2', "value"=> '2', "checked"=>true ]];
        $space =  $this->indent();
        echo "{$space}<view ";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ((array)@$values as $item){
            echo $this->indent(1) . "<view class='weui-tabbar__item'>\r\n";
            echo $this->indent(2) . "<view class='weui-tabbar_icon'>\r\n";
            if (@$item['image']){
                echo $this->indent(3) . "<image src='{$item['image']}' class='weui-tabbar__icon'></image>\r\n";
            }elseif (@$item['icon']){
                echo $this->indent(3) . "<view class='weui-tabbar__icon {$item['icon']}'></view>\r\n";
            }else{
                echo $this->indent(3) . "<view class='weui-tabbar__icon bg-secondary'></view>\r\n";
            }
            echo $this->indent(3) . "<text class='weui-badge'>8</text>\r\n";
            echo $this->indent(2) . "</view>\r\n";
            echo $this->indent(2) . "<text".$this->wrap_output('class', $this->itemCss($item)).">\r\n";
            echo $this->indent(3) . @$item['text'] . "\r\n";
            echo $this->indent(2) . "</text>\r\n";
            echo $this->indent(1) . "</view>\r\n";
        }

        echo "{$space}</view>\r\n";
    }
}
