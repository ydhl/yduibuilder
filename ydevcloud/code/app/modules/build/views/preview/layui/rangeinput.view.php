<?php
namespace app\modules\build\views\preview\layui;


use app\modules\build\views\code\Base_Code_Fragment;


use app\modules\build\views\preview\Preview_View;

class Rangeinput_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }

    private function body_class() {
        $arr = ['layui-pl-0 layui-border-0 layui-d-flex layui-align-items-center'];
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing']!='normal'){
            $css[] = 'layui-form-'.$this->data['meta']['css']['formSizing'];
        }
        if (@$this->data['meta']['form']['state']=='disabled'){
            $css[] = 'layui-disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $css[] = 'readonly';
        }
        return join(' ', $arr);
    }
    private function range_class(){
        $css = ['layui-form-control-range layui-w-100'];
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing']!='normal'){
            $css[] = 'layui-form-control-range-'.$this->data['meta']['css']['formSizing'];
        }
        if (@$this->data['meta']['custom']['theme'] && $this->data['meta']['custom']['theme']!='default'){
            $css[] = 'layui-range-'.$this->data['meta']['custom']['theme'];
        }
        return join(" ", $css);
    }
    public function build_code():Base_Code_Fragment
    {
        parent::build_code();
        ob_start();
?>
document.getElementById('<?= $this->myId(true)?>-range')?.addEventListener('change', function(event) {
    var minValue = event.target.min || 1;
    var value = event.target.value;
    var maxValue = event.target.max || 100;
    var percent = ((value - minValue) / (maxValue - minValue) * 100) + '%';
    event.target.style.cssText = 'background-size:' + percent + ' 100% !important';
});
<?php
        $this->get_code_Fragment()->add_code(ob_get_clean());
        return $this->get_code_fragment();
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
        if ($style) $_["[data-uiid=".$this->myId().'-body]'] = join(";", $style);

        // range style
        $style = [];
        $background = [];
        $backgroundSize = ['50%', '100%'];
        $color = $this->data['meta']['custom']['color'];
        $backgroundColor = $this->data['meta']['custom']['backgroundColor'];
        $min = $this->data['meta']['custom']['min'] ??  1;
        $max = $this->data['meta']['custom']['max'] ?? 100;
        $value = $this->data['meta']['value'] ?? 50;
        $backgroundSize[0] = $value==0 ? '0%' : (($value - $min) / ($max - $min) * 100 ) . '%';
        if ($color) {
            $style[] = "border: 1px solid {$color} !important";
            $background[] = "-webkit-linear-gradient({$color}, {$color}) no-repeat";
        }
        if ($backgroundColor) {
            $background[] = $backgroundColor;
        }
        if ($background) {
            $style[] = "background:" . join(',', $background) . " !important";
        }
        $style[] = "background-size:" . join(' ', $backgroundSize) . " !important";
        $_["[data-uiid=".$this->myId().'-range]'] = join(";", $style);

        return $_;
    }

    public function build_ui()
    {
        $space =  $this->indent(2);
        echo "{$space}";
        echo '<div';
        echo $this->wrap_output('data-uiid', $this->myId().'-body');
        echo $this->wrap_output('class', $this->body_class());
        echo ">";
        echo $this->indent(3) . '<input type="range"';
        echo $this->wrap_output('id', $this->myId(true).'-range');
        echo $this->wrap_output('data-uiid', $this->myId().'-range');
        echo $this->build_form_attrs();
        echo $this->wrap_output('min', $this->data['meta']['custom']['min']?:1);
        echo $this->wrap_output('max', $this->data['meta']['custom']['max']?:100);
        echo $this->wrap_output('step', $this->data['meta']['custom']['step']?:1);
        echo $this->wrap_output('class', $this->range_class());
        echo ' value="'.@$this->data['meta']['value'].'"';
        echo "'>\r\n";
        echo "{$space}</div>\r\n";
    }
}
