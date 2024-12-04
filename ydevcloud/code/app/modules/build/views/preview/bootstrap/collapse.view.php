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
            $this->output_event_listen_props();
            echo ' data-bs-target="#'.$this->myid(true).'collapse'.$index.'" aria-expanded="true" :aria-controls="'.$this->myid(true).'collapse'.$index.'">';
            echo $view->data['meta']['title'];
            echo "</button>".PHP_EOL;
            echo $this->indent(2)."</div>".PHP_EOL;
            echo $this->indent(2).'<div id="'.$this->myid(true).'collapse'.$index.'" class="accordion-collapse collapse';
            echo !isset($this->data['meta']['custom']['activeItem']) && !$index || $this->data['meta']['custom']['activeItem'] == $index ? 'show' : '';
            echo '" aria-labelledby="'.$this->myid(true).'heading'.$index.'" data-parent="#'.$this->myid(true).'">'.PHP_EOL;
            echo $this->indent(3).'<div class="accordion-body p-0">'.PHP_EOL;

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
        echo $this->output_main_attrs(false);
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

        if (!$this->is_1d_object_array($bindOutput)) {
            return $fragment;
        }

        $nextTick = <<< TICK
this.\$nextTick(() => {
    const {$myid} = {}; 
    const {$myid}_subpages = document.querySelectorAll("[data-bs-target='{$myid}'].accordion-body");
    for( const subpage of {$myid}_subpages){
        if (!subpage.dataset.value)continue;
        const id = subpage.id;
        {$myid}[id] = subpage.dataset.value;
    }
    console.log({$myid})
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
        if (!$this->is_1d_object_array($bindOutput)){
            $this->build->output_code(__('can not bound to type '.$bindOutput['type']), $indent);
            return;
        }
        // 只处理object数组，value看作是要加载的子页url，key看作标题
        list('name'=>$xTitle, 'value'=>$xValue, 'data'=>$boundData) = $this->get_bind_name_value($bindOutput, $itemName);
        $activeExp = 'idxOf'.$itemName.' == 0';
        ob_start();
        $this->output_event_listen_props();
        $eventListen = ob_get_clean();

        $html = <<<HTML
<div class="accordion-item">
    <div class="accordion-header" :id="alpinejs_get_index(\$el, '{$myid}', idxOf{$itemName} + '-header')">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bound="{$boundData}"
            :data-value="{$xValue}"{$eventListen} :data-bs-target="alpinejs_get_index(\$el, '#{$myid}', idxOf{$itemName} + '-collapse')"
            aria-expanded="true" :aria-controls="alpinejs_get_index(\$el, '{$myid}', idxOf{$itemName} + '-collapse')"
            x-text="{$xTitle}">
        </button>
    </div>
    <div :id="alpinejs_get_index(\$el, '{$myid}', idxOf{$itemName} + '-collapse')" :class="{'accordion-collapse collapse': true, 'show': {$activeExp}}"
        :aria-labelledby="alpinejs_get_index(\$el, '{$myid}', idxOf{$itemName} + '-header')" :data-value="{$xValue}" data-bs-target="{$myid}"
        :data-parent="alpinejs_get_index(\$el, '#{$myid}')">
        <div :id="alpinejs_get_index(\$el, '{$myid}', idxOf{$itemName} + '-body')" :data-value="{$xValue}" data-bs-target="{$myid}" class="accordion-body p-0"></div>
    </div>
</div>
HTML;
        $this->build->output_code($html, $indent);
    }
    protected function emptyContent(){
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
