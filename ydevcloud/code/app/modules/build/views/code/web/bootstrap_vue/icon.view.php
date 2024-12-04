<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Icon_View as Preview_Icon_View;
use app\modules\build\views\code\web\Vue;

class Icon_View extends Preview_Icon_View {
    use Vue{
        Vue::output_as_prop as vue_output_as_prop;
        Vue::build_code as vueBuildCode;
    }
    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<IconComponent";
        $this->output_component_props();
        echo "></IconComponent>".PHP_EOL;
    }

    public function build_code(): Base_Code_Fragment
    {
        $fragment = $this->vueBuildCode();
        $fragment->add_import('@/components/IconComponent.vue', [], 'IconComponent');
        return $fragment;
    }

    protected function output_as_prop($outputAs, $outputData){
        if (!strcasecmp($outputAs,'value')){
            return ':class';
        }
        return $this->vue_output_as_prop($outputAs, $outputData);
    }
}
