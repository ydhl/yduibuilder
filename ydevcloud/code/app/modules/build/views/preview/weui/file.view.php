<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class File_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup,Html_Code_Helper;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }

    public function build_ui()
    {
        $space =  $this->indent(4);
        echo "{$space}";
        echo "<div class='weui-uploader__bd'>\r\n";
        echo $this->indent(5);
        echo "<div class='weui-uploader__input-box'>\r\n";
        echo $this->indent(6);
        echo '<input type="file" class="weui-uploader__input"';
        echo $this->build_form_attrs();
        if (@$this->data['meta']['custom']['accept']){
            echo ' accept="'.$this->data['meta']['custom']['accept'].'"';
        }
        if (@$this->data['meta']['custom']['multiple']){
            echo ' multiple';
        }
        echo ">\r\n";
        echo $this->indent(5);
        echo "</div>\r\n";
        echo "{$space}";
        echo "</div>\r\n";
    }
}
