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
 *  <div class="card">
 *      <div class="card-header" id="headingOne">
 *          <h2 class="mb-0">
 *              <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">Collapsible Group Item #1</button>
 *          </h2>
 *      </div>
 *      <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
 *          Some placeholder content for the first accordion panel. This panel is shown by default, thanks to the <code>.show</code> class.
 *      </div>
 *  </div>
 * </div>
 * </pre>
 */
class Collapse_View extends ValueList_View {
    use Bootstrap_Popup,Html_Code_Helper,Alpine {
        Alpine::build_code as alpineBuildCode;
    }

    protected function build_valuelist_static()
    {
        $this->build_ui_begin();
        if (!$this->data['items']){
            $this->emptyContent();
            return;
        }

        foreach ($this->childViews as $index => $view){
            echo $this->indent(1).'<div class="card">'.PHP_EOL;
            echo $this->indent(2).'<div class="card-header" id="'.$this->myid(true).'heading'.$index.'">'.PHP_EOL;
            echo $this->indent(3).'<h2 class="mb-0">'.PHP_EOL;
            echo $this->indent(4).'<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse"';
            echo $this->wrap_output('data-value', $index);
            $this->build_event_listen();
            echo ' data-target="#'.$this->myid(true).'collapse'.$index.'" aria-expanded="true" :aria-controls="'.$this->myid(true).'collapse'.$index.'">';
            echo $view->data['meta']['title'];
            echo "</button>".PHP_EOL;
            echo $this->indent(3)."</h2>".PHP_EOL;
            echo $this->indent(2)."</div>".PHP_EOL;
            echo $this->indent(2).'<div id="'.$this->myid(true).'collapse'.$index.'" class="collapse';
            echo !isset($this->data['meta']['custom']['activeItem']) && !$index || $this->data['meta']['custom']['activeItem'] == $index ? 'show' : '';
            echo '" aria-labelledby="'.$this->myid(true).'heading'.$index.'" data-parent="#'.$this->myid(true).'">'.PHP_EOL;


            $view->increase_indent(2);
            $view->output();

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
    protected function build_valuelist($outputData, $itemName, $staticData = null)
    {
        // 在build_value_static和build_valuelist_iterator中处理
    }
    protected function build_ui_begin()
    {
        $space =  $this->indent();
        $myid = $this->myid();
        $inputDataName = $this->get_input_data_name();
        echo "{$space}<div";
        echo $this->build_main_attrs(false);
        echo $this->wrap_output('x-id', "['{$myid}']");
        echo $this->wrap_output(':id', "alpinejs_get_index(\$el, '{$myid}')");
        echo $this->wrap_output('x-input', $inputDataName);
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
    const {$myid}_subpages = document.querySelectorAll("[data-target='{$myid}'].collapse");
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
        list('name'=>$xTitle, 'value'=>$xValue) = $this->get_bind_name_value($bindOutput, $itemName);
        $needLoadSubpage =  $this->is_2d_scale_array($bindOutput) || $this->is_1d_scale_array($bindOutput);
        if ($this->is_1d_scale_array($bindOutput)){
            $activeExp = 'idxOf'.$itemName.' == 0';
        }else{
            $activeExp = "Object.keys({$iteratorName})?.[0]==idxOf{$itemName}";
        }
        ob_start();
        $this->build_event_listen();
        $eventListen = ob_get_clean();
        $headerText = $needLoadSubpage ? "decodeURIComponent(\$loadSubPages[{$xValue}])" : $xTitle;
        if (!$needLoadSubpage){
            $bodyAttr = ' x-text="'.$xTitle.'"';
        }

        $html = <<<HTML
<div class="card">
    <div class="card-header" :id="alpinejs_get_index(\$el, '{$myid}', idxOf{$itemName} + '-header')">
        <h2 class="mb-0">
            <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse"
            :data-value="{$xValue}"{$eventListen} :data-target="alpinejs_get_index(\$el, '#{$myid}', idxOf{$itemName} + '-collapse')"
            aria-expanded="true" :aria-controls="alpinejs_get_index(\$el, '{$myid}', idxOf{$itemName} + '-collapse')"
            x-text="{$headerText}"></button>
        </h2>
    </div>
    <div :id="alpinejs_get_index(\$el, '{$myid}', idxOf{$itemName} + '-collapse')" :class="{'collapse': true, 'show': {$activeExp}}"
        :aria-labelledby="alpinejs_get_index(\$el, '{$myid}', idxOf{$itemName} + '-header')" :data-value="{$xValue}" data-target="{$myid}"
        :data-parent="alpinejs_get_index(\$el, '#{$myid}')"{$bodyAttr}>
    </div>
</div>
HTML;
        $this->build->output_code($html, $indent);
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
}
