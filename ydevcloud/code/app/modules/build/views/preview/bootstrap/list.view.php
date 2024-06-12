<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use function yangzie\__;


class List_View extends ValueList_View {
    private function bind_theme($inputDataName, $currItemValue) {
        $css = ["'list-group-item list-group-item-action': true"];
        if ($inputDataName){
            $css[] = "'active': {$inputDataName}=={$currItemValue}";
            if (@$this->data['meta']['css']['backgroundTheme']){
                $css[] = "'{$this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['backgroundTheme']]}':{$inputDataName}=={$currItemValue}";
                $css[] = "'{$this->cssTranslate['borderColorClass'][$this->data['meta']['css']['backgroundTheme']]}':{$inputDataName}=={$currItemValue}";
            }
        }

        $cssMap = parent::css_map();
        if (@$this->data['meta']['css']['backgroundTheme']
            && $this->data['meta']['css']['backgroundTheme']!='default'){
            $css[] = "'list-group-item-{$this->data['meta']['css']['backgroundTheme']}': true";
        }
        if (@$cssMap['foregroundTheme']) {
            $css[] = "'{$cssMap['foregroundTheme']}': true";
        }
        return "{".join(', ', $css)."}";
    }
    private function list_theme($inputDataName, $item) {
        $css = ["'list-group-item list-group-item-action': true"];
        if ($inputDataName){
            $css[] = "'active': {$inputDataName}=='{$item['value']}'";
        }else if (@$item['checked']){
            $css[] = "'active': true";
        }
        if (@$this->data['meta']['css']['backgroundTheme']){
            $css[] = "'".$this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['backgroundTheme']]."': true";
            $css[] = "'".$this->cssTranslate['borderColorClass'][$this->data['meta']['css']['backgroundTheme']]."': true";
        }

        $cssMap = parent::css_map();
        if (@$this->data['meta']['css']['backgroundTheme']
            && $this->data['meta']['css']['backgroundTheme']!='default'){
            $css[] = "'list-group-item-" . $this->data['meta']['css']['backgroundTheme']."': true";
        }
        if (@$cssMap['foregroundTheme']) {
            $css[] = "'".$cssMap['foregroundTheme']."': true";
        }
        return "{".join(', ', $css)."}";
    }
    private function list_style($inputDataName, $item) {
        $styleMap = parent::style_map();
        $fixedStyle = [];
        if (@$styleMap['color']) {
            $fixedStyle[] = $styleMap['color'];
        }
        if (@$styleMap['background-color']) {
            $rgba = $this->get_Rgba_Info($this->data['meta']['style']['background-color']);
            $style = "background-color:rgba(".$rgba['r'].",".$rgba['g'].",".$rgba['b'].",".($rgba['a'] * 0.75).") !important";
            if ($inputDataName){
                $varStyle = "{$inputDataName}=='{$item['value']}' ? '{$style}' : ''";
            }else if (@$item['checked']){
                $fixedStyle[] = $style;
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
    private function bind_style($inputDataName, $currItemValue) {
        $styleMap = parent::style_map();
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
    protected function css_map()
    {
        $arrMap = parent::css_map();
        unset($arrMap['backgroundTheme'], $arrMap['foregroundTheme']);
        $arr = [];
        if (@$this->data['meta']['custom']['horizontal']){
            $arr[] = 'list-group-horizontal';
        }
        if (@$this->data['meta']['custom']['flush']){
            $arr[] = 'list-group-flush';
        }
        $arr[] = 'list-group';
        $arrMap['-'] = join(' ', $arr);
        return $arrMap;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta);
        unset($styleArray['color'], $styleArray['background-color']);
        return $styleArray;
    }

    protected function build_ui_static()
    {
        $this->get_input_data($inputDataName);
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '1' ], [ "text"=> 'Sample 2', "value"=> '2' ]];
        $space =  $this->indent();
        echo "{$space}<div";
        $this->build_main_attrs();
        if (!$inputDataName) $inputDataName = $this->myid() . '_temp';
        echo $this->wrap_output('x-input', $inputDataName);
        echo ">".PHP_EOL;

        foreach ((array)@$values as $item){
            echo $this->indent(1) . "<a href='javascript:;'"
                .$this->wrap_output(':class', $this->list_theme($inputDataName, $item))
                .$this->wrap_output(':style', $this->list_style($inputDataName, $item))
                .$this->wrap_output('data-value', $item['value']?:$item['text'])
                .">".PHP_EOL;
            echo $this->indent(2) . ($item['text']).PHP_EOL;
            echo $this->indent(1) . "</a>".PHP_EOL;
        }
        echo "{$space}</div>".PHP_EOL;
    }
    protected function build_ui_2d_array($bindOutput, $outDataName){
        $itemName = $bindOutput['name'];
        $space =  $this->indent();
        $inputData = $this->get_input_data($inputDataName);

        echo "{$space}<div";
        $this->build_main_attrs();
        if (!$inputDataName) $inputDataName = $this->myid() . '_temp';
        $inputDataName = $this->is_array($inputData) ? "{$inputDataName}[idxOf{$itemName}]" : $inputDataName;
        echo $this->wrap_output('x-input', $inputDataName);
        echo ">".PHP_EOL;

        list('name'=>$name, 'value'=>$value) = $this->get_bind_name_value($bindOutput['item'], $itemName.'2');

        echo $this->indent(1) . '<template x-for="(itemOf'.$itemName.'2, idxOf'.$itemName.'2) in itemOf'.$itemName.'" :key="idxOf'.$itemName.'2">'.PHP_EOL;
        echo $this->indent(1) . "<a href='javascript:;'";
        echo $this->wrap_output(':class', $this->bind_theme($inputDataName, $value));
        echo $this->wrap_output(':style', $this->bind_style($inputDataName,$value));

        echo $this->wrap_output('x-text', $name);
        echo $this->wrap_output(':data-bound', "'itemOf{$itemName}[\''+idxOf{$itemName}2+'\']'");
        echo $this->wrap_output(':data-value', $value);

        echo "></a>".PHP_EOL;
        echo $this->indent(1) . "</template>".PHP_EOL;

        echo "{$space}</div>".PHP_EOL;
    }
    protected function build_ui_array($bindOutput, $outDataName){
        $itemName = $bindOutput['name'];
        $this->get_input_data($inputDataName);

        $space =  $this->indent();
        echo "{$space}<div";
        $this->build_main_attrs();
        if (!$inputDataName) $inputDataName = $this->myid() . '_temp';
        echo $this->wrap_output('x-input', $inputDataName);
        echo ">".PHP_EOL;
        list('name'=>$name, 'value'=>$value) = $this->get_bind_name_value($bindOutput, $itemName);


        echo $this->indent(1) . '<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '.$outDataName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;
        echo $this->indent(1) . "<a href='javascript:;'";
        echo $this->wrap_output(':class', $this->bind_theme($inputDataName,$value));
        echo $this->wrap_output(':style', $this->bind_style($inputDataName,$value));
        echo $this->wrap_output('x-text', $name);
        echo $this->wrap_output(':data-bound', "'{$outDataName}[\''+idxOf{$itemName}+'\']'");
        echo $this->wrap_output(':data-value', $value);
        echo "></a>".PHP_EOL;
        echo $this->indent(1) . "</template>".PHP_EOL;

        echo "{$space}</div>".PHP_EOL;
    }

    function build_code(): Base_Code_Fragment
    {
        parent::build_code();
        $codeFragment = $this->get_code_Fragment();
        $inputData = $this->get_input_data($dataName);
        // 如果没有数据绑定的话，定义一个临时数据
        if (!$dataName) {
            $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid() . '_temp: "",');
        }
        return $codeFragment;
    }
}
