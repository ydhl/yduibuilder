<?php
namespace app\modules\build\views\preview\weui;

/**
 * <pre>
 * <div class="weui-cells__group weui-cells__group_form">
 *  <div class="weui-cells weui-cells_checkbox">
 *      <label class="weui-cell weui-cell_active weui-check__label" for="s11">
 *          <div class="weui-cell__hd">
 *              <input type="checkbox" class="weui-check" name="checkbox1" id="s11" checked="checked">
 *              <i class="weui-icon-checked"></i>
 *          </div>
 *          <div class="weui-cell__bd">
 *              <p>standard is dealt for u.</p>
 *          </div>
 *      </label>
 *  </div>
 * </pre>
 */
class Checkbox_View extends Radio_View {
    protected $type = 'checkbox';

    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex = null, $iteratorName='')
    {
        list('name'=>$name, 'value'=>$value, 'checked'=>$checked) = $this->get_bind_name_value($outputData, $itemName);
        $staticValue = strlen($staticData['value'])?$staticData['value']:$staticData['name'];
        $myid = $this->myid();
        $inputDataName = $this->get_input_data_name($inputIsArr);


        echo $this->indent(1)."<label";
        echo " class='weui-cell weui-cell_active weui-check__label'";
        echo $this->wrap_output('style', $this->cell_style());
        echo ">".PHP_EOL;


        echo $this->indent(2)."<div class='weui-cell__hd'>".PHP_EOL;
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
        echo "></span>".PHP_EOL;
        echo $this->indent(2)."</div>".PHP_EOL;

        echo $this->indent(2)."<div class='weui-cell__bd'>".PHP_EOL;
        echo $this->indent(3)."<p";
        if ($name){
            echo $this->wrap_output('x-text', $name);
        }
        echo ">".$staticData['name']."</p>".PHP_EOL;
        echo $this->indent(2)."</div>".PHP_EOL;


        echo $this->indent(1)."</label>".PHP_EOL;
    }

}
