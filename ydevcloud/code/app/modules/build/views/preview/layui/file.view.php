<?php
namespace app\modules\build\views\preview\layui;

use app\modules\build\views\preview\Preview_View;

class File_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }
    public function body_class() {
        $css = ['layui-pl-0 layui-border-0 layui-d-flex layui-align-items-center'];
        if ($this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing'] == "normal"){
            $css[] = 'layui-form-'.$this->data['meta']['css']['formSizing'];
        }
        if ($this->data['meta']['form']['state'] === 'disabled' || $this->data['meta']['form']['state'] === 'readonly'){
            $css[] = 'disabled';
        }
        return join(' ', $css);
    }
    public function build_style($justSelf = true)
    {
        $_ = parent::build_style($justSelf);
        $style = [];
        $styleMap = parent::style_map();
        foreach ($styleMap as $name => $value){
            if (preg_match("/^height/", $name)) {
                $style[$name] = $value;
            }
        }
        $_["[data-uiid=".$this->myId().'-body]'] = join(";", array_values($style));
        return $_;
    }

    public function build_ui()
    {
        $space =  $this->indent(2);
        echo "{$space}";
        echo "<div";
        echo $this->wrap_output('id', $this->myId(true).'-body');
        echo $this->wrap_output('data-uiid', $this->myId().'-body');
        echo $this->wrap_output('class', $this->body_class());
        echo $this->wrap_output('accept', $this->data['meta']['custom']['accept'] ?: '*/*');
        echo $this->wrap_output('multiple', $this->data['meta']['custom']['multiple']);
        echo ">";
        echo $this->indent(3) . '<input class="layui-w-100" type="file"';
        echo $this->build_form_attrs();
        echo ">\r\n";
        echo "{$space}</div>\r\n";
    }
}
