<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Rangeinput_View as Preview_Rangeinput_View;

class Rangeinput_View extends Preview_Rangeinput_View {
    use Vue;
    public function build_ui()
    {
        $space =  $this->indent(2);
        $myid = $this->myId();
        echo "{$space}";
        echo "<div";
        echo $this->wrap_output('class', $this->body_css());
        echo ">\r\n";
        echo $this->indent(3)."<input type='range' ref='{$myid}'";
        echo $this->build_form_attrs();
        echo ' v-model="'.$myid.'Value" :style="'.$myid.'Style"';
        echo ' min="'.@$this->data['meta']['custom']['min']?:1;
        echo '" max="'.@$this->data['meta']['custom']['max']?:100;
        echo '" step="'.@$this->data['meta']['custom']['step']?:1;

        echo '"';
        echo $this->wrap_output('class', $this->rangeCss());
        echo ">\r\n";
        echo "{$space}";
        echo "</div>\r\n";
    }
    public function build_code():Base_Code_Fragment
    {
        parent::build_code();
        $myid = $this->myId();
        $defaultValue = floatval(@$this->data['meta']['value']);
        $this->get_code_fragment()->add_import('vue', ['ref','computed']);
        $this->get_code_fragment()->add_import('bootstrap');
        $this->get_code_fragment()->add_ref($myid, '', true);
        $this->get_code_fragment()->add_ref("{$myid}Value", $defaultValue, true);

        ob_start();
?>
() => {
  const minValue = <?= $myid?>.value?.min || 1
  const value = <?= $myid?>Value.value
  const maxValue = <?= $myid?>.value?.max || 100
  const percent = ((value - minValue) / (maxValue - minValue)) * 100 + '%'
  const style = <?= $this->formatVue3JSON($this->rangeStyle())?>

  style['background-size'] = `${percent} 100%`
  return YDECloud.styleFromJson(style)
}
<?php

        $this->get_code_fragment()->add_import("@/lib/ydecloud", [], 'YDECloud');
        $this->get_code_fragment()->add_computed("{$myid}Style", ob_get_clean(), true);
        return $this->get_code_fragment();
    }
}
