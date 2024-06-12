<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Formgroup_View extends Preview_View {
    use  Vant_Popup,Html_Code_Helper;
    protected function has_title(){
        return trim(@$this->data['meta']['title']);
    }
    protected function has_Help_Tip() {
        return trim(@$this->data['meta']['form']['helpTip']);
    }
    protected function css_map()
    {
        $arr = parent::css_map();
        unset($arr['formSizing']);

        $arr[] = 'form-group overflow-hidden';
        if (@$this->data['meta']['form']['horizontal']){
            $arr[] = 'row';
        }
        if (@$this->data['meta']['form']['state']=='hidden'){
            $arr[] = 'd-none';
        }
        return $arr;
    }
    private function label_css() {
        $css = ['col-form-label d-block'];
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing']!='normal'){
            $css[] = 'col-form-label-'.$this->data['meta']['css']['formSizing'];
        }
        if (@$this->data['meta']['form']['horizontal']){
            $css[] = 'col-'.$this->data['meta']['form']['horizontalCol'];
        }
        if (@$this->data['meta']['titleAlign']){
            $css[] = 'text-'.$this->data['meta']['titleAlign'];
        }
        return join(' ', $css);
    }
    private function body_css() {
        $css = ['d-flex align-items-start flex-column'];
        if (@$this->data['meta']['form']['horizontal']){
            $css[] = 'col-'.(12 - (@$this->data['meta']['form']['horizontalCol'] ?: 2));
        }
        if (!$this->has_title() && @$this->data['meta']['form']['horizontal']){
            $css[] = 'offset-'.(@$this->data['meta']['form']['horizontalCol'] ?: 2);
        }
        return join(' ', $css);
    }

    protected function style_map($meta=null, $state = 'normal')
    {
        // 由里面的form control处理style
        // 但flex 设置则应用在这里
        $styleArray = parent::style_map($meta);
        $newStyle = [];
        foreach ($styleArray as $key => $value) {
            if (!preg_match("/height/", $key)) {
                $newStyle[$key] = $value;
            }
        }
        return $newStyle;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";

        if ($this->has_title()){
            echo $this->indent(1);
            echo "<label for='".$this->myId(true)."Control'";
            echo $this->wrap_output('class', $this->label_css());
            echo ">";
            echo @$this->data['meta']['title'];
            echo "</label>\r\n";
        }
        echo $this->indent(1);
        echo "<div";
        echo $this->wrap_output('class', $this->body_css());
        echo ">\r\n";

        echo $this->content_of_view();

        if ($this->has_Help_Tip() && @$this->data['meta']['form']['horizontal']){
            echo $this->indent(2);
            echo "<small id='".$this->myId(true)."Help' class='form-text text-muted'>";
            echo $this->data['meta']['form']['helpTip'];
            echo "</small>\r\n";
        }
        echo $this->indent(1);
        echo "</div>\r\n";

        if ($this->has_Help_Tip() && !@$this->data['meta']['form']['horizontal']){
            echo $this->indent(1);
            echo "<small id='".$this->myId(true)."Help' class='form-text text-muted'>";
            echo $this->data['meta']['form']['helpTip'];
            echo "</small>\r\n";
        }

        echo "{$space}</div>\r\n";
    }
}
