<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\ValueList_View;


class Nav_View extends ValueList_View {
    use Bootstrap_Popup,Html_Code_Helper,Alpine {
        Alpine::build_code as alpineBuildCode;
    }

    private function theme_css($inputDataName, $staticData, $bindDataName=null) {
        $css = ["nav-link"];
        $theme = $this->data['meta']['css']['foregroundTheme'];
        if ($theme && $theme !== 'default'){
            $checked = $this->cssTranslate['backgroundTheme'][$theme].' text-white';
            $unchecked = $this->cssTranslate['foregroundTheme'][$theme];
        }

        $value = $staticData['value']?"'{$staticData['value']}'":$bindDataName;
        $css = ["'".join(' ', $css)."': true"];
        if ($inputDataName){
            $css[] = "'{$checked} active': {$inputDataName}=={$value}";
            if ($unchecked) $css[] = "'{$unchecked}': {$inputDataName}!={$value}";
        }else if (@$staticData['checked']){
            $css[] = "'{$checked} active': true";
        }else{
            if($unchecked) $css[] = "'{$unchecked}': true";
        }
        return "{".join(', ', $css)."}";
    }

    private function theme_style($inputDataName, $staticData, $bindDataName=null) {
        $color = $this->data['meta']['style']['color'];
        if (!$color){
            return null;
        }
        $value = $staticData['value']?"'{$staticData['value']}'":$bindDataName;

        $checkedStyle = "background-color:{$color} !important;color:#fff;";
        $uncheckedStyle = "color:{$color} !important;";
        if ($inputDataName){
            return "{$inputDataName}=={$value} ? '$checkedStyle' : '{$uncheckedStyle}'";
        }else{
            return $staticData['checked'] ? "'{$checkedStyle}'" : "'{$uncheckedStyle}'";
        }
    }
    protected function css_map()
    {
        $cssMap = parent::css_map();
        unset($cssMap['foregroundTheme']);
        $arr = [];
        $arr[] = 'nav';
        if (@$this->data['meta']['custom']['type'] == 'tab'){
            $arr[] =  'nav-tabs';
        }
        if (@$this->data['meta']['custom']['type'] == 'pill'){
            $arr[] =  'nav-pills';
        }
        if (@$this->data['meta']['custom']['justified']){
            $arr[] =  'nav-justified';
        }
        if (@$this->data['meta']['custom']['filled']){
            $arr[] =  'nav-fill';
        }

        $parentUI = $this->get_parent_UI();
        $parentIsCard = in_array(strtolower($parentUI['type']), ['card']);

        if ($parentIsCard && @$this->data['meta']['custom']['type'] == 'tab'){
            $arr[] = 'card-header-tabs';
        }
        if ($parentIsCard && @$this->data['meta']['custom']['type'] == 'pill'){
            $arr[] = 'card-header-pills';
        }

        $cssMap['-'] = join(' ', $arr);
        return $cssMap;
    }

    protected function build_ui_static()
    {
        $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_temp';
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '#' ], [ "text"=> 'Sample 2', "value"=> '#', 'checked'=> true ]];
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        if ($inputDataName) echo $this->wrap_output('x-input', $inputDataName);
        echo ">".PHP_EOL;

        foreach ((array)@$values as $item){
            echo $this->indent(1) . "<div class='nav-item'>".PHP_EOL;
            echo $this->indent(2) . "<a"
                .$this->wrap_output(':class', $this->theme_css($inputDataName, $item))
                .$this->wrap_output(':style', $this->theme_style($inputDataName, $item))
                .$this->wrap_output('href', "javascript:;")
                .$this->wrap_output('data-value', $item['value']?:$item['text'])
                .">{$item['text']}</a>".PHP_EOL;
            echo $this->indent(1) . "</div>".PHP_EOL;
        }
        foreach ((array)@$this->childViews as $view){
            $view->output();
        }
        echo "{$space}</div>".PHP_EOL;
    }

    protected function build_ui_2d_array($bindOutput, $outDataName)
    {
        $inputData = $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_temp';
        $itemName = $bindOutput['name'];
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        if ($inputDataName) {
            $inputDataName = $this->is_array($inputData) ? "{$inputDataName}[idxOf{$itemName}]" : $inputDataName;
            echo $this->wrap_output('x-input', $inputDataName);
        }
        echo ">".PHP_EOL;

        $this->ui($bindOutput['item'], $outDataName, true, $itemName);

        echo "{$space}</div>".PHP_EOL;
    }
    protected function build_ui_array($bindOutput, $outDataName)
    {
        $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_temp';
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        if ($inputDataName) echo $this->wrap_output('x-input', $inputDataName);
        echo ">".PHP_EOL;

        $this->ui($bindOutput, $outDataName, false);

        echo "{$space}</div>".PHP_EOL;
    }
    private function ui($bindOutput, $outDataName, $is2D, $topDataName=null) {
        $itemName = $outDataName;
        $this->get_input_data($inputDataName);
        if ($is2D) $itemName .= '2';
        list('name'=>$xText, 'value'=>$xValue) = $this->get_bind_name_value($bindOutput, $itemName);

        echo $this->indent(1).'<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '
            .($is2D?"itemOf":"").$outDataName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;
        echo $this->indent(1) . '<div class="nav-item">'.PHP_EOL;
        echo $this->indent(2) . "<a";

        echo $this->wrap_output(':class',$this->theme_css($is2D ? "{$inputDataName}[idxOf{$topDataName}]" : $inputDataName, [], $xValue));
        echo $this->wrap_output(':style',$this->theme_style($is2D ? "{$inputDataName}[idxOf{$topDataName}]" : $inputDataName, [], $xValue));

        echo $this->wrap_output('href', 'javascript:;');
        echo $this->wrap_output('x-text', $xText);
        echo $this->wrap_output(':data-value', $xValue);
        if ($is2D){
            echo $this->wrap_output(':data-bound', "'itemOf{$outDataName}[\''+idxOf{$itemName}+'\']'");
        }else{
            echo $this->wrap_output(':data-bound', "'{$outDataName}[\''+idxOf{$itemName}+'\']'");
        }
        echo '></a>'.PHP_EOL;
        echo $this->indent(1) . "</div>".PHP_EOL;
        echo $this->indent(1) . "</template>".PHP_EOL;

        foreach ((array)@$this->childViews as $view){
            $view->output();
        }
    }
    function build_code(): Base_Code_Fragment
    {
        $this->alpineBuildCode();
        $codeFragment = $this->get_code_Fragment();
        $inputData = $this->get_input_data($dataName);
        // 如果没有数据绑定的话，定义一个临时数据
        if (!$dataName) {
            $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid() . '_temp: "",');
        }
        return $codeFragment;
    }
}
