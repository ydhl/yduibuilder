<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use yangzie\YZE_View_Component;
use app\modules\build\views\preview\Html_Event_Binding;
class File_View extends Preview_View {
    use Html_Event_Binding, Bootstrap_Popup,Html_Code_Helper;
    protected function css_map() {
        $css = parent::css_map();
        $css[] = 'd-flex align-items-center overflow-hidden';
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing'] != 'normal'){
            $css[] = ' form-control-'.$this->data['meta']['css']['formSizing'];
        }
        if (@$this->data['meta']['form']['state']=='disabled'){
            $css[] = ' disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $css[] = ' readonly';
        }
        return $css;
    }

    public function build_ui()
    {
        $space =  $this->indent(2);
        echo "{$space}";
        echo "<div";
        echo $this->build_main_attrs();
        echo ">\r\n";
        echo $this->indent(3);
        echo '<input type="file" class="d-block"';
        echo $this->build_form_attrs();
        if (@$this->data['meta']['custom']['accept']){
            echo ' accept="'.$this->data['meta']['custom']['accept'].'"';
        }
        if (@$this->data['meta']['custom']['multiple']){
            echo ' multiple';
        }
        echo ">\r\n";
        echo "{$space}";
        echo "</div>\r\n";
    }
}
