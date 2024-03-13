<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Select_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup, Html_Code_Helper;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }
    protected function body_css()
    {
        $css = parent::css_map();
        $style = parent::style_map();
        $css[] = 'weui-select pl-0';
        if ($style['background-color']){
            unset($css['backgroundTheme']);
        }
        if ($style['color']){
            unset($css['foregroundTheme']);
        }
        return join(' ', $css);
    }
    protected function body_style()
    {
        $baseStyle = parent::style_map();
        $style[] = 'border-top: 1px solid #e5e5e5;border-bottom: 1px solid #e5e5e5;';
        $style[] = $baseStyle['color'];
        return join(' ', $style);
    }

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '1' ], [ "text"=> 'Sample 2', "value"=> '2' ]];
        $space =  $this->indent(2);
        echo "{$space}<select";
        echo $this->build_form_attrs();
        echo $this->wrap_output('style', $this->body_style());
        echo $this->wrap_output('class', $this->body_css());
        echo ">\r\n";

        foreach ((array)$values as $item){
            echo $this->indent(3);
            echo "<option value='{$item['value']}' ".(@$item['checked'] ? 'selected' : '').">{$item['text']}</option>\r\n";
        }

        echo "{$space}</select>\r\n";
    }
}
