<?php
namespace app\modules\build\views\preview;

use function yangzie\__;

/**
 * 迭代类ui公共逻辑封装
 */
abstract class ValueList_View extends Preview_View {

    // 重载，数组不循环输出自己，循环输出自己的子项
    protected function build_data_output_bind(){
        $outputDatas = $this->get_output_datas($outputDataName);
        if (!$outputDatas) return;
        foreach ($outputDatas as $outputAs => $outputData){
            $outputAs = strtoupper($outputAs);
            // value为输出内部元素，在build_ui_array, build_ui_2d_array中处理
            if ($outputAs=='VALUELIST') return;

            echo $this->wrap_output($this->output_as_prop($outputAs, $outputData), $this->get_output_data_name($outputAs, $outputData, $outputDataName[$outputAs]));
        }
    }

    public function build_ui()
    {
        $bindOutputs = $this->get_output_datas($outDataName);
        if (!$bindOutputs){
            $this->build_ui_static();
            return;
        }
        // 对于迭代类ui，循环输出ui时只有2d数组，并且绑定的是value
        if ($this->need_iterate_ui('VALUELIST', $bindOutputs['VALUELIST'])){
            $this->build_ui_2d_array($bindOutputs['VALUELIST'], $bindOutputs['VALUELIST']['name']);
        }else if($bindOutputs['VALUELIST']){// 循环输出内部列表
            $this->build_ui_array($bindOutputs['VALUELIST'], $outDataName['VALUELIST']);
        }else{
            $this->build_ui_static();
        }
    }

    /**
     * 返回在遍历时用到的name，value
     * - 对象数组，如果对象有name属性用之，没有JSON.stringify(数组项)
     * - 对象数组，如果对象有value属性用之，没有返回数组索引
     * - 对象，name和value都采用key:value都格式
     *
     * @param $bindOutput
     * @param $itemName
     * @return string[] [name, value, item]
     */
    protected function get_bind_name_value($bindOutput, $itemName)
    {
        $name = "itemOf{$itemName}";
        $value = "itemOf{$itemName}";

        if ($bindOutput['type']=='array'){
            if ($this->has_props($bindOutput['item'],'name')){//对象数组
                $name = "itemOf{$itemName}.name";
            }else if ($this->is_object($bindOutput['item'])){
                $name = "JSON.stringify(itemOf{$itemName})";
            }

            if ($this->has_props($bindOutput['item'],'value')){
                $value = "itemOf{$itemName}.value";
            }else if ($this->is_object($bindOutput['item'])){
                $value = "idxOf{$itemName}";
            }
        }else if ($this->is_object($bindOutput)){
            $name = "idxOf{$itemName}";
            $value = "itemOf{$itemName}";
        }
        return ['name'=>$name, 'value'=>$value];
    }
    protected abstract function build_ui_static();
    protected abstract function build_ui_2d_array($bindOutput, $outDataName);
    protected abstract function build_ui_array($bindOutput, $outDataName);
}
