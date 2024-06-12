<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Pagination_View extends ValueList_View {
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta);
        unset($styleArray['background-color'],$styleArray['color']);
        return $styleArray;
    }
    protected function activeLinkStyle()
    {
        $styleArray = parent::style_map();
        return $styleArray['background-color']
            ? $styleArray['background-color'].";border-color:{$this->data['meta']['style']['background-color']}!important;"
            : NULL;
    }
    protected function linkStyle()
    {
        $styleArray = parent::style_map();
        return $styleArray['color'] ?: NULL;
    }
    protected function css_map()
    {
        $cssArray = parent::css_map();
        unset($cssArray['backgroundTheme'],$cssArray['foregroundTheme']);
        return $cssArray;
    }
    public function linkCss() {
        $cssArray = parent::css_map();
        return @$cssArray['foregroundTheme']?:NULL;
    }
    public function activeLinkCss() {
        $cssArray = parent::css_map();
        if (!@$cssArray['backgroundTheme']) return '';
        $backgroundTheme = @$this->data['meta']['css']['backgroundTheme'];
        return $cssArray['backgroundTheme'] . ' text-light ' . @$this->cssTranslate['borderColorClass'][$backgroundTheme]?:NULL;
    }
    public function activeItemCss() {
        $cssArray = parent::css_map();
        if (!@$cssArray['backgroundTheme']) return 'active';
        return ''?:NULL;
    }

    protected function build_ui_static()
    {
        $total = max(intval($this->data['meta']['custom']['total']), 100);
        $size = max(intval($this->data['meta']['custom']['pageSize']), 1);
        $page = ceil($total / $size);
        $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_temp';

        $space =  $this->indent();
        echo "{$space}<nav";
        echo $this->build_main_attrs(false);
        echo ">".PHP_EOL;
        echo $this->indent(1).'<ul class="pagination">'.PHP_EOL;

        for($i=1; $i<=$page; $i++){
            echo $this->indent(2) . '<li';
            if ($inputDataName){
                echo $this->wrap_output(':class',  "{'page-item': true, '".$this->activeItemCss()."':{$inputDataName}=={$i}}");
            }else{
                echo $this->wrap_output('class',  "page-item ".($i==1 ? $this->activeItemCss() : ''));
            }
            echo '><a';
            if ($inputDataName){
                echo $this->wrap_output('x-input', $inputDataName);
                echo $this->wrap_output(':class', "{'page-link': true,'".$this->activeLinkCss()."': {$inputDataName}=={$i},'".$this->linkCss()."': {$inputDataName}!={$i}}");
                echo $this->wrap_output("style", $this->activeLinkStyle());
            }else{
                echo $this->wrap_output('class', "page-link ".($i==1 ? $this->activeLinkCss() : $this->linkCss()));
                echo $this->wrap_output("style", ($i==1 ? $this->activeLinkStyle() : $this->linkStyle()));
            }
            echo $this->wrap_output('data-value', $i);
            $this->build_event_listen();
            echo ' href="javascript:;">'.$i.'</a></li>'.PHP_EOL;
        }

        echo $this->indent(1) . "</ul>".PHP_EOL;

        echo "{$space}</nav>".PHP_EOL;
    }

    protected function build_ui_2d_array($bindOutput, $outDataName)
    {
        return $this->ui($bindOutput['item'], $outDataName, true);
    }

    protected function build_ui_array($bindOutput, $outDataName)
    {
       $this->ui($bindOutput, $outDataName, false);
    }
    private function ui($bindOutput, $outDataName, $is2D){
        $itemName = $outDataName;
        if ($is2D) $itemName .= '2';
        $inputData = $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_temp';

        $space =  $this->indent();
        echo "{$space}<nav";
        echo $this->build_main_attrs(false);
        if ($is2D){
            echo $this->wrap_output(':id', $this->container_id());
        }else{
            echo $this->wrap_output('id', $this->myid(true));
        }
        echo ">".PHP_EOL;
        echo $this->indent(1).'<ul class="pagination">'.PHP_EOL;
        echo $this->indent(1) . "</ul>".PHP_EOL;
        list('name'=>$name, 'value'=>$value) = $this->get_bind_name_value($bindOutput, $itemName);

        echo $this->indent(1).'<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '
            .($is2D?"itemOf":"").$outDataName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;

        echo $this->indent(1) . '<li';
        if ($inputDataName){
            $inputDataName = $this->is_array($inputData) ? "{$inputDataName}[idxOf{$outDataName}]" : $inputDataName;
            echo $this->wrap_output(':class',  "{'page-item': true, '".$this->activeItemCss()."':{$inputDataName}=={$value}}");
        }else{
            echo $this->wrap_output('class',  "page-item");
        }
        echo '><a';
        if ($inputDataName){
            echo $this->wrap_output('x-input', $inputDataName);
            echo $this->wrap_output(':class', "{'page-link': true,'".$this->activeLinkCss()."': {$inputDataName}=={$value},'".$this->linkCss()."': {$inputDataName}!={$value}}");
            echo $this->wrap_output("style", $this->activeLinkStyle());
        }else{
            echo $this->wrap_output('class', "page-link ".$this->linkCss());
            echo $this->wrap_output("style", $this->linkStyle());
        }
        echo $this->wrap_output(':data-value', $value);
        echo $this->wrap_output('data-bound', "itemOf{$itemName}");
        echo $this->wrap_output('x-text',  $value);
        $this->build_event_listen();
        echo ' href="javascript:;"></a></li>'.PHP_EOL;
        echo $this->indent(1) . "</template>".PHP_EOL;
        echo "{$space}</nav>".PHP_EOL;
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
        $this->appendChild(0);
        return $codeFragment;
    }

    private function appendChild($indent){
        $origIndent = $indent;
        $bindOutputs = $this->get_output_datas($outDataName);
        $bindOutput = $bindOutputs['VALUELIST'];
        $outDataName = $outDataName['VALUELIST'];
        $is2D = $this->is_2d_array($bindOutput);
        $fragment = $this->get_code_fragment();
        $itemName = $outDataName;
        $itemName = 'idxOf'.$itemName;
        $arrName = 'this.'.$outDataName;
        if ($is2D) {
            // 二维数组第一层循环
            $selector =  $this->myid(true) ."\"+{$itemName}+\"";
            $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($origIndent, true).'for( const ' . $itemName . ' in ' . $arrName . '){');
            $indent = $origIndent + 1;
        }else{
            $selector =  $this->myid(true);
        }
        // 由于pagination 自身代码的原因 indicator和inner内的元素不能有其他的dom，所以在输出ui时把template放到他们的外面，然后这里
        // 通过脚本把动态生成的内容通过createDocumentFragment移到pagination内
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent, true).'this.$nextTick(() => {');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'let elements = document.querySelectorAll("#'.$selector.' .page-item");');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'let fragment = document.createDocumentFragment();');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'elements.forEach(function(element) {');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+2, true).'fragment.appendChild(element);');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'});');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'let targetElement = document.querySelector("#'.$selector.' .pagination");');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'targetElement.appendChild(fragment);');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent, true).'})');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, '');

        if ($is2D) {
            $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($origIndent, true).'}');
        }
    }

    /**
     * 没有绑定输出数据或者输出数据不是2d的，则返回myid，2d的则加上第一维索引
     * @return string
     */
    private function container_id()
    {
        $bindOutputs = $this->get_output_datas($itemName);
        if (!$bindOutputs['VALUELIST'] || !$this->is_2d_array($bindOutputs['VALUELIST'])){
            return "'" . parent::myid(true) . "'";
        }
        return "'" . parent::myid(true) ."'+idxOf{$itemName['VALUELIST']}";
    }
}
