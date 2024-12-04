<?php
namespace app\modules\build\views\code\web\bootstrap_vue;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\web\Vue;
use app\modules\build\views\preview\bootstrap\Richtext_View as Preview_Richtext_View;


class Richtext_View extends Preview_Richtext_View {
    use Vue{
        Vue::build_code as vueBuildCode;
        Vue::output_as_prop as vueOutputAsProp;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $outputData = $this->get_output_datas($dataNames);
        $htmlData = $this->get_output_data_name('VALUE', $outputData['VALUE'], $dataNames['VALUE']);
        echo "{$space}<RichTextComponent";
        $this->output_component_props();
        echo ">";
        echo PHP_EOL;
        if ($htmlData){
            echo "{$space}<div v-html='{$htmlData}'></div>";
        }else{
            echo $space.$this->default_value();
        }
        echo PHP_EOL;
        echo $space;
        echo "</RichTextComponent>".PHP_EOL;
    }

    public function build_code(): Base_Code_Fragment{
        $fragment = $this->vueBuildCode();
        $fragment->add_import('@/components/RichTextComponent.vue', [], 'RichTextComponent');
        return $fragment;
    }
    protected function output_as_prop($outputAs, $outputData)
    {
        if (strtolower($outputAs) == 'value'){
            return null;
        }
        return $this->vueOutputAsProp($outputAs, $outputData);
    }
    protected function default_value() {
        $outputData = $this->get_output_datas($dataNames);
        if ($outputData['VALUE']){
            return '';
        }
        return @$this->data['meta']['value'] ?: @$this->data['meta']['title'];
    }
}
