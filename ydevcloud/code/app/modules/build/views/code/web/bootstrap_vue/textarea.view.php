<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\web\Vue;

class Textarea_View extends Input_View {
    use Vue {
        Vue::build_code as vueBuildCode;
        Vue::get_base_data_attrs as vueBaseAttrs;
    }
    public function build_code():Base_Code_Fragment
    {
        $this->vueBuildCode();
        $fragment = $this->get_code_fragment();
        $fragment->add_import('@/components/TextareaComponent.vue', [], 'TextareaComponent');
        return $fragment;
    }
    public function build_ui()
    {
        $space =  $this->indent();
        $outputDatas = $this->get_output_datas($outputDataName);
        $iteratorDataName = $this->get_iterator_data_name();

        echo "{$space}<TextareaComponent";
        $this->output_component_props();
        echo $this->wrap_output("color", $this->data['meta']['style']['color']);
        echo $this->wrap_output("foregroundCss", $this->data['meta']['css']['foregroundTheme']);
        echo $this->wrap_output(":wordCountVisible", $this->data['meta']['custom']['wordCountVisible'] ? 'true' : 'false');
        echo $this->wrap_output(":clearButtonVisible", $this->data['meta']['custom']['clearButtonVisible'] ? 'true' : 'false');
        echo $this->wrap_output(":maxLength", $this->data['meta']['custom']['maxLength']);
        echo PHP_EOL."{$space}";

        if ($outputDataName['VALUE']){
            echo $this->wrap_output(':defaultValue', $iteratorDataName ?: $outputDataName['VALUE']);
        }

        $this->output_v_model();
        echo "></TextareaComponent>".PHP_EOL;
    }

    protected function get_base_data_attrs(){
        return parent::get_base_data_attrs();// 调用input->get_base_data_attrs的方法，避免调用vue->get_base_data_attrs的方法
    }
}
