<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Select_View as Preview_Select_View;

class Select_View extends Preview_Select_View {
    use Wxmp;
    private function get_values(){
        return @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '1' ], [ "text"=> 'Sample 2', "value"=> '2' ]];
    }
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }
    public function indicator_style(){
        return 'height:56px'; // weui 定义的高度
    }
    public function pick_style(){
        return 'height:56px;width:100%';
    }
    public function mask_style(){
        return 'background:none';
    }
    protected function css_map()
    {
        $css = parent::css_map();
        $style = parent::style_map();
        if ($style['background-color']){
            unset($css['backgroundTheme']);
        }
        if ($style['color']){
            unset($css['foregroundTheme']);
        }
        return $css;
    }

    public function build_ui()
    {
        $space =  $this->indent(4);
        echo "\r\n{$space}<picker-view";
        echo $this->wrap_output('style', $this->pick_style());
        echo $this->wrap_output('indicator-style', $this->indicator_style());
        echo $this->wrap_output('mask-style', $this->mask_style());
        echo $this->wrap_output('value', "{{".$this->myId()."Value}}");
        echo $this->build_form_attrs();
        echo $this->wrap_output('class', $this->get_css());
        echo ">\r\n";

        echo $this->indent(5);
        echo "<picker-view-column>\r\n";


        echo $this->indent(6);
        echo "<view wx:for='{{".$this->myId()."Items}}'";
        echo $this->wrap_output('style', "line-height: 56px");
        echo " wx:key='index'>{{item.text}}</view>\r\n";
        echo $this->indent(5);
        echo "</picker-view-column>\r\n";
        echo "{$space}</picker-view>\r\n";

    }
    public function build_code(): Base_Code_Fragment
    {
        parent::build_code();

        $values = $this->get_values();
        $items = [];
        $defaultValue = 0;
        foreach ((array)$values as $index => $item){
            $items[$index] = ['text' => $item['text'], 'value' => $item['value']];
            if ($item['checked']) {
                $defaultValue = $index;
            }
        }

        $this->get_code_fragment()->add_data($this->myId()."Items", $items);
        $this->get_code_fragment()->add_data($this->myId()."Value", [$defaultValue]);
        return $this->get_code_fragment();
    }
}
