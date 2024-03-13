<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Radio_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup, Html_Code_Helper;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }
    public function build_ui()
    {
        $space =  $this->indent(4);
        $values = @$this->data['meta']['values']?:[[ "text"=> 'sample', "value"=> '1' ]];
        echo "{$space}<div class='w-100 weui-cells_radio'>\r\n";
        foreach ((array)@$values as $index => $item){
            echo $this->indent(5)."<label";
            echo " class='weui-cell weui-cell_active weui-check__label'";
            echo ">\r\n";
            echo $this->indent(6);
            echo "<div class='weui-cell__hd pr-3'>\r\n";
            echo $this->indent(7);
            echo "<input type='radio'";

            if (@$item['checked']){
                echo ' checked';
            }
            echo ' class="weui-check" id="'.$this->myId(true).$index.'"';
            echo $this->build_form_attrs(true);
            echo ' value="'.@$item['value'].'"';
            echo ">\r\n";
            echo $this->indent(7);
            echo "<span class='weui-icon-checked'></span>\r\n";
            echo $this->indent(6);
            echo "</div>\r\n";

            echo $this->indent(6);
            echo "<div class='weui-cell__bd'>\r\n";
            echo $this->indent(7);
            echo "<p>".$item['text']."</p>\r\n";
            echo $this->indent(6);
            echo "</div>\r\n";

            echo $this->indent(5);
            echo "</label>\r\n";
        }
        echo "{$space}</div>\r\n";
    }
}
