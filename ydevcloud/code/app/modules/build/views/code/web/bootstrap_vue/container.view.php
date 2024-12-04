<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Container_View as Preview_Container_View;
use app\modules\build\views\code\web\Vue;

class Container_View extends Preview_Container_View {
    use Vue {
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $subset = $this->get_subset();

        $outputDatas = $this->get_output_datas($dataNames);
        if (!$dataNames['VALUE'] || !$this->subset){
            echo "{$space}<ContainerComponent";
            $this->output_component_props();
            echo ">".PHP_EOL;
            if ($outputDatas['TEXT']) {
                $htmlDataName = $this->get_output_data_name('TEXT', $outputDatas['TEXT'], $dataNames['TEXT']);
                echo "{$space}{{{$htmlDataName}}}".PHP_EOL;
            }
            $this->container_body();
            echo "{$space}</ContainerComponent>".PHP_EOL;
        }else{
            $subsetActive = @$this->data['meta']['custom']['subsetActive'];
            $outputDataName = $this->get_output_data_name('VALUE', $outputDatas['VALUE'], $dataNames['VALUE']);

            echo "{$space}<ContainerComponent";
            $this->output_component_props();
            echo ">".PHP_EOL;
            foreach ($subset as $subsetName => $views){
                echo $space.'<template v-if="'.$outputDataName.'==\''.$subsetName.'\'">'.PHP_EOL;
                // 对于当前处于激活的subset，meta items中的可能是最新的
                if ($subsetActive == $subsetName){
                    $this->container_body();
                }else{
                    foreach ($views as $view){
                        $view->output();
                    }
                }
                echo $space.'</template>'.PHP_EOL;
            }
            echo "{$space}</ContainerComponent>".PHP_EOL;
        }
    }
    public function build_code(): Base_Code_Fragment{
        $fragment = $this->vueBuildCode();
        $fragment->add_import('@/components/ContainerComponent.vue', [], 'ContainerComponent');
        return $fragment;
    }
}
