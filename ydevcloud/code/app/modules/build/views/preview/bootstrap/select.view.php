<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\ValueList_View;
use yangzie\YZE_View_Component;
use function yangzie\__;

class Select_View extends ValueList_View {
    use Bootstrap_Popup,Html_Code_Helper;

    protected function build_valuelist($bindOutput=null, $itemName=null, $staticData=null, $staticDataIndex=null, $iteratorName=''){
        list('name'=>$name, 'value'=>$value, 'checked'=>$checked) = $this->get_bind_name_value($bindOutput, $itemName);
        $inputDataName = $this->get_input_data_name($inputIsArr);
        $staticValue = $staticData['value'] ?: $staticData['name'];

        echo $this->indent(2).'<option';
        if ($value){
            echo $this->wrap_output(":value", $value);
            echo $this->wrap_output("x-text", $name);
            echo $this->wrap_output(':data-default', $checked ? "{$checked} ? {$value} : ''" : null);
            echo $this->wrap_output(':selected', "alpinejs_in_array(\$el, '{$inputDataName}', {$value})");
        }else{
            echo $this->wrap_output("value", $staticData['value']?:$staticData['name']);
            echo $this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null);
            echo $this->wrap_output(':selected', "alpinejs_in_array(\$el, '{$inputDataName}', '{$staticValue}')");
        }
        echo '>'.$staticData['name'].'</option>'.PHP_EOL;
    }
    protected function css_map()
    {
        $cssmap = parent::css_map();
        unset($cssmap['formSizing'], $cssmap['backgroundTheme']);
        return $cssmap;
    }
    protected function style_map($meta = null, $state = 'normal')
    {
        $map = parent::style_map($meta, $state);
        unset($map['background-color']);
        return $map;
    }
    public function build_ui_begin($iteratorName=null){
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;
        echo $this->indent(1)."<select";
        echo $this->wrap_output('size', $this->data['meta']['custom']['size']?:NULL);
        echo $this->wrap_output('multiple', NULL, $this->data['meta']['custom']['multiple']);
        echo $this->build_form_attrs();
        echo $this->wrap_output('class', $this->select_css());
        echo ">".PHP_EOL;
    }
    public function build_ui_end(){
        $space =  $this->indent();
        echo $this->indent(1);
        echo "</select>".PHP_EOL;
        echo "{$space}</div>".PHP_EOL;
    }
    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $style['[data-uiid='.$this->myid().'] select'] = $this->select_style();
        return $style;
    }

    private function select_css() {
        $styleMap = parent::style_map();
        $css[] = 'form-control input';
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing'] != 'normal' ){
            $css[] = 'form-control-'.$this->data['meta']['css']['formSizing'];
        }
        if($this->data['meta']['css']['backgroundTheme'] && !$styleMap['background-color']) $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['backgroundTheme']];
        return join(' ', $css);
    }
    protected function select_style() {
        $styleMap = parent::style_map();
        $newStyle = ['font' => 'font:inherit','color' => 'color:inherit'];
        foreach ($styleMap as $key => $value) {
            if (preg_match("/height/", $key)) {
                $newStyle[$key] = $value;
            }
        }
        if ($styleMap['background-color']){
            $newStyle['background-color'] = $styleMap['background-color'];
        }
        $newStyle = array_values($newStyle);
        return join(';', $newStyle);
    }



}
