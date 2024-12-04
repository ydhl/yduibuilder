<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Holder_View as Preview_Holder_View;
use app\modules\build\views\code\web\Vue;

class Holder_View extends Preview_Holder_View {
    use Vue{
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<HolderComponent";
        $this->output_component_props();
        echo "></HolderComponent>".PHP_EOL;
    }
    public function build_code(): Base_Code_Fragment{
        $fragment = $this->vueBuildCode();
        $fragment->add_import('@/components/HolderComponent.vue', [], 'HolderComponent');
        return $fragment;
    }
}
