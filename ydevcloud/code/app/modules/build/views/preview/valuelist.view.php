<?php
namespace app\modules\build\views\preview;

use app\modules\build\views\code\Base_Code_Fragment;
use function yangzie\__;

/**
 * 迭代类ui公共逻辑封装
 */
abstract class ValueList_View extends Preview_View implements Valuable_View{

    public function build_ui()
    {
        $bindOutputs = $this->get_output_datas($outDataName);
        if (!$bindOutputs){
            $this->build_valuelist_static();
            return;
        }
        $valueListData = $bindOutputs['VALUELIST'];
        if ($this->need_iterate_ui('VALUELIST', $bindOutputs['VALUELIST'])){
            // 二维数组，第二维迭代
            $valueListDataName = $bindOutputs['VALUELIST']['name'];
            $this->build_valuelist_iterator($valueListData, $outDataName['VALUELIST'],'itemOf'.$valueListDataName,$valueListDataName.'2', true, 'idxOf'.$valueListDataName);
        }else if($bindOutputs['VALUELIST']){
            // 一维数组迭代
            $valueListDataName = $outDataName['VALUELIST'];
            $this->build_valuelist_iterator($valueListData, $valueListDataName, $valueListDataName, $valueListData['name']);
        }else{
            $this->build_valuelist_static();
        }
    }


    protected function demo_values() {
        return [[ "name"=> 'Item 1', "value"=> 'item 1' ], [ "name"=> 'Item 2', "value"=> 'item 2' ]];
    }

    protected function default_value() {
        $uiType = strtolower($this->data['type']);
        $isArr = $uiType == 'checkbox' || ($uiType=='select' && $this->data['meta']['custom']['multiple']) || $this->need_iterate_data();
        if (@!$this->data['meta']['values']){
            return $isArr ? ['item 1'] : 'item 1';
        }
        $arr = [];
        foreach($this->data['meta']['values'] as $value){
            if($value['checked']) $arr[] = $value['value']?:$value['name'];
        }
        return $isArr ? $arr : $arr[0];// 多值返回数组，单值返回标量
    }
    protected abstract function build_ui_begin($iteratorName=null);
    protected abstract function build_ui_end();

    /**
     * 输出值列表项目
     * @param $outputData array 值列表项目上绑定的数据
     * @param $itemName string 迭代值列表项目是的数据名称
     * @param $staticData array 静态数据
     * @param $staticDataIndex int 静态数据索引
     * @return mixed
     */
    protected abstract function build_valuelist($outputData, $itemName, $staticData=null, $staticDataIndex=null, $iteratorName='');
    protected function build_valuelist_static(){
        $this->build_ui_begin();
        $values = $this->data['meta']['values'] ?: $this->demo_values();
        foreach ($values as $index => $item){
            $this->build_valuelist(null, null, $item, $index);
        }
        $this->build_ui_end();
    }

    /**
     * 迭代输出值列表
     *
     * 举例：
     * <pre>
     * <ol>
     *  <template x-for="(itemOf[$itemName], idxOf[$itemName]) in [$iteratorName]" :key="idxOf[$itemName]">
     *      值列表html元素
     *  </template>
     * </ol>
     * </pre>
     *
     *
     * @param $outputData array 绑定的数据
     * @param $outDataName string 是绑定输出数据的从根开始的访问名称
     * @param $iteratorName string 迭代数据名，如果是一维数组同outDataName，如果是二维数组，则是itemOf$outDataName
     * @param $itemName string 数据项目名，如果是一维数组，则是绑定数据的名称，如果是二维数组，则是数据名加个2后缀
     * @param $is2D boolean true表示该值列表是二维，那么在取outputData中的数据时可根据该参数区分，比如调用2维的情况下，get_bind_name_value中的第一个
     *                      参数要取$outputData['item']:
     *                      $this->get_bind_name_value($is2d ? $outputData['item'] : $outputData, $itemName);
     *
     * @param $firstIndex string 2维数组迭代UI时，该参数是第一位数组迭代的索引
     * @return mixed
     */
    protected function build_valuelist_iterator($outputData, $outDataName, $iteratorName, $itemName, $is2D=false, $firstIndex=''){
        $this->build_ui_begin($iteratorName);

        echo $this->indent(1).'<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '
            .$iteratorName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;
        $this->build_valuelist($is2D ? $outputData['item'] : $outputData, $itemName);
        echo $this->indent(1).'</template>'.PHP_EOL;

        $this->build_ui_end();
    }
}
