<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use yangzie\YZE_View_Component;
use function yangzie\__;

class Select_View extends ValueList_View {
    protected function css_map()
    {
        $cssmap = parent::css_map();
        unset($cssmap['formSizing']);
        return $cssmap;
    }

    protected function body_css() {
        $css[] = 'form-control';
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing'] != 'normal' ){
            $css[] = 'form-control-'.$this->data['meta']['css']['formSizing'];
        }
        return join(' ', $css);
    }
    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $style['[data-uiid='.$this->myid().'] select'] = $this->body_style();
        return $style;
    }
    protected function body_style() {
        $styleMap = parent::style_map();
        $newStyle = ['font:inherit;color:inherit'];
        foreach ($styleMap as $key => $value) {
            if (preg_match("/height/", $key)) {
                $newStyle[$key] = $value;
            }
        }
        $newStyle = array_values($newStyle);
        return join(';', $newStyle);
    }

    private function ui_begin($is2d=false, $outDataName=''){
        $space =  $this->indent();
        $this->get_input_data($dataName);
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;
        echo $this->indent(1)."<select";
        echo $this->wrap_output('size', $this->data['meta']['custom']['size']?:NULL);
        if ($this->data['meta']['custom']['multiple']) echo $this->wrap_output('multiple', NULL, true);
        echo $this->build_form_attrs();
        // 如果没有数据绑定的话，定义一个临时数据
        if (!$dataName) {
            echo $this->wrap_output('x-model.fill', $this->myid() . '_temp'.($is2d ? "[idxOf{$outDataName}]" : ''));
        }
        echo $this->wrap_output('class', $this->body_css());
        echo ">".PHP_EOL;
    }
    private function ui_end(){
        $space =  $this->indent();
        echo $this->indent(1);
        echo "</select>".PHP_EOL;
        echo "{$space}</div>".PHP_EOL;
    }

    protected function build_ui_static()
    {
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '1' ], [ "text"=> 'Sample 2', "value"=> '2' ]];

        $this->ui_begin();
        foreach ((array)$values as $item){
            echo $this->indent(2);
            echo "<option value='{$item['value']}' ".(@$item['checked'] ? 'selected' : '').">{$item['text']}</option>".PHP_EOL;
        }
        $this->ui_end();
    }

    protected function build_ui_2d_array($bindOutput, $outDataName)
    {
        $this->build_select($bindOutput, $outDataName, true);
    }

    protected function build_ui_array($bindOutput, $outDataName)
    {
        $this->build_select($bindOutput, $outDataName, false);
    }
    private function build_select($bindOutput, $outDataName, $is2d){
        $this->ui_begin($is2d, $outDataName);
        $itemName = $bindOutput['name'];
        if ($is2d){
            $itemName .= '2';
        }
        echo $this->indent(2).'<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '.($is2d?"itemOf":"").$outDataName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;
        $this->build_option($is2d ? $bindOutput['item'] : $bindOutput, $itemName, ($is2d?"itemOf":"").$outDataName);
        echo $this->indent(2)."</template>".PHP_EOL;
        $this->ui_end();
    }
    private function build_option($bindOutput, $itemName, $outDataName){
        list('name'=>$name, 'value'=>$value) = $this->get_bind_name_value($bindOutput, $itemName);
        echo $this->indent(2).'<option';
        echo $this->wrap_output(":value",$value);
        echo $this->wrap_output("x-text",$name);
        echo $this->wrap_output(":data-bound","'{$outDataName}[\''+idxOf{$itemName}+'\']'");
        echo '></option>'.PHP_EOL;
    }

    function build_code(): Base_Code_Fragment
    {
        parent::build_code();
        $hasIterate = $this->need_iterate_data($iterateOutputAs, $outputDataName, $iterateDataName) || $this->data['meta']['custom']['multiple'];
        $codeFragment = $this->get_code_Fragment();
        $inputData = $this->get_input_data($dataName);
        // 如果没有数据绑定的话，定义一个临时数据
        if (!$dataName) {
            $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid() . '_temp: '.($hasIterate?'[]':'""').',');
        }
        return $this->get_code_fragment();
    }
}
