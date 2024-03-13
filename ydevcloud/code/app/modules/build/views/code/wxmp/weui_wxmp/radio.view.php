<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Radio_View as Preview_Radio_View;

class Radio_View extends Preview_Radio_View {
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
        echo "\r\n{$space}<radio-group class='w-100 weui-cells_radio'>\r\n";
        foreach ((array)@$values as $index => $item){
            echo $this->indent(5)."<label";
            echo " class='weui-cell weui-check__label'";
            echo ">\r\n";
            echo $this->indent(6);
            echo "<view class='weui-cell__hd pr-3'>\r\n";
            echo $this->indent(7);
            echo "<radio";

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
        echo "{$space}</radio-group>\r\n";
    }
}
