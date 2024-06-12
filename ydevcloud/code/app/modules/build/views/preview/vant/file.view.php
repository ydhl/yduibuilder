<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use yangzie\YZE_View_Component;

class File_View extends Preview_View {
    use  Vant_Popup,Html_Code_Helper;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }

    private function body_css() {
        $css = ['d-flex align-items-center'];
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing'] != 'normal'){
            $css[] = ' form-control-'.$this->data['meta']['css']['formSizing'];
        }
        if (@$this->data['meta']['form']['state']=='disabled'){
            $css[] = ' disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $css[] = ' readonly';
        }
        return join(' ', $css);
    }
    private function body_style() {
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
        $space =  $this->indent(2);
        echo "{$space}";
        echo "<div";
        echo $this->wrap_output('class', $this->body_css());
        echo $this->wrap_output('style', $this->body_style());
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
