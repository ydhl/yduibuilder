<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Nav_View as Preview_Nav_View;

class Nav_View extends Preview_Nav_View {
    use Wxmp;

    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $activeStyle = $this->activeItemStyle();
        $unactiveStyle = $this->itemStyle();
        if ($activeStyle) $style['.' . $this->myId() . '-active'] = $activeStyle;
        if ($unactiveStyle) $style['.' . $this->myId() . '-unactive'] = $unactiveStyle;
        return $style;
    }

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '#' ], [ "text"=> 'Sample 2', "value"=> '#', 'checked'=> true ]];
        $space =  $this->indent();
        echo "{$space}<view ";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ((array)@$values as $item){
            echo $this->indent(1) . "<view class='weui-navbar__item ".($item['checked'] ? $this->myId().'-active' : $this->myId().'-unactive')." "
                .($item['checked'] ? $this->activeItemCss() : $this->itemCss())
                ."'>\r\n";
            echo $this->indent(2) . "{$item['text']}\r\n";
            echo $this->indent( 1) . "</view>\r\n";
        }
        foreach ((array)@$this->childViews as $view){
            $view->output();
        }
        echo "{$space}</view>\r\n";
    }
}
