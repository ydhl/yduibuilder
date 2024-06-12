<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Fragment;

class Collapse_View extends ValueList_View {
    public function build_code(): Base_Code_Fragment{
        parent::build_code();
        $fragment = $this->get_code_fragment();
        $bindOutputs = $this->get_output_datas($outDataName);
        if (!$bindOutputs['VALUELIST']) return $fragment;
        $bindOutput = $bindOutputs['VALUELIST'];
        $outDataName = $outDataName['VALUELIST'];

        // 只处理标量数组
        $is_scale = $this->is_2d_scale_array($bindOutput) || $this->is_1d_scale_array($bindOutput);
        if (!$is_scale) return $fragment;

        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, 'const '.$this->myid(true).' = {};');

        $is2D = $this->is_2d_array($bindOutput);
        $itemName = $outDataName;
        $indent = 0;

        $containerId = "'".$this->myid(true)."_'+idxOf{$itemName}";
        $itemName = 'idxOf'.$itemName;
        $arrName = 'this.'.$outDataName;
        if ($is2D) {
            $fragment->add_code(Html_Code_Fragment::SECTION_INIT, 'for( const '.$itemName.' in '.$arrName.'){');
            $outDataName = "itemOf{$outDataName}";
            $arrName = "{$arrName}[{$itemName}]";
            $itemName .= '2';
            $containerId .= "+'_'+{$itemName}";
            $indent = 1;
        }


        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent, true).'for( const '.$itemName.' in '.$arrName.'){');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent + 1, true).$this->myid(true).'['.$containerId.'] = '.$arrName.'['.$itemName.'];');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent, true).'}');
        if ($is2D) {
            $fragment->add_code(Html_Code_Fragment::SECTION_INIT, '}');
        }
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, 'YDECloud.loadSubpages('.$this->myid(true).')');

        return $fragment;
    }

    protected function css_map()
    {
        $map = parent::css_map();
        $map['-'] = 'accordion';
        return $map;
    }

    protected function build_ui_static()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs(false);
        echo $this->wrap_output('id', $this->myid(true));
        echo ">".PHP_EOL;

        $this->build_static_item();

        echo $this->indent();
        echo "</div>".PHP_EOL;
    }

    protected function build_ui_2d_array($bindOutput, $outDataName)
    {
        $this->build_item($bindOutput, $outDataName, true);
    }

    protected function build_ui_array($bindOutput, $outDataName)
    {
        $this->build_item($bindOutput, $outDataName, false);
    }

    private function emptyContent(){
        echo $this->indent(1);
        echo '<div class="card">'.PHP_EOL;
        echo $this->indent(2);
        echo '<div class="card-header" id="'.$this->myid(true).'heading0">'.PHP_EOL;
        echo $this->indent(3);
        echo '<h2 class="mb-0">'.PHP_EOL;
        echo $this->indent(4);
        echo '<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse"
                    data-target="#'.$this->myid(true).'collapse0" aria-expanded="true" :aria-controls="'.$this->myid(true).'collapse0">';
        echo "According Header";
        echo "</button>".PHP_EOL;
        echo $this->indent(3);
        echo "</h2>".PHP_EOL;
        echo $this->indent(2);
        echo "</div>".PHP_EOL;
        echo $this->indent(2);
        echo '<div id="'.$this->myid(true).'collapse0" class="collapse show" aria-labelledby="'.$this->myid(true).'heading0" data-parent="#'.$this->myid(true).'">'.PHP_EOL;
        echo $this->indent(3);
        echo '<div class="card-body p-0">'.PHP_EOL;
        echo "According body, you can add item from Style Panel".PHP_EOL;
        echo "</div>".PHP_EOL;
        echo $this->indent(2);
        echo "</div>".PHP_EOL;
        echo $this->indent(1);
        echo "</div>".PHP_EOL;
    }
    private function build_static_item(){
        if (!$this->data['items']){
            $this->emptyContent();
            return;
        }
        $this->get_input_data($inputDataName);
        foreach ($this->childViews as $index => $view){
            echo $this->indent(1).'<div class="card">'.PHP_EOL;
            echo $this->indent(2).'<div class="card-header" id="'.$this->myid(true).'heading'.$index.'">'.PHP_EOL;
            echo $this->indent(3).'<h2 class="mb-0">'.PHP_EOL;
            echo $this->indent(4).'<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse"';
            echo $this->wrap_output('data-value', $index);
            if ($inputDataName) echo $this->wrap_output('x-input', $inputDataName);
            $this->build_event_listen();
            echo ' data-target="#'.$this->myid(true).'collapse'.$index.'" aria-expanded="true" :aria-controls="'.$this->myid(true).'collapse'.$index.'">';
            echo $view->data['meta']['title'];
            echo "</button>".PHP_EOL;
            echo $this->indent(3)."</h2>".PHP_EOL;
            echo $this->indent(2)."</div>".PHP_EOL;
            echo $this->indent(2).'<div id="'.$this->myid(true).'collapse'.$index.'" class="collapse';
            echo !isset($this->data['meta']['custom']['activeItem']) && !$index || $this->data['meta']['custom']['activeItem'] == $index ? 'show' : '';
            echo '" aria-labelledby="'.$this->myid(true).'heading'.$index.'" data-parent="#'.$this->myid(true).'">'.PHP_EOL;
            echo $this->indent(3);
            echo '<div class="card-body p-0">'.PHP_EOL;

            $view->increase_indent(3);
            $view->output();

            echo $this->indent(3);
            echo "</div>".PHP_EOL;
            echo $this->indent(2);
            echo "</div>".PHP_EOL;
            echo $this->indent(1);
            echo "</div>".PHP_EOL;
        }
    }
    private function build_item($bindOutput, $outDataName, $is2D){
        $inputData = $this->get_input_data($inputDataName);
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs(false);
        echo $this->wrap_output('id', $this->myid(true));
        echo ">".PHP_EOL;

        $itemName = $bindOutput['name'];
        $idSuffix = "idxOf{$itemName}";

        if ($is2D) {
            $itemName .= '2';
            $outDataName = "itemOf{$outDataName}";
            $idSuffix .= "+'_'+idxOf{$itemName}";
        }

        if ($this->is_scale($bindOutput) || $this->is_1d_scale_array($bindOutput)){
            $activeExp = 'idxOf'.$itemName.' == 0';
        }else{
            $activeExp = "Object.keys({$outDataName})?.[0]==idxOf{$itemName}";
        }

        echo $this->indent(1).'<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '
            .$outDataName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;

        // 只处理标量一维数组并把标量看作是要加载的子页url
        list('name'=>$xTitle, 'value'=>$xValue) = $this->get_bind_name_value($bindOutput, $itemName);
        $needLoadSubpage = $this->is_2d_scale_array($bindOutput) || $this->is_1d_scale_array($bindOutput);

        echo $this->indent(1).'<div class="card">'.PHP_EOL;
        echo $this->indent(2).'<div class="card-header" :id="\''.$this->myid(true)."heading'+".$idSuffix.'">'.PHP_EOL;
        echo $this->indent(3).'<h2 class="mb-0">'.PHP_EOL;
        echo $this->indent(4).'<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse"';
        echo $this->wrap_output(':data-value', $xValue);
        echo $this->wrap_output(':data-bound', "'{$outDataName}[\''+idxOf{$itemName}+'\']'");
        $this->build_event_listen();
        if ($inputDataName) {
            $inputDataName = $this->is_array($inputData) ? "{$inputDataName}[idxOf{$bindOutput['name']}]" : $inputDataName;
            echo $this->wrap_output('x-input', $inputDataName);
        }
        echo ' :data-target="\'#'.$this->myid(true).'collapse\'+'.$idSuffix.'" aria-expanded="true" :aria-controls="\''.$this->myid(true).'collapse\'+'.$idSuffix.'"';

        if ($needLoadSubpage){
            echo $this->wrap_output('x-text', "decodeURIComponent(\$loadSubPages[{$xValue}])");
        }else{
            echo $this->wrap_output('x-text', $xTitle);
        }
        echo "></button>".PHP_EOL;
        echo $this->indent(3)."</h2>".PHP_EOL;
        echo $this->indent(2)."</div>".PHP_EOL;
        echo $this->indent(2).'<div :id="\''.$this->myid(true).'collapse\'+'.$idSuffix.'" :class="{\'collapse\':true,\'show\':'.$activeExp.'}"';
        echo ' :aria-labelledby="\''.$this->myid(true).'heading\'+'.$idSuffix.'" data-parent="#'.$this->myid(true).'">'.PHP_EOL;
        echo $this->indent(3).'<div class="card-body p-0"';

        if ($needLoadSubpage){
            echo $this->wrap_output(':id', "'".$this->myid(true)."_'+{$idSuffix}");
        }else{
            echo $this->wrap_output('x-text', $xTitle);
        }
        echo '>'.PHP_EOL;
        echo $this->indent(3)."</div>".PHP_EOL;
        echo $this->indent(2)."</div>".PHP_EOL;
        echo $this->indent(1)."</div>".PHP_EOL;
        echo $this->indent(1).'</template>'.PHP_EOL;

        echo $this->indent()."</div>".PHP_EOL;
    }
}
