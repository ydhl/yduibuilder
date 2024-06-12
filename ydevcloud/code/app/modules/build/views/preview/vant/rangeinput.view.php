<?php
namespace app\modules\build\views\preview\vant;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Rangeinput_View extends Preview_View {
    use Vant_Popup,Html_Code_Helper;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }

    protected function body_css() {
        $css = ['w-100 h-100 d-flex align-items-center mb-2'];
        if (@$this->data['meta']['form']['state']=='disabled'){
            $css[] = ' disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $css[] = ' readonly';
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
    protected function rangeCss() {
        $css = ['form-control-range'];
        if ($this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing'] != 'normal') {
            $css[] = 'form-control-range-' . $this->data['meta']['css']['formSizing'];
        }
        if ($this->data['meta']['custom']['theme'] && $this->data['meta']['custom']['theme']!='default') {
            $css[] = 'range-' . $this->data['meta']['custom']['theme'];
        }
        return join(' ', $css);
    }
    protected function rangeStyle() {
        $style = [];
        $background = [];
        $backgroundSize = ['50%', '100%'];
        $color = $this->data['meta']['custom']['color'];
        $backgroundColor = $this->data['meta']['custom']['backgroundColor'];
        if ($color) {
            $style['border'] = "1px solid {$color} !important";
            $background[] = "-webkit-linear-gradient({$color}, {$color}) no-repeat";
        }
        if ($backgroundColor) {
            $background[] = $backgroundColor;
        }
        if ($background) {
            $style['background'] = join(',', $background) . ' !important';
        }

        $min = $this->data['meta']['custom']['min'] ?? 1;
        $default = $this->data['meta']['value'] ?: 50;
        $max = $this->data['meta']['custom']['max'] ?: 100;
        $backgroundSize[0] = (($default - $min) / ($max - $min) * 100) . '%';
        $style['background-size'] = join(' ', $backgroundSize) . ' !important';
        return $style;
    }
    protected function rangeStyleString() {
        $styles = $this->rangeStyle();
        array_walk($styles, function (&$val, $key){
            $val = $key.":".$val;
        });
        return join(";", array_values($styles));
    }
    public function build_ui()
    {
        $space =  $this->indent(2);
        echo "{$space}";
        echo "<div";
        echo $this->wrap_output('class', $this->body_css());
        echo $this->wrap_output('style', $this->body_style());
        echo ">\r\n";
        echo $this->indent(3)."<input type='range' ";
        echo $this->build_form_attrs();
        echo ' min="'.(@$this->data['meta']['custom']['min']??1);
        echo '" max="'.(@$this->data['meta']['custom']['max']??100);
        echo '" step="'.(@$this->data['meta']['custom']['step']??1);
        echo '"';
        echo $this->wrap_output('class', $this->rangeCss());

        echo $this->wrap_output('style', $this->rangeStyleString());
        echo ' value="'.@$this->data['meta']['value'].'"';
        echo ">\r\n";
        echo "{$space}";
        echo "</div>\r\n";
    }
    public function build_code():Base_Code_Fragment
    {
        parent::build_code();
        ob_start();
?>
document.getElementById('<?= $this->myId(true)?>')?.addEventListener('change', function(event) {
    var minValue = event.target.min || 1;
    var value = event.target.value;
    var maxValue = event.target.max || 100;
    var percent = ((value - minValue) / (maxValue - minValue) * 100) + '%';
    event.target.style.backgroundSize = percent + ' 100%';
});
<?php
        $this->get_code_Fragment()->add_code(ob_get_clean());
        return $this->get_code_fragment();
    }
}
