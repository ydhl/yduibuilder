<?php
namespace app\modules\build\views\code\web\bootstrap_vue;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Collapse_View as Preview_Collapse_View;
use app\modules\build\views\code\web\Vue;
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
class Collapse_View extends Preview_Collapse_View {
    use Vue {
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo $space."<CollapseComponent";
        $this->output_component_props();
        echo '>'.PHP_EOL;
        echo $space.'<template v-slot="{click, dblclick, mousedown, mouseup, mouseover, mouseout, mousemove, mouseenter, mouseleave}">'.PHP_EOL;
        $this->output_items();
        echo $space."</template>".PHP_EOL;
        echo $space."</CollapseComponent>".PHP_EOL;
    }
    public function build_code():Base_Code_Fragment
    {
        $this->vueBuildCode();
        $bindOutputs = $this->get_output_datas($outDataName);
        $fragment = $this->get_code_fragment();
        $fragment->add_import('@/components/CollapseComponent.vue', [], 'CollapseComponent');
        if($bindOutputs['VALUELIST']) $fragment->add_import('@/components/SubpageComponent.vue', [], 'SubpageComponent');
        return $fragment;
    }
    private function output_items(){
        $bindOutputs = $this->get_output_datas($outDataName);
        if (!$bindOutputs['VALUELIST']){// 静态
            if ( ! $this->data['items']) {
                $this->emptyContent();
                return;
            }

            foreach ((array)@$this->childViews as $index => $view){
                echo $this->indent(1).'<div class="accordion-item">'.PHP_EOL;
                echo $this->indent(2).'<div class="accordion-header" id="'.$this->myid(true).'heading'.$index.'">'.PHP_EOL;
                echo $this->indent(3).'<button class="accordion-button" type="button" data-bs-toggle="collapse"';
                echo $this->wrap_output('@click', "click(\$event, {$index})");
                echo $this->wrap_output('@dblclick', "dblclick(\$event, {$index})");
                echo $this->wrap_output('@mousedown', "mousedown(\$event, {$index})");
                echo $this->wrap_output('@mouseup', "mouseup(\$event, {$index})");
                echo $this->wrap_output('@mouseover', "mouseover(\$event, {$index})");
                echo $this->wrap_output('@mouseout', "mouseout(\$event, {$index})");
                echo $this->wrap_output('@mousemove', "mousemove(\$event, {$index})");
                echo $this->wrap_output('@mouseenter', "mouseenter(\$event, {$index})");
                echo $this->wrap_output('@mouseleave', "mouseleave(\$event, {$index})");
                echo ' data-bs-target="#'.$this->myid(true).'collapse'.$index.'" aria-expanded="true" aria-controls="'.$this->myid(true).'collapse'.$index.'">';
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
            return;
        }

        if ($this->need_iterate_ui('VALUELIST', $bindOutputs['VALUELIST'])){
            // 二维数组，第二维迭代
            $valueListDataName = $bindOutputs['VALUELIST']['name'];
            $iterate = 'itemOf'.$valueListDataName;
            $itemName = $valueListDataName.'2';
        }else{
            // 一维数组迭代
            $iterate = $outDataName['VALUELIST'];
            $itemName = $bindOutputs['VALUELIST']['name'];
        }

        // 只处理object数组，value看作是要加载的子页url，key看作标题
        list('name'=>$xTitle, 'value'=>$xValue, 'data'=>$boundData) = $this->get_bind_name_value($bindOutputs['VALUELIST'], $itemName);

        echo $this->indent(1).'<div class="accordion-item" v-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '.$iterate.'" :key="idxOf'.$itemName.'">'.PHP_EOL;
        echo $this->indent(2).'<div class="accordion-header" :id="`'.$this->myid(true).'heading${idxOf'.$itemName.'}`">'.PHP_EOL;
        echo $this->indent(3).'<button class="accordion-button" type="button" data-bs-toggle="collapse"';
        echo $this->wrap_output('@click', "click(\$event, idxOf{$itemName}, itemOf{$itemName})");
        echo $this->wrap_output('@dblclick', "dblclick(\$event, idxOf{$itemName}, itemOf{$itemName})");
        echo $this->wrap_output('@mousedown', "mousedown(\$event, idxOf{$itemName}, itemOf{$itemName})");
        echo $this->wrap_output('@mouseup', "mouseup(\$event, idxOf{$itemName}, itemOf{$itemName})");
        echo $this->wrap_output('@mouseover', "mouseover(\$event, idxOf{$itemName}, itemOf{$itemName})");
        echo $this->wrap_output('@mouseout', "mouseout(\$event, idxOf{$itemName}, itemOf{$itemName})");
        echo $this->wrap_output('@mousemove', "mousemove(\$event, idxOf{$itemName}, itemOf{$itemName})");
        echo $this->wrap_output('@mouseenter', "mouseenter(\$event, idxOf{$itemName}, itemOf{$itemName})");
        echo $this->wrap_output('@mouseleave', "mouseleave(\$event, idxOf{$itemName}, itemOf{$itemName})");
        echo ' :data-bs-target="`#'.$this->myid(true).'collapse${idxOf'.$itemName.'}`" aria-expanded="true" aria-controls="`'.$this->myid(true).'collapse${idxOf'.$itemName.'}`">';
        echo "{{{$xTitle}}}</button>".PHP_EOL;
        echo $this->indent(2)."</div>".PHP_EOL;

        echo $this->indent(2).'<div :id="`'.$this->myid(true).'collapse${idxOf'.$itemName.'}`" :class="{\'accordion-collapse collapse\': true, \'show\': '.($this->data['meta']['custom']['activeItem']?:0).' == idxOf'.$itemName.'}"';
        echo ':aria-labelledby="`'.$this->myid(true).'heading${idxOf'.$itemName.'}`" data-parent="#'.$this->myid(true).'">'.PHP_EOL;
        echo $this->indent(3).'<div class="accordion-body p-0">'.PHP_EOL;

        echo $this->indent(3).'<SubpageComponent :url="'.$xValue.'"></SubpageComponent>'.PHP_EOL;

        echo $this->indent(3);
        echo "</div>".PHP_EOL;
        echo $this->indent(2);
        echo "</div>".PHP_EOL;

        echo $this->indent(1)."</div>".PHP_EOL;
    }
}
