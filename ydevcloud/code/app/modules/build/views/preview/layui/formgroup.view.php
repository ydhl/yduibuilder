<?php
namespace app\modules\build\views\preview\layui;
use app\modules\build\views\preview\Preview_View;

class Formgroup_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    private function label_css() {
        $css = [];
        if ($this->data['meta']['form']['horizontal']) {
            $css[] = 'layui-form-mid';
            $css[] = 'layui-col-xs' . $this->data['meta']['form']['horizontalCol'];
        }else{
            $css[] = 'layui-form-label-col';
        }
        return $css ? join(' ', $css) : '';
    }
    private function label_style() {
        $style = [];
        if (@$this->data['meta']['form']['horizontal']) {
            $style[] = 'margin-right: 0px !important;padding-right: 15px !important';
        }
        if(@$this->data['meta']['titleAlign']){
            $style[] = 'text-align:' . $this->data['meta']['titleAlign'];
        }
        return $style ? join(';', $style) : '';
    }
    private function body_css() {
        $css = [];
        if ($this->data['meta']['form']['horizontal']) {
            $css[] = 'layui-col-xs' . (12 - ($this->data['meta']['form']['horizontalCol'] ?: 2));
        }
        if (!$this->has_title() && $this->data['meta']['form']['horizontal']){
            $css[] = 'layui-col-xs-offset'.($this->data['meta']['form']['horizontalCol'] ?: 2);
        }
        return $css ? join(' ', $css) : '';
    }
    protected function has_title(){
        return trim(@$this->data['meta']['title']);
    }
    protected function has_Help_Tip() {
        return trim(@$this->data['meta']['form']['helpTip']);
    }
    protected function css_map()
    {
        $cssMap = parent::css_map();
        // sizing 作用在具体的表单元素上
        unset($cssMap['formSizing']);
        $arr = ['layui-d-flex layui-align-items-center'];
        if ($this->data['meta']['form']['state'] == 'disabled') {
            $arr[] = 'layui-disabled';
        }
        if ($this->data['meta']['form']['horizontal']) {
            $arr[] = 'layui-form-item';
        }
        if (@$this->data['meta']['form']['state']=='hidden'){
            $arr[] = 'flex-d-none';
        }
        $cssMap['-'] = join(' ', $arr);
        return $cssMap;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $style = parent::style_map($meta, $state);
        $newStyle = [];
        foreach ($style as $name => $value){
            if (!preg_match("/^height/", $name)){
                $newStyle[$name] = $value;
            }
        }
        return $newStyle;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->output_main_attrs();
        echo ">\r\n";

        if ($this->has_title()){
            echo $this->indent(1);
            echo "<label for='".$this->myId(true).$this->data['type']."'";
            echo $this->wrap_output('class', $this->label_css());
            echo $this->wrap_output('style', $this->label_style());
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
            echo "<small";
            echo $this->wrap_output('id', $this->myId(true)."Help");
            echo $this->wrap_output('class', "layui-form-mid layui-word-aux");
            echo ">";
            echo $this->data['meta']['form']['helpTip'];
            echo "</small>\r\n";
        }
        echo $this->indent(1);
        echo "</div>\r\n";

        if ($this->has_Help_Tip() && !@$this->data['meta']['form']['horizontal']){
            echo $this->indent(1);
            echo "<small";
            echo $this->wrap_output('id', $this->myId(true)."Help");
            echo $this->wrap_output('class', "layui-form-mid layui-word-aux");
            echo ">";
            echo $this->data['meta']['form']['helpTip'];
            echo "</small>\r\n";
        }

        echo "{$space}</div>\r\n";
    }
}
