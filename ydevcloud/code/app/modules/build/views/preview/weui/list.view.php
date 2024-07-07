<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\bootstrap\List_View as Bootstrap_List_View;
use app\modules\build\views\preview\Preview_View;

class List_View extends Bootstrap_List_View {
    private function item_theme($inputDataName, $currItemValue) {
        $css = ["'weui-cell weui-cell_active': true"];
        if ($inputDataName){
            $backgroundTheme = $this->data['meta']['css']['backgroundTheme'];
            if (@$backgroundTheme && $backgroundTheme != 'default'){
                $css[] = "'{$this->cssTranslate['backgroundTheme'][$backgroundTheme]} {$this->cssTranslate['borderColorClass'][$backgroundTheme]}':true";
            }
        }

        $cssMap = Preview_View::css_map();
        if (@$cssMap['foregroundTheme']) {
            $css[] = "'{$cssMap['foregroundTheme']}': true";
        }
        return "{".join(', ', $css)."}";
    }
    private function item_style($inputDataName, $currItemValue) {
        $styleMap = Preview_View::style_map();
        $fixedStyle = [];
        $varStyle = '';
        if (@$styleMap['color']) {
            $fixedStyle[] = $styleMap['color'];
        }
        if (@$styleMap['background-color']) {
            $rgba = $this->get_Rgba_Info($this->data['meta']['style']['background-color']);
            if ($inputDataName){
                $varStyle = "{$inputDataName}=={$currItemValue} ? 'background-color:rgba(".$rgba['r'].",".$rgba['g'].",".$rgba['b'].",".($rgba['a'] * 0.75).") !important':'".$styleMap['background-color']."'";
            }else{
                $fixedStyle[] = $styleMap['background-color'];
            }
            $fixedStyle[] = "border-color:".$this->data['meta']['style']['background-color']." !important";
        }
        if (!$fixedStyle && !$varStyle) return null;
        if (!$fixedStyle) return $varStyle;
        if (!$varStyle) return "'".join(';',$fixedStyle)."'";
        return "'" . join(';',$fixedStyle) . ";' + ({$varStyle})";
    }
    public function build_ui_static()
    {
        $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_value';

        $values = @$this->data['meta']['values']?:[[ "name"=> 'Sample 1', "value"=> '1' ], [ "name"=> 'Sample 2', "value"=> '2' ]];
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        echo $this->wrap_output('x-input', $inputDataName);
        echo ">".PHP_EOL;

        foreach ((array)@$values as $item){
            $myValue = addslashes($item['value']?:$item['text']);
            echo $this->indent(1) . "<div"
                .$this->wrap_output('data-value', $myValue)
                .$this->wrap_output(':class', $this->item_theme($inputDataName, "'{$myValue}'"))
                .$this->wrap_output(':style', $this->item_style($inputDataName, "'{$myValue}'")).">".PHP_EOL;
            echo $this->indent(2) . "<span class='weui-cell__bd'>".PHP_EOL;
            echo $this->indent(3) . "<span>".@$item['text']."</span>".PHP_EOL;
            echo $this->indent(2) . "</span>".PHP_EOL;
            echo $this->indent(2) . '<template x-if="'.$inputDataName.'==\''.$myValue.'\'" ><span class="weui-cell__ft"><i class="weui-icon-success"></i></span></template>'.PHP_EOL;
            echo $this->indent(1) . "</div>".PHP_EOL;
        }

        echo "{$space}</div>".PHP_EOL;
    }
    protected function build_ui_2d_array($bindOutput, $outDataName){
        $itemName = $bindOutput['name'];
        $space =  $this->indent();
        $inputData = $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_value';
        $inputDataName = $this->is_array($inputData) ? "{$inputDataName}[idxOf{$itemName}]" : $inputDataName;

        echo "{$space}<div";
        $this->build_main_attrs();
        echo $this->wrap_output('x-input', $inputDataName);
        echo ">".PHP_EOL;

        list('name'=>$name, 'value'=>$value) = $this->get_bind_name_value($bindOutput['item'], $itemName.'2');

        echo $this->indent(1) . '<template x-for="(itemOf'.$itemName.'2, idxOf'.$itemName.'2) in itemOf'.$itemName.'" :key="idxOf'.$itemName.'2">'.PHP_EOL;
        echo $this->indent(1) . "<div";
        echo $this->wrap_output(':class', $this->item_theme($inputDataName, $value));
        echo $this->wrap_output(':style', $this->item_style($inputDataName,$value));
        echo $this->wrap_output(':data-value', $value);
        echo ">".PHP_EOL;

        echo $this->indent(2) . "<span class='weui-cell__bd'";
        echo $this->wrap_output('x-text', $name);
        "></span>".PHP_EOL;
        echo $this->indent(2) . '<template x-if="'.$inputDataName.'=='.$value.'" ><span class="weui-cell__ft"><i class="weui-icon-success"></i></span></template>'.PHP_EOL;

        echo "</div>".PHP_EOL;
        echo $this->indent(1) . "</template>".PHP_EOL;

        echo "{$space}</div>".PHP_EOL;
    }
    protected function build_ui_array($bindOutput, $outDataName){
        $itemName = $bindOutput['name'];
        $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_value';

        $space =  $this->indent();
        echo "{$space}<div";
        $this->build_main_attrs();
        echo $this->wrap_output('x-input', $inputDataName);
        echo ">".PHP_EOL;
        list('name'=>$name, 'value'=>$value) = $this->get_bind_name_value($bindOutput, $itemName);

        echo $this->indent(1) . '<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '.$outDataName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;
        echo $this->indent(1) . "<div";
        echo $this->wrap_output(':class', $this->item_theme($inputDataName, $value));
        echo $this->wrap_output(':style', $this->item_style($inputDataName,$value));
        echo $this->wrap_output(':data-value', $value);
        echo ">".PHP_EOL;

        echo $this->indent(2) . "<span class='weui-cell__bd'";
        echo $this->wrap_output('x-text', $name);
        "></span>".PHP_EOL;
        echo $this->indent(2) . "<span x-if='{$inputDataName}=={$value}' class='weui-cell__ft'><i class='weui-icon-success'></i></span>".PHP_EOL;

        echo "</div>".PHP_EOL;
        echo $this->indent(1) . "</template>".PHP_EOL;

        echo "{$space}</div>".PHP_EOL;
    }
}
