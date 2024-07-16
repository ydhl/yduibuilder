<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\ValueList_View;

/**
 * <pre>
 *  <div class="weui-cells weui-cells_radio">
 *      <label class="weui-cell weui-cell_active weui-check__label" for="x11">
 *          <div class="weui-cell__bd">
 *              <p>cell standard</p>
 *          </div>
 *          <div class="weui-cell__ft">
 *              <input type="radio" class="weui-check" name="radio1" id="x11">
 *              <span class="weui-icon-checked"></span>
 *          </div>
 *      </label>
 * </pre>
 */
class Radio_View extends ValueList_View {
    use Weui_Popup,Html_Code_Helper;
    protected $type = 'radio';

    protected function build_ui_begin($iteratorName = null)
    {
        $space =  $this->indent();
        echo "{$space}<div";
        $this->build_main_attrs();
        echo ">".PHP_EOL;
    }

    protected function build_ui_end()
    {
        $space =  $this->indent();
        echo "{$space}</div>".PHP_EOL;
    }

    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex = null, $iteratorName='')
    {
        list('name'=>$name, 'value'=>$value, 'checked'=>$checked) = $this->get_bind_name_value($outputData, $itemName);
        $staticValue = $staticData['value']?:$staticData['name'];
        $myid = $this->myid();
        $inputDataName = $this->get_input_data_name($inputIsArr);

        $disabled = $this->data['meta']['form']['state']=='disabled';

        echo $this->indent(1)."<label";
        echo " class='weui-cell weui-cell_active weui-check__label ".($disabled?'weui-cell_disabled':'')."'";
        echo $this->wrap_output('style', $this->cell_style());
        echo ">".PHP_EOL;

        echo $this->indent(2)."<div class='weui-cell__bd'>".PHP_EOL;
        echo $this->indent(3)."<p";
        if ($name){
            echo $this->wrap_output('x-text', $name);
        }
        echo ">".$staticData['name']."</p>".PHP_EOL;
        echo $this->indent(2)."</div>".PHP_EOL;

        echo $this->indent(2)."<div class='weui-cell__ft'>".PHP_EOL;
        echo $this->indent(3)."<input type='{$this->type}'";
        $this->build_form_attrs();
        if ($value){
            echo $this->wrap_output(':value', $value);
            echo $this->wrap_output(':data-default', $checked ? "{$checked} ? {$value} : ''" : null);
            echo $this->wrap_output(':checked', "alpinejs_in_array(\$el, '{$inputDataName}', {$value})");
        }else{
            echo $this->wrap_output('value', $staticValue);
            echo $this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null);
            echo $this->wrap_output(':checked', "alpinejs_in_array(\$el, '{$inputDataName}', '{$staticValue}')");
        }

        echo '" class="weui-check">"'.PHP_EOL;
        echo $this->indent(3).'<span';
        echo $this->wrap_output('class', $this->icon_class());
        echo $this->wrap_output(':style', $this->icon_style($value, $staticValue));
        echo "></span>".PHP_EOL;
        echo $this->indent(2)."</div>".PHP_EOL;

        echo $this->indent(1)."</label>".PHP_EOL;
    }

    protected function css_map()
    {
        $css = parent::css_map();
        $styleMap = Preview_View::style_map();
        if ($styleMap['color']){
            unset($css['foregroundTheme']);
        }
        if ($styleMap['background-color']){
            unset($css['backgroundTheme']);
        }
        $css['-'] = 'w-100 weui-cells weui-cells_'.$this->type;
        return $css;
    }
    protected function cell_style()
    {
        $map = parent::style_map();
        if ($map['color']) return 'color:inherit !important;';
        return '';
    }

    protected function icon_class(){
        $map = Preview_View::style_map();
        $css = ['weui-icon-checked'];
        $foreground = $this->data['meta']['css']['foregroundTheme'];
        if (!$map['color'] && $foreground && $foreground!='default'){
            $css[] = $this->cssTranslate['foregroundTheme'][$foreground];
        }
        return join(' ', $css);
    }
    protected function icon_style($valueName, $staticValue=''){
        $inputDataName = $this->get_input_data_name($isArr);
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;
        $value = $valueName?:"'{$staticValue}'";

        $style = [];
        $map = Preview_View::style_map();

        if ($map['color']){
            $style[] = $map['color'];
        }
        if ($this->type=='radio'){
            return "{$inputDataNameString}=={$value}?'".join(';', $style)."':'display:none'";
        }else{
            return "{$inputDataNameString}=={$value}?'".join(';', $style)."':''";
        }
    }


}
