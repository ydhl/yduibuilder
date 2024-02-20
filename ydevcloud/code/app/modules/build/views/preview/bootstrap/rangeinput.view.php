<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;

class Rangeinput_View extends Preview_View {
    use Html_Event_Binding,Bootstrap_Popup,Html_Code_Helper;


    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);

        $height = floatval($this->data['meta']['style']['height']);
        $heightUnit = preg_replace("/\d+/", '', $this->data['meta']['style']['height']);
        $thumb = [];
        $color = $this->data['meta']['style']['color'];
        if ($color){
            $thumb[] = 'background-color:'.$color;
        }
        if($height){
            $thumb[] = 'width:'.($height*1.5).$heightUnit;
            $thumb[] = 'height:'.($height*1.5).$heightUnit;
        }
        if ($thumb){
            $style['[data-uiid='.$this->myid().']::-webkit-slider-thumb'] = join(';', $thumb);
        }
        return $style;
    }
    protected function css_map()
    {
        $map = parent::css_map();
        $_ = ['form-control-range'];
        if ($this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing'] != 'normal') {
            $_[] = 'form-control-range-' . $this->data['meta']['css']['formSizing'];
        }

        $color = $this->data['meta']['style']['color'];
        if ($this->data['meta']['css']['foregroundTheme'] && $this->data['meta']['css']['foregroundTheme']!='default' && !$color) {
            $_[] = 'range-' . $this->data['meta']['css']['foregroundTheme'];
        }

        $map['-'] = join(' ', $_);
        return $map;
    }
    protected function style_map()
    {
        $map = parent::style_map();

        $background = [];
        $backgroundSize = ['50%', '100%'];
        $color = $this->data['meta']['style']['color'];
        if ($color) {
            $map['border'] = "border:1px solid {$color} !important";
            $background[] = "-webkit-linear-gradient(top, {$color}, {$color})";
        }

        if ($background) {
            $map['background-image'] = 'background-image:'.join(',', $background) . ' !important';
        }

        $min = $this->data['meta']['custom']['min'] ?? 1;
        $default = $this->data['meta']['value'] ?: 50;
        $max = $this->data['meta']['custom']['max'] ?: 100;
        $backgroundSize[0] = (($default - $min) / ($max - $min) * 100) . '%';
        $map['background-size'] = 'background-size:'.join(' ', $backgroundSize);
        return $map;
    }

    public function build_ui()
    {
        $space =  $this->indent(2);
        echo "{$space}\r\n";
        echo $this->indent(3)."<input type='range' ";
        echo $this->build_main_attrs();
        echo $this->build_form_attrs(true);
        echo ' min="'.(@$this->data['meta']['custom']['min']??1);
        echo '" max="'.(@$this->data['meta']['custom']['max']??100);
        echo '" step="'.(@$this->data['meta']['custom']['step']??1);
        echo '"';
        echo ' value="'.@$this->data['meta']['value'].'"';
        echo ">\r\n";
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
