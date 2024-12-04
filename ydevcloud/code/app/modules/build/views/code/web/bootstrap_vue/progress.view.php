<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Progress_View as Preview_Progress_View;
use app\modules\build\views\code\web\Vue;

class Progress_View extends Preview_Progress_View {
    use Vue {
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $outputDatas = $this->get_output_datas($outputDataNames);

        $space =  $this->indent();
        $value = $this->data['meta']['value']?:50;

        echo "{$space}<ProgressComponent";
        echo $this->wrap_output("barCss", join(' ',$this->bar_css()));

        if ($outputDatas['VALUE']){
            $valueDataName = $this->get_output_data_name('VALUE', $outputDatas['VALUE'], $outputDataNames['VALUE']);
            echo $this->wrap_output(':progress', $valueDataName);
        }else{
            echo $this->wrap_output(':progress', $value);
        }
        echo $this->wrap_output(':min', @$this->data['meta']['custom']['min'] ?? 0);
        echo $this->wrap_output(':max', @$this->data['meta']['custom']['max'] ?? 100);
        $this->output_component_props();
        echo ">";

        if (@$this->data['meta']['custom']['label']) {
            $textDataName = $this->get_output_data_name('TEXT', $outputDatas['TEXT'], $outputDataNames['TEXT']);
            if ($textDataName && $valueDataName){
                echo "{{{$textDataName}}}:{{{$valueDataName}}}%";
            }else if ($textDataName){
                echo "{{{$textDataName}}}";
            }else if ($valueDataName){
                echo "{{{$valueDataName}}}%";
            }else{
                echo "{$value}%";
            }
        }
        echo "</ProgressComponent>".PHP_EOL;
    }
    public function build_code(): Base_Code_Fragment
    {
        $fragment = $this->vueBuildCode();
        $fragment->add_import('@/components/ProgressComponent.vue', [], 'ProgressComponent');

        return $fragment;
    }
}
