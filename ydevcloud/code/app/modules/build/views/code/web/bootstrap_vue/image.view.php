<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Image_View as Preview_Image_View;
use app\modules\build\views\code\web\Vue;

class Image_View extends Preview_Image_View {
    use Vue{
        Vue::output_as_prop as vue_output_as_prop;
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $outputData = $this->get_output_datas($outputDataNames);

        echo "{$space}<ImageComponent";
        if ($outputDataNames['VALUE']){
            $valueName = $this->get_output_data_name('VALUE', $outputData['VALUE'], $outputDataNames['VALUE']);
            echo $this->wrap_output(':src', $valueName);
        }else{
            echo $this->wrap_output(":src", $this->myid().'Src');
        }
        $this->output_component_props();
        echo "></ImageComponent>".PHP_EOL;
    }

    public function build_code(): Base_Code_Fragment
    {
        $fragment = $this->vueBuildCode();
        $fragment->add_import('@/components/ImageComponent.vue', [], 'ImageComponent');

        $imgSrc = @$this->data['meta']['value']?:'/uibuilder.jpg';
        $imgSrc = $this->get_Img_Src($imgSrc);
        $this->get_output_datas($outputDataNames);

        if (!$outputDataNames['VALUE']){
            $fragment->add_import($imgSrc, [], $this->myid().'Src');
        }
        return $fragment;
    }
    protected function output_as_prop($outputAs,$outputData){
        if (!strcasecmp($outputAs,'value')){
            return null;
        }
        return $this->vue_output_as_prop($outputAs, $outputData);
    }
}
