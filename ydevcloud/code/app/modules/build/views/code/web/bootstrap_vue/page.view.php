<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Page_View as Preview_Page_View;
use app\modules\build\views\code\web\Vue;

class Page_View extends Preview_Page_View {
    use Vue{
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<PageComponent";
        $this->output_component_props();
        echo ">".PHP_EOL;
        foreach ($this->childViews as $view){
            $view->output();
        }

        echo "{$space}</PageComponent>".PHP_EOL;
    }

    public function build_code(): Base_Code_Fragment{
        $fragment = $this->vueBuildCode();
        $fragment->add_import('@/components/PageComponent.vue', [], 'PageComponent');
        return $fragment;
    }
}
