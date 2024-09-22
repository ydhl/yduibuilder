<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\ValueList_View;

/**
 * <pre>
 * <div class="accordion" id="accordionExample">
 *  <div class="accordion-item">
 *      <h2 class="accordion-header">
 *          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
 *              Accordion Item #1
 *          </button>
 *      </h2>
 *      <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
 *          <div class="accordion-body">
 *              <strong>This is the first item's accordion body.</strong> It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
 *          </div>
 *      </div>
 *  </div>
 * </div>
 * </pre>
 */
class Collapse_View extends ValueList_View {
    use Bootstrap_Popup,Html_Code_Helper,Alpine {
        Alpine::build_code as alpineBuildCode;
    }

    public function is_input_ui() {
        return false;
    }
    protected function build_valuelist_static()
    {
        $this->build_ui_begin();
        if (!$this->data['items']){
            $this->emptyContent();
            return;
        }

        foreach ($this->childViews as $index => $view){
            echo $this->indent(1).'<div class="accordion-item">'.PHP_EOL;
            echo $this->indent(2).'<div class="accordion-header" id="'.$this->myid(true).'heading'.$index.'">'.PHP_EOL;
            echo $this->indent(3).'<button class="accordion-button" type="button" data-bs-toggle="collapse"';
            echo $this->wrap_output('data-value', $index);
            $this->build_event_listen();
            echo ' data-bs-target="#'.$this->myid(true).'collapse'.$index.'" aria-expanded="true" :aria-controls="'.$this->myid(true).'collapse'.$index.'">';
            echo $view->data['meta']['title'];
            echo "</button>".PHP_EOL;
            echo $this->indent(2)."</div>".PHP_EOL;
            echo $this->indent(2).'<div id="'.$this->myid(true).'collapse'.$index.'" class="accordion-collapse collapse';
            echo !isset($this->data['meta']['custom']['activeItem']) && !$index || $this->data['meta']['custom']['activeItem'] == $index ? 'show' : '';
            echo '" aria-labelledby="'.$this->myid(true).'heading'.$index.'" data-parent="#'.$this->myid(true).'">'.PHP_EOL;
            echo $this->indent(3).'<div class="accordion-body">'.PHP_EOL;

            $view->increase_indent(3);
            $view->output();

            echo $this->indent(3);
            echo "</div>".PHP_EOL;
            echo $this->indent(2);
            echo "</div>".PHP_EOL;
            echo $this->indent(1);
            echo "</div>".PHP_EOL;
        }
        $this->build_ui_end();
    }
    protected function build_valuelist_iterator($outputData, $outDataName, $iteratorName, $itemName, $is2D = false, $firstIndex = '')
    {
        $this->build_ui_begin();

        $bindOutput = $is2D ? $outputData['item'] : $outputData;
        echo $this->indent(1).'<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '
            .$iteratorName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;
        $this->build_item($bindOutput, $iteratorName, $itemName, $this->build->get_indent()+1);
        echo $this->indent(1).'</template>'.PHP_EOL;

        $this->build_ui_end();
    }
    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex=null, $iteratorName='')
    {
        // 在build_value_static和build_valuelist_iterator中处理
    }
    protected function build_ui_begin($iteratorName=null)
    {
        $space =  $this->indent();
        $myid = $this->myid();
        echo "{$space}<div";
        echo $this->build_main_attrs(false);
        echo $this->wrap_output(':id', "alpinejs_get_index(\$el, '{$myid}')");
        echo ">".PHP_EOL;
    }
    protected function build_ui_end()
    {
        echo $this->indent();
        echo "</div>".PHP_EOL;
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

        if (!$is_scale) {
            return $fragment;
        }

        $nextTick = <<< TICK
this.\$nextTick(() => {
    const {$myid} = {}; 
    const {$myid}_subpages = document.querySelectorAll("[data-bs-target='{$myid}'].collapse");
    for( const subpage of {$myid}_subpages){
        if (!subpage.dataset.value)continue;
        const id = subpage.id;
        {$myid}[id] = subpage.dataset.value;
    }
    YDECloud.loadSubpages({$myid});
})
TICK;

        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $this->build->indent_code($indent, $nextTick));

        return $fragment;
    }
    protected function css_map()
    {
        $map = parent::css_map();
        $map['-'] = 'accordion';
        return $map;
    }
    private function build_item($bindOutput, $iteratorName, $itemName, $indent){
        $myid = $this->myid();
        // 只处理标量一维数组并把标量看作是要加载的子页url
        list('name'=>$xTitle, 'value'=>$xValue, 'data'=>$boundData) = $this->get_bind_name_value($bindOutput, $itemName);
        $needLoadSubpage =  $this->is_2d_scale_array($bindOutput) || $this->is_1d_scale_array($bindOutput);
        if ($this->is_1d_scale_array($bindOutput)){
            $activeExp = 'idxOf'.$itemName.' == 0';
        }else{
            $activeExp = "Object.keys({$iteratorName})?.[0]==idxOf{$itemName}";
        }
        ob_start();
        $this->build_event_listen();
        $eventListen = ob_get_clean();
        $headerText = $needLoadSubpage ? "decodeURIComponent(\$store.loadSubPages[{$xValue}])" : $xTitle;
        if (!$needLoadSubpage){
            $bodyAttr = ' x-text="'.$xTitle.'"';
        }

        $html = <<<HTML
<div class="accordion-item">
    <div class="accordion-header" :id="alpinejs_get_index(\$el, '{$myid}', idxOf{$itemName} + '-header')">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bound="{$boundData}"
            :data-value="{$xValue}"{$eventListen} :data-bs-target="alpinejs_get_index(\$el, '#{$myid}', idxOf{$itemName} + '-collapse')"
            aria-expanded="true" :aria-controls="alpinejs_get_index(\$el, '{$myid}', idxOf{$itemName} + '-collapse')"
            x-text="{$headerText}">
        </button>
    </div>
    <div :id="alpinejs_get_index(\$el, '{$myid}', idxOf{$itemName} + '-collapse')" :class="{'accordion-collapse collapse': true, 'show': {$activeExp}}"
        :aria-labelledby="alpinejs_get_index(\$el, '{$myid}', idxOf{$itemName} + '-header')" :data-value="{$xValue}" data-bs-target="{$myid}"
        :data-parent="alpinejs_get_index(\$el, '#{$myid}')">
        <div {$bodyAttr} class="accordion-body"></div>
    </div>
</div>
HTML;
        $this->build->output_code($html, $indent);
    }
    private function emptyContent(){
        echo $this->indent(1);
        echo '<div class="accordion-item">'.PHP_EOL;
        echo $this->indent(2);
        echo '<div class="accordion-header" id="'.$this->myid(true).'heading0">'.PHP_EOL;
        echo $this->indent(3);
        echo '<button class="accordion-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#'.$this->myid(true).'collapse0" aria-expanded="true" :aria-controls="'.$this->myid(true).'collapse0">';
        echo "According Header";
        echo "</button>".PHP_EOL;
        echo $this->indent(2);
        echo "</div>".PHP_EOL;
        echo $this->indent(2);
        echo '<div id="'.$this->myid(true).'collapse0" class="accordion-collapse collapse show" aria-labelledby="'.$this->myid(true).'heading0" data-parent="#'.$this->myid(true).'">'.PHP_EOL;
        echo $this->indent(3);
        echo '<div class="accordion-body p-0">'.PHP_EOL;
        echo "According body, you can add item from Style Panel".PHP_EOL;
        echo "</div>".PHP_EOL;
        echo $this->indent(2);
        echo "</div>".PHP_EOL;
        echo $this->indent(1);
        echo "</div>".PHP_EOL;
    }
}
