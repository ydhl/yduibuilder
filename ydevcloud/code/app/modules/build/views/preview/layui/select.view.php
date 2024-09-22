<?php
namespace app\modules\build\views\preview\layui;


use app\modules\build\views\preview\Preview_View;

class Select_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }
    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "name"=> 'Sample 1', "value"=> '1' ], [ "name"=> 'Sample 2', "value"=> '2' ]];
        $space =  $this->indent(2);
        echo $space;
        echo "<select";
        if (@$this->data['meta']['custom']['size']){
            echo ' size=' . $this->data['meta']['custom']['size'];
        }
        if (@$this->data['meta']['custom']['multiple']){
            echo ' multiple';
        }
        if ($this->data['meta']['custom']['searchable']) {
            echo ' lay-search';
        }
        echo $this->build_form_attrs();
        echo ">\r\n";

        foreach ((array)$values as $item){
            echo $this->indent(3);
            echo "<option value='{$item['value']}' ".(@$item['checked'] ? 'selected' : '').">{$item['text']}</option>\r\n";
        }

        echo "{$space}</select>\r\n";
    }
}
