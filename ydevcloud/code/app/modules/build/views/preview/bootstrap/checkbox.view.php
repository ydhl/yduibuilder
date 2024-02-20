<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use yangzie\YZE_View_Component;
use app\modules\build\views\preview\Html_Event_Binding;
class Checkbox_View extends Preview_View {
    use Html_Event_Binding, Bootstrap_Popup,Html_Code_Helper;

    private function checkbox_css(){
        $css = ['form-check d-flex mr-3'];
        return join(' ', $css);
    }
    protected function css_map() {
        $arr = parent::css_map();
        if ($this->data['meta']['custom']['inline']) {
            $arr[] = 'h-100 d-flex align-items-center';
        } else {
            $arr[] = 'h-auto';
        }
        if (@$this->data['meta']['css']['formSizing']){
            $arr[] = 'form-control-'.$this->data['meta']['css']['formSizing'];
        }
        return $arr;
    }

    public function build_ui()
    {
        $space =  $this->indent(2);
        $values = @$this->data['meta']['values']?:[[ "text"=> 'sample', "value"=> '1' ]];
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";
        foreach ((array)@$values as $index => $item){
            echo $this->indent(3);
            echo "<div";
            echo $this->wrap_output('class', $this->checkbox_css());
            echo ">\r\n";
            echo $this->indent(4);
            echo "<input type='checkbox'";

            if (@$item['checked']){
                echo ' checked';
            }
            echo ' class="form-check-input" id="'.$this->myId(true).$item['value'].$index.'"';
            echo $this->build_form_attrs(true);
            echo ' value="'.@$item['value'].'"';
            echo ">\r\n";

            echo $this->indent(4);
            echo "<label class='form-check-label' for='".$this->myId(true).$item['value'].$index."'>";
            echo $item['text'];
            echo "</label>\r\n";

            echo $this->indent(3);
            echo "</div>\r\n";
        }

        echo "{$space}</div>\r\n";
    }
}
