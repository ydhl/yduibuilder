<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\ValueList_View;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use function yangzie\__;

class Carousel_View extends ValueList_View {
    use  Weui_Popup,Html_Code_Helper,Alpine{
        Alpine::build_code as alpineBuildCode;
    }
    protected function css_map()
    {
        $map = parent::css_map();
        $css = ['carousel slide'];
        if ($this->data['meta']['custom']['effect'] == 'crossfade'){
            $css[] = 'carousel-fade';
        }
        $map['-'] = join(' ',$css);
        return $map;
    }
    protected function placeholder_style()
    {
        $map = ['background-color:#777'];
        if (!$this->data['meta']['style']['height'] && !$this->data['meta']['style']['min-height'] ){
            $map[] = 'height:300px';
        }else{
            $map[] = 'height:' . $this->data['meta']['style']['height'];
            $map[] = 'min-height:'.$this->data['meta']['style']['min-height'];
        }
        return join(';', $map);
    }

    private function build_static_indicator(){
        if ( ! $this->data['meta']['custom']['showIndicator']) return;
        echo $this->indent(1);
        echo '<ol class="carousel-indicators">'.PHP_EOL;
        if ( ! $this->data['items']) {
            echo $this->indent(2);
            echo "<li";
            echo $this->wrap_output("data-target", "#".$this->myid(true));
            echo $this->wrap_output("data-slide-to", 0);
            echo $this->wrap_output("class", 'active');
            echo "></li>".PHP_EOL;
        }
        foreach ($this->data['items'] as $index => $item){
            echo $this->indent(2);
            echo "<li";
            echo $this->wrap_output("data-target", "#".$this->myid(true));
            echo $this->wrap_output("data-slide-to", $index);
            echo $this->wrap_output("class", !isset($this->data['meta']['custom']['activeIndex']) && !$index || $this->data['meta']['custom']['activeIndex'] == $index ? 'active' : null);
            echo "></li>".PHP_EOL;
        }

        echo $this->indent(1);
        echo "</ol>".PHP_EOL;
    }
    private function build_static_slide(){
        $this->get_input_data($inputDataName);
        echo $this->indent(1);
        echo '<div class="carousel-inner"';
        $this->build_event_listen();
        if ($inputDataName) echo $this->wrap_output('x-input', $inputDataName);
        echo '>'.PHP_EOL;
        if ( ! $this->data['items']) {
            echo $this->indent(2);
            echo '<div class="carousel-item active">'.PHP_EOL;
            echo $this->indent(3);
            echo '<div class="d-block w-100 d-flex justify-content-center align-items-center"';
            echo $this->wrap_output('style', $this->placeholder_style());
            echo ">".PHP_EOL;
            echo $this->indent(4);
            echo '<div class="display-1 text-center">';
            echo __("Slide");
            echo "</div>".PHP_EOL;
            echo $this->indent(3);
            echo "</div>".PHP_EOL;
            echo $this->indent(2);
            echo "</div>".PHP_EOL;
        }

        foreach ((array)@$this->childViews as $index => $view){
            echo $this->indent(2);
            echo "<div";
            echo $this->wrap_output('class', 'carousel-item '.(!isset($this->data['meta']['custom']['activeIndex']) && !$index || $this->data['meta']['custom']['activeIndex'] == $index ? 'active' : null));
            echo $this->wrap_output('data-value', $index);
            echo ">".PHP_EOL;
            $view->increase_indent(2);
            $view->output();

            echo $this->indent(2);
            echo "</div>".PHP_EOL;
        }

        echo $this->indent(1);
        echo "</div>".PHP_EOL;
    }
    private function build_control(){
        if ( ! $this->data['meta']['custom']['showIndicator']) return;
        $bindOutputs = $this->get_output_datas($dataName);
        $needDataBind = false;
        foreach ($bindOutputs as $outputAs => $bindOutput){
            if ($this->need_iterate_ui($outputAs, $bindOutput)){
                $needDataBind = true;
                break;
            }
        }

        echo $this->indent(1).'<a class="carousel-control-prev" type="button"';
        if ($needDataBind){
            echo $this->wrap_output(":data-target", "'#'+".$this->container_id());
        }else{
            echo $this->wrap_output("data-target", "#".$this->myid(true));
        }
        echo " data-slide=\"prev\">".PHP_EOL;
        echo $this->indent(2)."<span class='carousel-control-prev-icon' aria-hidden='true'></span>".PHP_EOL;
        echo $this->indent(1)."</a>".PHP_EOL;

        echo $this->indent(1).'<a class="carousel-control-next" type="button"';
        if ($needDataBind){
            echo $this->wrap_output(":data-target", "'#'+".$this->container_id());
        }else{
            echo $this->wrap_output("data-target", "#".$this->myid(true));
        }
        echo " data-slide='next'>".PHP_EOL;
        echo $this->indent(2)."<span class=\"carousel-control-next-icon\" aria-hidden=\"true\"></span>".PHP_EOL;
        echo $this->indent(1)."</a>".PHP_EOL;
    }

    protected function build_ui_static()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs(false);
        echo $this->wrap_output('data-ride', 'carousel');
        echo $this->wrap_output('id', $this->myid(true));
        echo ">".PHP_EOL;

        $this->build_static_indicator();
        $this->build_static_slide();
        $this->build_control();
        echo $this->indent();

        echo "</div>".PHP_EOL;
    }

    protected function build_ui_2d_array($bindOutput, $outDataName)
    {
        $this->ui($bindOutput, $outDataName, true);
    }

    protected function build_ui_array($bindOutput, $outDataName)
    {
        $this->ui($bindOutput, $outDataName, false);
    }

    public function build_code(): Base_Code_Fragment{
        $this->alpineBuildCode();
        $fragment = $this->get_code_fragment();
        $bindOutputs = $this->get_output_datas($outDataName);
        if (!$bindOutputs['VALUELIST']) return $fragment;
        $bindOutput = $bindOutputs['VALUELIST'];
        $outDataName = $outDataName['VALUELIST'];

        $indent = 0;
        // 只处理标量数组
        $is_scale = $this->is_2d_scale_array($bindOutput) || $this->is_1d_scale_array($bindOutput);
        if (!$is_scale) {
            if ($this->is_iteration($bindOutput)) $this->appendChild($indent, true);
            return $fragment;
        }

        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, 'const '.$this->myid(true).' = {};');

        $is2D = $this->is_2d_array($bindOutput);
        $itemName = $outDataName;
        $itemName = 'idxOf'.$itemName;
        $arrName = 'this.'.$outDataName;
        if ($is2D) {
            // 二维数组第一层循环
            $fragment->add_code(Html_Code_Fragment::SECTION_INIT, 'for( const '.$itemName.' in '.$arrName.'){');
            $outDataName = "itemOf{$outDataName}";
            $arrName = "{$arrName}[{$itemName}]";
            $itemName .= '2';
            $indent = 1;
        }
        $slideItemId = $this->container_id()."+{$itemName}";

        $this->appendChild($indent, false);

        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent, true).'for( const '.$itemName.' in '.$arrName.'){');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent + 1, true).$this->myid(true).'['.$slideItemId.'] = '.$arrName.'['.$itemName.'];');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent, true).'}');
        if ($is2D) {
            $fragment->add_code(Html_Code_Fragment::SECTION_INIT, '}');
        }
        // 动态加载子页
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, 'YDECloud.loadSubpages('.$this->myid(true).')');

        return $fragment;
    }
    private function appendChild($indent, $needIterable=false){
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
            $indicatorSelector =  $this->myid(true) ."\"+{$itemName}+\"";
            if ($needIterable){
                $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($origIndent, true).'for( const ' . $itemName . ' in ' . $arrName . '){');
                $indent = $origIndent + 1;
            }
        }else{
            $indicatorSelector =  $this->myid(true);
        }
        // 由于carousel 自身代码的原因 indicator和inner内的元素不能有其他的dom，所以在输出ui时把template放到他们的外面，然后这里
        // 通过脚本把动态生成的内容通过createDocumentFragment移到indicator和inner内
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent, true).'this.$nextTick(() => {');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'let elements = document.querySelectorAll("#'.$indicatorSelector.' .carousel-indicator");');
//        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'console.log("#'.$indicatorSelector.' .carousel-indicator", elements)');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'let fragment = document.createDocumentFragment();');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'elements.forEach(function(element) {');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+2, true).'fragment.appendChild(element);');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'});');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'let targetElement = document.getElementById("'.$indicatorSelector.'_indicators");');
//        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'console.log("#'.$indicatorSelector.'_indicators", targetElement)');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'targetElement.appendChild(fragment);');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, '');

        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'elements = document.querySelectorAll("#'.$indicatorSelector.' .carousel-item");');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'fragment = document.createDocumentFragment();');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'elements.forEach(function(element) {');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+2, true).'fragment.appendChild(element);');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'});');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'targetElement = document.getElementById("'.$indicatorSelector.'_inner");');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent+1, true).'targetElement.appendChild(fragment);');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->indent($indent, true).'})');
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, '');

        if ($is2D && $needIterable) {
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

    private function ui($bindOutput, $outDataName, $is2D){
        $inputData = $this->get_input_data($inputDataName);
        $itemName = $bindOutput['name'];
        $idSuffix = "idxOf{$itemName}";
        if ($is2D) {
            $itemName .= '2';
            $outDataName = "itemOf{$outDataName}";
            $idSuffix .= "+idxOf{$itemName}";
        }

        if ($this->is_scale($bindOutput) || $this->is_1d_scale_array($bindOutput)){
            $activeExp = 'idxOf'.$itemName.' == 0';
        }else{
            $activeExp = "Object.keys({$outDataName})?.[0]==idxOf{$itemName}";
        }

        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs(false);
        echo $this->wrap_output('data-ride', 'carousel');
        if ($is2D){
            echo $this->wrap_output(':id', $this->container_id());
        }else{
            echo $this->wrap_output('id', $this->myid(true));
        }
        echo ">".PHP_EOL;

        if ( $this->data['meta']['custom']['showIndicator']) {
            echo $this->indent(1).'<ol class="carousel-indicators"';
            if ($is2D){
                echo $this->wrap_output(':id', $this->container_id(). "+'_indicators'");
            }else{
                echo $this->wrap_output('id', $this->myid(true)."_indicators");
            }
            echo '>'.PHP_EOL;
            echo $this->indent(1)."</ol>".PHP_EOL;
            // bootstrap会把template当中一个元素，导致幻灯片的的索引和实际部分，因为把template当中第0个幻灯片，
            // 所以把template放到外面，并通过 init 指定到ol中（x-for情况下x-teleport之后有一个被放到ol中）
            echo $this->indent(1).'<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '
                .$outDataName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;
            echo $this->indent(1)."<li";
            if ($is2D){
                echo $this->wrap_output(":data-target", "'#'+".$this->container_id());
            }else{
                echo $this->wrap_output("data-target", "#".$this->myid(true));
            }
            echo $this->wrap_output("class", "carousel-indicator");
            echo $this->wrap_output(":data-slide-to", "idxOf{$itemName}");
            echo $this->wrap_output(':class', "{$activeExp} ? 'active' : ''");
            echo "></li>".PHP_EOL;
            echo $this->indent(1).'</template>'.PHP_EOL;
        }

        echo $this->indent(1).'<div class="carousel-inner"';
        if ($is2D){
            echo $this->wrap_output(':id', $this->container_id(). "+'_inner'");
        }else{
            echo $this->wrap_output('id', $this->myid(true)."_inner");
        }
        $this->build_event_listen();
        if ($inputDataName) {
            $inputDataName = $this->is_array($inputData) ? "{$inputDataName}[idxOf{$bindOutput['name']}]" : $inputDataName;
            echo $this->wrap_output('x-input', $inputDataName);
        }
        echo '>'.PHP_EOL;
        echo $this->indent(1)."</div>".PHP_EOL;

        echo $this->indent(1).'<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '
            .$outDataName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;

        // 只处理标量一维数组并把标量看作是要加载的子页id
        list('name'=>$xText, 'value'=>$value) = $this->get_bind_name_value($bindOutput, $itemName);

        $needLoadSubpage = $this->is_2d_scale_array($bindOutput) || $this->is_1d_scale_array($bindOutput);

        echo $this->indent(1)."<div";
        echo $this->wrap_output('class', 'carousel-item');
        echo $this->wrap_output(':data-value', $value);
        echo $this->wrap_output(':class', "{'active': {$activeExp}}");
        if ($needLoadSubpage){
            echo $this->wrap_output(':id', "'".$this->myid(true)."'+{$idSuffix}");
        }else{
            echo $this->wrap_output('x-text', $xText);
        }
        echo ">".PHP_EOL;
        echo $this->indent(1)."</div>".PHP_EOL;
        echo $this->indent(1).'</template>'.PHP_EOL;

        $this->build_control();

        echo $this->indent()."</div>".PHP_EOL;
    }
}
