<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\bootstrap\Bootstrap_Popup;
use app\modules\build\views\preview\ValueList_View;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use function yangzie\__;

/**
 * <pre>
 * <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
 *  <ol class="carousel-indicators">
 *      <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
 *      <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
 *      <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
 *  </ol>
 *  <div class="carousel-inner">
 *      <div class="carousel-item active">
 *          <img src="..." class="d-block w-100" alt="...">
 *      </div>
 *      <div class="carousel-item">
 *          <img src="..." class="d-block w-100" alt="...">
 *      </div>
 *  </div>
 *  <button class="carousel-control-prev" type="button" data-target="#carouselExampleIndicators" data-slide="prev">
 *      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
 *      <span class="sr-only">Previous</span>
 *  </button>
 *  <button class="carousel-control-next" type="button" data-target="#carouselExampleIndicators" data-slide="next">
 *      <span class="carousel-control-next-icon" aria-hidden="true"></span>
 *      <span class="sr-only">Next</span>
 *  </button>
 * </div>
 * </pre>
 */
class Carousel_View extends ValueList_View {
    use Weui_Popup,Html_Code_Helper,Alpine{
        Alpine::build_code as alpineBuildCode;
    }

    public function is_input_ui() {
        return false;
    }

    protected function build_valuelist_static()
    {
        $this->build_ui_begin();
        $this->build_static_indicator();
        $this->build_static_slide();
        $this->build_prev_next();
        $this->build_ui_end();
    }
    protected function build_valuelist_iterator($outputData, $outDataName, $iteratorName, $itemName, $is2D = false, $firstIndex = '')
    {
        $myid = $this->myid();
        $bindOutput = $is2D ? $outputData['item'] : $outputData;

        // 只处理标量一维数组并把标量看作是要加载的子页id
        list('name'=>$xText, 'value'=>$value, 'data'=>$boundData) = $this->get_bind_name_value($bindOutput, $itemName);
        $needLoadSubpage = $this->is_2d_scale_array($outputData) || $this->is_1d_scale_array($outputData);

        if ($this->is_1d_scale_array($bindOutput)){
            $activeExp = 'idxOf'.$itemName.' == 0';
        }else{
            $activeExp = "Object.keys({$iteratorName})?.[0]==idxOf{$itemName}";
        }

        $this->build_ui_begin();
        if ( $this->data['meta']['custom']['showIndicator']) {
            echo $this->indent(1).'<ol class="carousel-indicators"';
            echo $this->wrap_output(':id', "alpinejs_get_index(\$el, '{$myid}', '-indicators')");
            echo '>'.PHP_EOL;
            echo $this->indent(1)."</ol>".PHP_EOL;
            // bootstrap会把template当中一个元素，导致幻灯片的css 选择器错误，因为把template当中第0个幻灯片，
            // 所以把template放到外面，并通过 init 指定到ol中（x-for情况下x-teleport之后有一个被放到ol中）
            echo $this->indent(1).'<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '
                .$iteratorName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;
            echo $this->indent(1)."<li";
            echo $this->wrap_output(":data-target", "alpinejs_get_index(\$el, '#{$myid}')");
            echo $this->wrap_output("class", "carousel-indicator");
            echo $this->wrap_output(":data-slide-to", "idxOf{$itemName}");
            echo $this->wrap_output(':class', "{$activeExp} ? 'active' : ''");
            echo "></li>".PHP_EOL;
            echo $this->indent(1).'</template>'.PHP_EOL;
        }

        echo $this->indent(1).'<div class="carousel-inner"';
        echo $this->wrap_output(':id', "alpinejs_get_index(\$el, '{$myid}', '-inner')");
        $this->build_event_listen();
        echo '>'.PHP_EOL;
        echo $this->indent(1)."</div>".PHP_EOL;

        echo $this->indent(1).'<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '
            .$iteratorName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;
        echo $this->indent(1)."<div";
        echo $this->wrap_output('class', 'carousel-item');
        echo $this->wrap_output(':data-value', $value);
        echo $this->wrap_output('data-bound', $boundData);
        echo $this->wrap_output('data-target', $myid);
        echo $this->wrap_output(':class', "{'active': {$activeExp}}");
        if ($needLoadSubpage){
            echo $this->wrap_output(':id', "alpinejs_get_index(\$el, '{$myid}', idxOf{$itemName} + '-item')");
        }else{
            echo $this->wrap_output('x-text', $xText);
        }
        echo ">".PHP_EOL;
        echo $this->indent(1)."</div>".PHP_EOL;
        echo $this->indent(1).'</template>'.PHP_EOL;

        $this->build_prev_next();
        echo $this->indent()."</div>".PHP_EOL;
    }
    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex=null, $iteratorName='')
    {
        // 在build_value_static和build_valuelist_iterator中处理
    }

    public function build_code(): Base_Code_Fragment{
        $this->alpineBuildCode();
        $myid = $this->myid();

        $fragment = $this->get_code_fragment();
        $bindOutputs = $this->get_output_datas($outDataName);
        if (!$bindOutputs['VALUELIST']) return $fragment;

        $bindOutput = $bindOutputs['VALUELIST'];
        $outDataName = $outDataName['VALUELIST'];

        $indent = 0;
        // 只处理标量数组
        $is_scale = $this->is_2d_scale_array($bindOutput) || $this->is_1d_scale_array($bindOutput);

        $loadSubpages = '';
        $append = '';
        if (!$is_scale) {
            if ($this->is_iteration($bindOutput)) {
                $append = $this->appendChild();
            }
        }else{
            $append = $this->appendChild();
            // 处理子页加载
            $loadSubpages = <<< SUB_PAGES

    const {$myid} = {}; 
    const {$myid}_subpages = document.querySelectorAll("[data-target='{$myid}'].carousel-item");
    for( const subpage of {$myid}_subpages){
        if (!subpage.dataset.value)continue;
        const id = subpage.id;
        {$myid}[id] = subpage.dataset.value;
    }
    
    YDECloud.loadSubpages({$myid});
SUB_PAGES;
        }

        $nextTick = <<< TICK
this.\$nextTick(() => {
    {$append}
    {$loadSubpages}
})
TICK;

        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->build->indent_code($indent, $nextTick));

        return $fragment;
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
    protected function build_ui_begin($iteratorName=null)
    {
        $space =  $this->indent();
        $myid = $this->myid();

        echo "{$space}<div";
        echo $this->build_main_attrs(false);
        echo $this->wrap_output('data-ride', 'carousel');
        echo $this->wrap_output(':id', "alpinejs_get_index(\$el, '{$myid}')");
        echo ">".PHP_EOL;
    }
    protected function build_ui_end()
    {
        echo $this->indent();
        echo "</div>".PHP_EOL;
    }

    private function placeholder_style()
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
        $myid = $this->myid();
        echo $this->indent(1);
        echo '<ol class="carousel-indicators">'.PHP_EOL;
        if ( ! $this->data['items']) {
            echo $this->indent(2);
            echo "<li";
            echo $this->wrap_output(":data-target", "alpinejs_get_index(\$el, '#{$myid}')");
            echo $this->wrap_output("data-slide-to", 0);
            echo $this->wrap_output("class", 'active');
            echo "></li>".PHP_EOL;
        }
        foreach ($this->data['items'] as $index => $item){
            echo $this->indent(2);
            echo "<li";
            echo $this->wrap_output("data-target", "alpinejs_get_index(\$el, '{$myid}')");
            echo $this->wrap_output("data-slide-to", $index);
            echo $this->wrap_output("class", !isset($this->data['meta']['custom']['activeIndex']) && !$index || $this->data['meta']['custom']['activeIndex'] == $index ? 'active' : null);
            echo "></li>".PHP_EOL;
        }

        echo $this->indent(1);
        echo "</ol>".PHP_EOL;
    }
    private function build_static_slide(){
        echo $this->indent(1);
        echo '<div class="carousel-inner"';
        $this->build_event_listen();
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
    private function build_prev_next(){
        if ( ! $this->data['meta']['custom']['showIndicator']) return;
        $myid = $this->myid();

        echo $this->indent(1).'<a class="carousel-control-prev" type="button"';
        echo $this->wrap_output(":data-target", "alpinejs_get_index(\$el, '#{$myid}')");
        echo $this->wrap_output("data-slide", "prev");
        echo ">".PHP_EOL;
        echo $this->indent(2).'<span class="carousel-control-prev-icon" aria-hidden="true"></span>'.PHP_EOL;
        echo $this->indent(1)."</a>".PHP_EOL;

        echo $this->indent(1).'<a class="carousel-control-next" type="button"';
        echo $this->wrap_output(":data-target", "alpinejs_get_index(\$el,'#{$myid}')");
        echo " data-slide='next'>".PHP_EOL;
        echo $this->indent(2).'<span class="carousel-control-next-icon" aria-hidden="true"></span>'.PHP_EOL;
        echo $this->indent(1)."</a>".PHP_EOL;
    }
    private function appendChild(){;
        $myid = $this->myid();

        // 由于carousel 自身代码的原因 indicator和inner内的元素不能有其他的dom，所以在输出ui时把template放到他们的外面，然后这里
        // 通过脚本把动态生成的内容通过createDocumentFragment移到indicator和inner内
        return <<< APPEND

    const {$myid}_uis = document.querySelectorAll("[data-uiid='{$myid}']");
    for( const ui of {$myid}_uis){
        const id = ui.id;
        if (!id) continue;
        let elements = document.querySelectorAll(`#\${id} .carousel-indicator`);
        let fragment = document.createDocumentFragment();
        elements.forEach(function(element) {
            fragment.appendChild(element);
        });
        let targetElement = document.getElementById(`\${id}-indicators`);
        targetElement.appendChild(fragment);
    
        elements = document.querySelectorAll(`#\${id} .carousel-item`);
        fragment = document.createDocumentFragment();
        elements.forEach(function(element) {
            fragment.appendChild(element);
        });
        targetElement = document.getElementById(`\${id}-inner`);
        targetElement.appendChild(fragment);
    }
APPEND;
    }
}
