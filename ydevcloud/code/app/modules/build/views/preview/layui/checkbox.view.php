<?php
namespace app\modules\build\views\preview\layui;



use app\modules\build\views\preview\Preview_View;

class Checkbox_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }
    public function build_ui()
    {
        $space =  $this->indent(2);
        $values = @$this->data['meta']['values']?:[[ "name"=> 'sample', "value"=> '1' ]];
        foreach ((array)@$values as $item){
            echo "{$space}<div ".($this->data['meta']['custom']['inline'] ? 'class="layui-d-inline-block"' : '').">\r\n";
            echo $this->indent(3);
            echo "<input type='checkbox' ";
            echo ' lay-skin="primary"';
            if (@$item['checked']){
                echo ' checked';
            }
            echo " title='{$item['text']}'";
            echo ' id="'.$this->myId(true).$item['value'].'"';
            echo $this->build_form_attrs();
            echo ' value="'.@$item['value'].'"';
            echo "'>\r\n";
            echo "{$space}</div>\r\n";
        }
    }
}
