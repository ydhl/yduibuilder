<?php
namespace app\modules\build\views\code\web\bootstrap_vue;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\web\Vue;
use app\modules\build\views\preview\bootstrap\Carousel_View as Preview_Carousel_View;
use function yangzie\__;

/**
 * <pre>
 * <div id="carouselExampleIndicators" class="carousel slide">
 *  <div class="carousel-indicators">
 *      <button data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"></button>
 *      <button data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"></button>
 *      <button data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"></button>
 *  </div>
 *  <div class="carousel-inner">
 *      <div class="carousel-item active">
 *          <img src="..." class="d-block w-100" alt="...">
 *      </div>
 *      <div class="carousel-item">
 *          <img src="..." class="d-block w-100" alt="...">
 *      </div>
 *      <div class="carousel-item">
 *          <img src="..." class="d-block w-100" alt="...">
 *      </div>
 *  </div>
 *  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
 *      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
 *      <span class="sr-only">Previous</span>
 *  </button>
 *  <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
 *      <span class="carousel-control-next-icon" aria-hidden="true"></span>
 *      <span class="sr-only">Next</span>
 *  </button>
 * </div>
 * </pre>
 */
class Carousel_View extends Preview_Carousel_View {
    use Vue{
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<CarouselComponent";
        $this->output_component_props(false);
        echo $this->wrap_output(":showIndicator", $this->data['meta']['custom']['showIndicator'] ? 'true' : null);
        echo $this->wrap_output(':showControl', $this->data['meta']['custom']['showControl'] ? 'true' : null);
        echo $this->wrap_output(':itemCount', $this->itemCount());
        echo $this->wrap_output(':defaultActiveIndex', isset($this->data['meta']['custom']['activeIndex']) ? $this->data['meta']['custom']['activeIndex'] : 0);
        echo ">".PHP_EOL;
        $this->output_items();
        echo $space."</CarouselComponent>".PHP_EOL;
    }
    public function build_code():Base_Code_Fragment
    {
        $this->vueBuildCode();
        $bindOutputs = $this->get_output_datas($outDataName);
        $fragment = $this->get_code_fragment();
        $fragment->add_import('@/components/CarouselComponent.vue', [], 'CarouselComponent');
        if($bindOutputs['VALUELIST']) $fragment->add_import('@/components/SubpageComponent.vue', [], 'SubpageComponent');
        return $fragment;
    }
    private function output_items(){
        $myid = $this->myid();
        $bindOutputs = $this->get_output_datas($outDataName);
        if (!$bindOutputs['VALUELIST']){// 静态
            if ( ! $this->data['items']) {
                echo $this->indent(1).'<template #slide="{ click, dblclick, mousedown, mouseup, mouseover, mouseout, mousemove, mouseenter, mouseleave, blur, focus }">'.PHP_EOL;
                echo $this->indent(2).'<div class="carousel-item active" data-bs-target="'.$myid.'"';
                echo $this->wrap_output('@click', "click(\$event, 0)");
                echo $this->wrap_output('@dblclick',"dblclick(\$event, 0)");
                echo $this->wrap_output('@mousedown',"mousedown(\$event, 0)");
                echo $this->wrap_output('@mouseup',"mouseup(\$event, 0)");
                echo $this->wrap_output('@mouseover',"mouseover(\$event, 0)");
                echo $this->wrap_output('@mouseout',"mouseout(\$event, 0)");
                echo $this->wrap_output('@mousemove',"mousemove(\$event, 0)");
                echo $this->wrap_output('@mouseenter',"mouseenter(\$event, 0)");
                echo $this->wrap_output('@mouseleave',"mouseleave(\$event, 0)");
                echo $this->wrap_output('@blur',"blur(\$event, 0)");
                echo $this->wrap_output('@focus',"focus(\$event, 0)");
                echo '>'.PHP_EOL;
                echo $this->indent(3).'<div class="d-block w-100 d-flex justify-content-center align-items-center"';
                echo $this->wrap_output('style', $this->placeholder_style());
                echo ">".PHP_EOL;
                echo $this->indent(4);
                echo '<div class="display-1 text-center">';
                echo __("Slide");
                echo "</div>".PHP_EOL;
                echo $this->indent(3)."</div>".PHP_EOL;
                echo $this->indent(2)."</div>".PHP_EOL;
                echo $this->indent(1)."</template>".PHP_EOL;
                return;
            }

            echo $this->indent(1).'<template #slide="{activeIndex, click, dblclick, mousedown, mouseup, mouseover, mouseout, mousemove, mouseenter, mouseleave, blur, focus}">'.PHP_EOL;
            foreach ((array)@$this->childViews as $index => $view){
                echo $this->indent(2).'<div :class="{\'carousel-item\': true, \'active\': activeIndex == '.$index.'}"';
                echo $this->wrap_output('@click', "click(\$event, {$index})");
                echo $this->wrap_output('@dblclick',"dblclick(\$event, {$index})");
                echo $this->wrap_output('@mousedown',"mousedown(\$event, {$index})");
                echo $this->wrap_output('@mouseup',"mouseup(\$event, {$index})");
                echo $this->wrap_output('@mouseover',"mouseover(\$event, {$index})");
                echo $this->wrap_output('@mouseout',"mouseout(\$event, {$index})");
                echo $this->wrap_output('@mousemove',"mousemove(\$event, {$index})");
                echo $this->wrap_output('@mouseenter',"mouseenter(\$event, {$index})");
                echo $this->wrap_output('@mouseleave',"mouseleave(\$event, {$index})");
                echo $this->wrap_output('@blur',"blur(\$event, {$index})");
                echo $this->wrap_output('@focus',"focus(\$event, {$index})");
                echo '>'.PHP_EOL;
                $view->increase_indent(2);
                $view->output();
                echo $this->indent(2)."</div>".PHP_EOL;
            }
            echo $this->indent(1)."</template>".PHP_EOL;
            return;
        }

        if ($this->need_iterate_ui('VALUELIST', $bindOutputs['VALUELIST'])){
            // 二维数组，第二维迭代
            $valueListDataName = $bindOutputs['VALUELIST']['name'];
            $iterate = 'itemOf'.$valueListDataName;
        }else{
            // 一维数组迭代
            $iterate = $outDataName['VALUELIST'];
        }
        echo $this->indent(1).'<template #slide="{activeIndex, click, dblclick, mousedown, mouseup, mouseover, mouseout, mousemove, mouseenter, mouseleave, blur, focus}">'.PHP_EOL;
        echo $this->indent(2).'<div :class="{\'carousel-item\': true, \'active\': activeIndex == index}" v-for="(item, index) in '.$iterate.'" :key="index"';
        echo $this->wrap_output('@click', "click(\$event, index, item)");
        echo $this->wrap_output('@dblclick',"dblclick(\$event, index, item)");
        echo $this->wrap_output('@mousedown',"mousedown(\$event, index, item)");
        echo $this->wrap_output('@mouseup',"mouseup(\$event, index, item)");
        echo $this->wrap_output('@mouseover',"mouseover(\$event, index, item)");
        echo $this->wrap_output('@mouseout',"mouseout(\$event, index, item)");
        echo $this->wrap_output('@mousemove',"mousemove(\$event, index, item)");
        echo $this->wrap_output('@mouseenter',"mouseenter(\$event, index, item)");
        echo $this->wrap_output('@mouseleave',"mouseleave(\$event, index, item)");
        echo $this->wrap_output('@blur',"blur(\$event, index, item)");
        echo $this->wrap_output('@focus',"focus(\$event, index, item)");
        echo '>'.PHP_EOL;
        echo $this->indent(3).'<SubpageComponent :url="item"></SubpageComponent>'.PHP_EOL;
        echo $this->indent(2)."</div>".PHP_EOL;
        echo $this->indent(1)."</template>".PHP_EOL;
    }
    private function itemCount(){
        $bindOutputs = $this->get_output_datas($outDataName);
        if (!$bindOutputs['VALUELIST']){
            return count($this->data['items']) ?: 1;
        }
        if ($this->need_iterate_ui('VALUELIST', $bindOutputs['VALUELIST'])){
            // 二维数组，第二维迭代
            $valueListDataName = $bindOutputs['VALUELIST']['name'];
            return $valueListDataName.'.length';
        }else{
            // 一维数组迭代
            return $outDataName['VALUELIST'].'.length';
        }
    }
}
