<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;
use yangzie\YZE_View_Component;

class Select_View extends Preview_View {
    use Html_Event_Binding,Bootstrap_Popup,Html_Code_Helper;

    protected function css_map()
    {
        $cssmap = parent::css_map();
        unset($cssmap['formSizing']);
        return $cssmap;
    }

    protected function body_css() {
        $css[] = 'form-control';
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing'] != 'normal' ){
            $css[] = 'form-control-'.$this->data['meta']['css']['formSizing'];
        }
        return join(' ', $css);
    }
    protected function body_style() {
        $styleMap = parent::style_map();
        $newStyle = [];
        foreach ($styleMap as $key => $value) {
            if (preg_match("/height/", $key)) {
                $newStyle[$key] = $value;
            }
        }
        $newStyle = array_values($newStyle);
        return join(';', $newStyle);
    }
    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '1' ], [ "text"=> 'Sample 2', "value"=> '2' ]];
        $space =  $this->indent(2);
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";
        echo $this->indent(3);
        echo "<select";
        if (@$this->data['meta']['custom']['size']){
            echo ' size=' . $this->data['meta']['custom']['size'];
        }
        if (@$this->data['meta']['custom']['multiple']){
            echo ' multiple';
        }
        echo $this->build_form_attrs();
        echo $this->wrap_output('class', $this->body_css());
        echo $this->wrap_output('style', $this->body_style());
        echo ">\r\n";

        foreach ((array)$values as $item){
            echo $this->indent(3);
            echo "<option value='{$item['value']}' ".(@$item['checked'] ? 'selected' : '').">{$item['text']}</option>\r\n";
        }
        echo $this->indent(3);
        echo "</select>\r\n";
        echo "{$space}</div>\r\n";
    }
}
