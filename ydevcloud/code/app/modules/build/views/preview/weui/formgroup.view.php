<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Formgroup_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup,Html_Code_Helper;
    protected function has_title(){
        return trim(@$this->data['meta']['title']);
    }
    protected function has_Help_Tip() {
        return trim(@$this->data['meta']['form']['helpTip']);
    }
    protected function css_map()
    {
        $map = parent::css_map();
        if ($this->isHorizontal()){
            $map['-'] = 'weui-cells';
        }else{
            $map['-'] = 'weui-cells__group';
        }
        if (@$this->data['meta']['form']['state']=='hidden'){
            $map['-'] .= ' d-none';
        }else{
            $map['-'] .= ' d-block';
        }
        if (@$this->data['meta']['form']['state']=='readonly' || @$this->data['meta']['form']['state']=='disabled'){
            $map['-'] .= ' weui-cell_readonly';
        }
        return $map;
    }
    protected function label_Css(){
        $css = [];
        if ($this->isHorizontal()){
            $css['-'] = 'weui-cell__hd pr-2';
        }else{
            $css['-'] = 'weui-cells__title text-dark';
        }
        if (@$this->data['meta']['titleAlign']){
            $css[] = 'text-'.$this->data['meta']['titleAlign'];
        }
        return join(' ', $css);
    }
    protected function body_css() {
        $css = ['weui-flex'];
        if (@$this->data['type'] == 'Textarea'){
            $css[] = 'align-items-start';
        }else{
            $css[] = 'align-items-center';
        }
        return join(' ', $css);
    }
    protected function style_map()
    {
        $map = parent::style_map();
        // 垂直排列时阴影交给内部元素处理
        if (!$this->isHorizontal()) {
            unset($map['box-shadow']);
        }
        return $map;
    }

    protected function boxStyle(){
        $map = parent::style_map();
        return $map['box-shadow'];
    }

    protected function singleLineCss() {
        $css = ['weui-cell'];
        if ($this->data['type'] == 'Select') {
            $css[] = 'align-items-center';
        }
        $css[] = 'align-items-start';
        return join(' ', $css);
    }
    protected function isHorizontal() {
        if (isset($this->data['meta']['form']['horizontal'])) {
            return $this->data['meta']['form']['horizontal'];
        }
        return true; // 默认水平排列
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<label";
        echo $this->build_main_attrs();
        echo ">\r\n";

        if($this->isHorizontal()){
            echo $this->indent(1);
            echo "<div ".$this->wrap_output('class', $this->singleLineCss()).">\r\n";
            if ($this->has_title()) {
                echo $this->indent(2);
                echo "<div " . $this->wrap_output('class', $this->label_css()) . ">\r\n";
                echo $this->indent(3);
                echo "<span class='weui-label'>".$this->data['meta']['title'];
                if ($this->data['meta']['form']['required']){
                    echo '<span class="text-danger">*</span>';
                }
                echo "\r\n".$this->indent(3);
                echo "</span>\r\n";
                echo $this->indent(2);
                echo "</div>\r\n";
            }

            echo $this->indent(2);
            echo "<div class='weui-cell__bd'>\r\n";
            echo $this->indent(3);
            echo "<div".$this->wrap_output('class', $this->body_css()).">\r\n";
            echo $this->content_of_view();
            echo $this->indent(3);
            echo "</div>\r\n";
            if ($this->has_Help_Tip()){
                echo $this->indent(3);
                echo "<small id='".$this->myId(true)."Help' class='text-muted'>".$this->data['meta']['form']['helpTip']."</small>\r\n";
            }
            echo $this->indent(2);
            echo "</div>\r\n";
            echo $this->indent(1);
            echo "</div>\r\n";
        }else{
            if ($this->has_title()) {
                echo $this->indent(1);
                echo "<div " . $this->wrap_output('class', $this->label_css()) . ">\r\n";
                echo $this->indent(2);
                echo $this->data['meta']['title'];
                if ($this->data['meta']['form']['required']){
                    echo '<span class="text-danger">*</span>';
                }
                echo "\r\n".$this->indent(1);
                echo "</div>\r\n";
            }
            echo $this->indent(1);
            echo "<div class='weui-cells weui-cells_form' style='".$this->boxStyle()."'>\r\n";
            echo $this->indent(2);
            echo "<div class='weui-cell'>\r\n";
            echo $this->indent(3);
            echo "<div class='weui-cell__bd'>\r\n";
            echo $this->indent(4);
            echo "<div".$this->wrap_output('class', $this->body_css()).">\r\n";
            echo $this->content_of_view();
            echo $this->indent(4);
            echo "</div>\r\n";
            if ($this->has_Help_Tip()){
                echo $this->indent(4);
                echo "<small id='".$this->myId(true)."Help' class='text-muted'>".$this->data['meta']['form']['helpTip']."</small>\r\n";
            }
            echo $this->indent(3);
            echo "</div>\r\n";
            echo $this->indent(2);
            echo "</div>\r\n";
            echo $this->indent(1);
            echo "</div>\r\n";
        }
        echo "{$space}</label>\r\n";
    }
}
