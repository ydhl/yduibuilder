<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\List_View as Preview_List_View;

class List_View extends Preview_List_View {
    use Wxmp;

    public function build_style($justSelf = true)
    {
        $map = parent::build_style($justSelf);
        $itemStyle = $this->itemStyle();
        if ($itemStyle) $map['.' . $this->myId(). '-item'] = $this->itemStyle();
        return $map;
    }

    protected function itemTheme($item)
    {
        $itemCss = [];
        if ($this->data['meta']['css']['backgroundTheme']) $itemCss[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['backgroundTheme']];
        if ($this->data['meta']['css']['foregroundTheme']) $itemCss[] = $this->cssTranslate['foregroundTheme'][$this->data['meta']['css']['foregroundTheme']];
        $itemCss[] = 'weui-cell';
        $itemCss[] = $this->myId()."-item";
        return join(' ', $itemCss);
    }

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '1' ], [ "text"=> 'Sample 2', "value"=> '2' ]];
        $space =  $this->indent();
        echo "{$space}<view ";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ((array)@$values as $item){
            echo $this->indent(1) . "<view"
                .$this->wrap_output('class', $this->itemTheme($item)).">\r\n";
            echo $this->indent(2) . "<text class='weui-cell__bd'>\r\n";
            echo $this->indent(3) . "<text>".@$item['text']."</text>\r\n";
            echo $this->indent(2) . "</text>\r\n";
            if (@$item['checked']){
                echo $this->indent(2) . "<view class='weui-cell__ft'><view class='weui-icon-success'></view></view>\r\n";
            }
            echo $this->indent(1) . "</view>\r\n";
        }

        echo "{$space}</view>\r\n";
    }
}
