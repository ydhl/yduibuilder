<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Checkbox_View as Preview_Checkbox_View;


class Checkbox_View extends Preview_Checkbox_View {
    use Wxmp;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }
    public function build_ui()
    {
        $space =  $this->indent(4);
        $values = @$this->data['meta']['values']?:[[ "text"=> 'sample', "value"=> '1' ]];
        echo "\r\n{$space}<checkbox-group class='w-100 weui-cells_checkbox'>\r\n";
        foreach ((array)@$values as $index => $item){
            echo $this->indent(5);
            echo "<label class='weui-cell weui-cell_active weui-check__label'>\r\n";
            echo $this->indent(6);
            echo "<view class='weui-cell__hd'>\r\n";
            echo $this->indent(7);
            echo "<checkbox";
            if (@$item['checked']){
                echo ' checked';
            }
            echo ' class="weui-check" id="'.$this->myId().$index.'"';
            echo $this->build_form_attrs(true);
            echo ' value="'.@$item['value'].'"';
            echo "/>\r\n";
            echo $this->indent(7);
            echo "<text class='weui-icon-checked'></text>\r\n";
            echo $this->indent(6);
            echo "</view>\r\n";

            echo $this->indent(6);
            echo "<view class='weui-cell__bd'>";
            echo $item['text'];
            echo "</view>\r\n";

            echo $this->indent(5);
            echo "</label>\r\n";
        }

        echo "{$space}</checkbox-group>\r\n";
    }
}
