<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Container_View extends Preview_View {
    use Bootstrap_Popup,Html_Code_Helper, Alpine {
        Alpine::build_code as alpineBuildCode;
    }
    protected $subset = [];
    protected function output_as_prop($outputAs, $outputData)
    {
        if (strtolower($outputAs) == 'value') return null;
        return parent::output_as_prop($outputAs, $outputData);
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $subset = $this->get_subset();

        $outputDatas = $this->get_output_datas($dataNames);
        if (!$dataNames['VALUE'] || !$this->subset){
            echo "{$space}<div";
            $this->output_main_attrs();
            echo ">".PHP_EOL;
            $this->container_body();
            echo "{$space}</div>".PHP_EOL;
        }else{
            $subsetActive = @$this->data['meta']['custom']['subsetActive'];
            $outputDataName = $this->get_output_data_name('VALUE', $outputDatas['VALUE'], $dataNames['VALUE']);
            foreach ($subset as $subsetName => $views){
                echo $space.'<template x-if="'.$outputDataName.'==\''.$subsetName.'\'">'.PHP_EOL;
                echo "{$space}<div";
                $this->output_main_attrs();
                echo ">".PHP_EOL;
                // 对于当前处于激活的subset，meta items中的可能是最新的
                if ($subsetActive == $subsetName){
                    $this->container_body();
                }else{
                    foreach ($views as $view){
                        $view->output();
                    }
                }
                echo "{$space}</div>".PHP_EOL;
                echo $space.'</template>'.PHP_EOL;
            }
        }
    }

    public function build_code(): Base_Code_Fragment
    {
        $fragment = $this->alpineBuildCode();
        $subset = $this->get_subset();
        $subsetActive = @$this->data['meta']['custom']['subsetActive'];

        foreach ($subset as $subsetName => $views){
            // 处于激活的 alpineBuildCode 已处理
            if ($subsetActive != $subsetName){
                foreach ($views as $view){
                    $view->build_code();
                    $fragment->merge($view->get_code_fragment());
                }
            }
        }

        return $fragment;
    }

    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        if ($justSelf) return $style;

        $subset = $this->get_subset();
        $subsetActive = @$this->data['meta']['custom']['subsetActive'];

        foreach ($subset as $subsetName => $views){
            // 对于当前处于激活的subset，meta items中的可能是最新的, parent::build_style 已处理
            if ($subsetActive != $subsetName){
                foreach ($views as $view){
                    $this->styles = array_merge($this->styles, $view->build_style(false));
                }
            }
        }

        foreach ($this->styles as $key => $styles){
            $this->styles[$key] = is_array($this->styles[$key]) ? array_unique($this->styles[$key]) : $this->styles[$key];
        }
        return $this->styles;
    }
    protected function container_body(){
        $outputDatas = $this->get_output_datas($dataName);
        if ($outputDatas) {
            $htmlDataName = $this->get_output_data_name('HTML', $outputDatas['HTML'], $dataName['HTML']);
        }
        // 绑定了html属性输出则忽略子组件
        if ($htmlDataName) return;
        foreach ((array)@$this->childViews as $view){
            $view->output();
        }
    }

    protected function get_subset(){
        foreach ((array)@$this->data['meta']['custom']['subset'] as $subsetName => $items){
            foreach ((array)@$items as $index => $item) {
                if (!$item) continue;
                $this->subset[$subsetName][$index] = $this->create_item_view($subsetName.$index, $item);
            }
        }
        return $this->subset;
    }
}
