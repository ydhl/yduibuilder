<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use function yangzie\__;


class Radio_View extends ValueList_View {
    protected $type = 'radio';
    protected function item_css() {
        $css = ['form-check d-flex mr-3 align-items-center'];
        return join(' ', $css);
    }
    protected function css_map() {
        $arr = parent::css_map();
        if ($this->data['meta']['custom']['inline']) {
            $arr[] = 'h-100 d-flex align-items-center';
        } else {
            $arr[] = 'h-auto';
        }

        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing']!='normal'){
            $arr[] = 'form-control-'.$this->data['meta']['css']['formSizing'];
        }
        return $arr;
    }

    protected function build_ui_static()
    {
        $space =  $this->indent();
        $values = @$this->data['meta']['values']?:[[ "text"=> 'sample', "value"=> '1' ]];
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;
        foreach ((array)@$values as $index => $item){
            echo $this->indent(1)."<div";
            echo $this->wrap_output('class', $this->item_css());
            echo $this->wrap_output('data-value', $item['value']);
            echo ">".PHP_EOL;
            echo $this->indent(2);
            echo "<input type='{$this->type}'";

            if (@$item['checked']){
                echo ' checked';
            }
            echo ' class="form-check-input" id="'.$this->myId(true).$item['value'].$index.'"';
            echo $this->build_form_attrs();
            $this->get_input_data($dataName);
            // 如果没有数据绑定的话，定义一个临时数据
            if (!$dataName) {
                echo $this->wrap_output('x-model.fill', $this->myid() . '_temp');
            }
            echo ' value="'.@$item['value'].'"';
            echo ">".PHP_EOL;

            echo $this->indent(2);
            echo "<label class='form-check-label' @click.stop for='".$this->myId(true).$item['value'].$index."'>";
            echo $item['text'];
            echo "</label>".PHP_EOL;

            echo $this->indent(1);
            echo "</div>".PHP_EOL;
        }
        echo "{$space}</div>".PHP_EOL;
    }

    protected function build_ui_2d_array($bindOutput, $outDataName)
    {
       $this->build_radio($bindOutput, $outDataName, true);
    }

    protected function build_ui_array($bindOutput, $outDataName)
    {
        $this->build_radio($bindOutput, $outDataName, false);
    }
    private function build_radio($bindOutput, $outDataName, $is2D)
    {
        $itemName = $bindOutput['name'];
        if ($is2D) {
            $itemNameTop = $itemName;
            $itemName .= '2';
        }
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        list('name'=>$name, 'value'=>$value) = $this->get_bind_name_value($is2D ? $bindOutput['item'] : $bindOutput, $itemName);
        echo $this->indent(1).'<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '.($is2D?"itemOf":"").$outDataName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;
        echo $this->indent(1)."<div";
        echo $this->wrap_output('class', $this->item_css());
        echo $this->wrap_output(':data-bound', "'".($is2D?"itemOf":"").$outDataName."[\''+idxOf{$itemName}+'\']'");
        echo $this->wrap_output(':data-value', $value);
        echo ">".PHP_EOL;

        $this->build_radio_input($is2D ? $bindOutput['item'] : $bindOutput, $itemName, $itemNameTop, $is2D);

        echo $this->indent(1) . "</div>".PHP_EOL;
        echo $this->indent(1) . "</template>" . PHP_EOL;

        echo "{$space}</div>".PHP_EOL;
    }
    private function build_radio_input($bindOutput, $itemName, $itemNameTop, $is2D){
        list('name'=>$name, 'value'=>$value) = $this->get_bind_name_value($bindOutput, $itemName);
        $index = ($itemNameTop ? "idxOf{$itemNameTop} + '_'+" : '').'idxOf'.$itemName;

        echo $this->indent(2);
        echo '<input type="'.$this->type.'" class="form-check-input"';
        echo $this->wrap_output(':id', "'" . $this->myId(true) . "'+{$index}");
        echo $this->wrap_output(':value', $value);

        echo $this->build_form_attrs();
        $this->get_input_data($dataName);
        // 如果没有数据绑定的话，定义一个临时数据
        if (!$dataName) {
            echo $this->wrap_output('x-model.fill', $this->myid() . '_temp'.($is2D ? "[idxOf{$itemNameTop}]" : ''));
        }
        if ($itemNameTop){
            echo $this->wrap_output(':name', "'".$this->myId(true)."'+idxOf{$itemNameTop}");
        }else{
            echo $this->wrap_output('name', $this->myId(true));
        }
        echo ">".PHP_EOL;

        echo $this->indent(2).'<label @click.stop x-text="' . $name . '" class="form-check-label"';
        echo $this->wrap_output(':for', "'" . $this->myId(true) . "'+{$index}");
        echo "></label>" . PHP_EOL;
    }

    function build_code(): Base_Code_Fragment
    {
        parent::build_code();
        $hasIterate = $this->need_iterate_data($iterateOutputAs, $outputDataName, $iterateDataName) || $this->type=='checkbox';
        $codeFragment = $this->get_code_Fragment();
        $inputData = $this->get_input_data($dataName);
        // 如果没有数据绑定的话，定义一个临时数据
        if (!$dataName) {
            $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid() . '_temp: '.($hasIterate?'[]':'""').',');
        }
        return $this->get_code_fragment();
    }
}
