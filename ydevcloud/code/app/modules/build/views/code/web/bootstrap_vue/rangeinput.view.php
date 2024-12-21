<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Rangeinput_View as Preview_Rangeinput_View;
use app\modules\build\views\code\web\Vue;

class Rangeinput_View extends Preview_Rangeinput_View {
    use Vue {
        Vue::build_code as vueBuildCode;
        Vue::get_base_data_attrs as vueBaseAttrs;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $outputDatas = $this->get_output_datas($outputDataName);

        $iteratorDataName = $this->get_iterator_data_name();
        echo "{$space}<RangeInputComponent";
        $this->output_component_props();
        echo $this->wrap_output(":min", $this->data['meta']['custom']['min']);
        echo $this->wrap_output(":max", $this->data['meta']['custom']['max']);
        echo $this->wrap_output(":step", $this->data['meta']['custom']['step']);
        echo PHP_EOL."{$space}";
        if ($outputDataName['VALUE']){
            echo $this->wrap_output(':defaultValue', $iteratorDataName ?: $outputDataName['VALUE']);
        }

        $this->output_v_model();
        echo "></RangeInputComponent>".PHP_EOL;
    }
    public function build_code(): Base_Code_Fragment
    {
        $this->vueBuildCode();
        $fragment = $this->get_code_fragment();
        $fragment->add_import('@/components/RangeInputComponent.vue', [], 'RangeInputComponent');
        return $fragment;
    }

    protected function get_base_data_attrs(){
        $attrs = $this->vueBaseAttrs();

        if (@$this->data['meta']['form']['state']=='disabled') $attrs[] = 'disabled:true';
        if (@$this->data['meta']['form']['state']=='readonly') $attrs[] = 'readonly:true';
        if (@$this->data['meta']['form']['required']) $attrs[] = 'required:true';

        return $attrs;
    }
}
