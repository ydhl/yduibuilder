<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Formgroup_View as Preview_Formgroup_View;

class Formgroup_View extends Preview_Formgroup_View {
    use Wxmp;

    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $box = $this->boxStyle();
        if ($box) $style['[data-uiid=' . $this->myId() . '-box]'] = $box;
        return $style;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<view";
        echo $this->build_main_attrs();
        echo ">\r\n";

        if($this->isHorizontal()){
            echo $this->indent(1);
            echo "<view ".$this->wrap_output('class', $this->singleLineCss()).">\r\n";
            if ($this->has_title()) {
                echo $this->indent(2);
                echo "<view " . $this->wrap_output('class', $this->label_css()) . ">\r\n";
                echo $this->indent(3);
                echo "<view class='weui-label'>".$this->data['meta']['title'];
                if ($this->data['meta']['form']['required']){
                    echo '<text class="text-danger">*</text>';
                }
                echo "\r\n".$this->indent(3);
                echo "</view>\r\n";
                echo $this->indent(2);
                echo "</view>\r\n";
            }

            echo $this->indent(2);
            echo "<view class='weui-cell__bd'>\r\n";
            echo $this->indent(3);
            echo "<view".$this->wrap_output('class', $this->body_css()).">";
            echo $this->content_of_view();
            echo $this->indent(3);
            echo "</view>\r\n";
            if ($this->has_Help_Tip()){
                echo $this->indent(3);
                echo "<text id='".$this->myId()."Help' class='text-muted'>".$this->data['meta']['form']['helpTip']."</text>\r\n";
            }
            echo $this->indent(2);
            echo "</view>\r\n";
            echo $this->indent(1);
            echo "</view>\r\n";
        }else{
            if ($this->has_title()) {
                echo $this->indent(1);
                echo "<view " . $this->wrap_output('class', $this->label_css()) . ">\r\n";
                echo $this->indent(2);
                echo $this->data['meta']['title'];
                if ($this->data['meta']['form']['required']){
                    echo '<span class="text-danger">*</span>';
                }
                echo "\r\n".$this->indent(1);
                echo "</view>\r\n";
            }
            echo $this->indent(1);
            echo "<view class='weui-cells weui-cells_form' data-uiid='".$this->myId()."-box' id='".$this->myId()."-box'>\r\n";
            echo $this->indent(2);
            echo "<view class='weui-cell'>\r\n";
            echo $this->indent(3);
            echo "<view class='weui-cell__bd'>\r\n";
            echo $this->indent(4);
            echo "<view".$this->wrap_output('class', $this->body_css()).">\r\n";
            echo $this->content_of_view();
            echo $this->indent(4);
            echo "</view>\r\n";
            if ($this->has_Help_Tip()){
                echo $this->indent(4);
                echo "<text id='".$this->myId()."Help' class='text-muted'>".$this->data['meta']['form']['helpTip']."</text>\r\n";
            }
            echo $this->indent(3);
            echo "</view>\r\n";
            echo $this->indent(2);
            echo "</view>\r\n";
            echo $this->indent(1);
            echo "</view>\r\n";
        }
        echo "{$space}</view>\r\n";
    }
}
