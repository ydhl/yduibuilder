<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Card_View as Preview_Card_View;
use app\modules\build\views\code\web\Vue;

class Card_View extends Preview_Card_View {
    use Vue{
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $myItems = ['head'=>[], 'inBody'=>[], 'outBody'=>[], 'foot'=>[]];
        foreach ((array)@$this->childViews as $view){
            if (@$view->data['placeInParent'] == 'head'){
                $myItems['head'][] = $view;
            }else if (@$view->data['placeInParent'] == 'foot') {
                $myItems['foot'][] = $view;
            }else{
                if (strtolower($view->data['type']) == 'list' || strtolower($view->data['type']) == 'table'){
                    $myItems['outBody'][] = $view;
                }else{
                    $myItems['inBody'][] = $view;
                }
            }
        }

        $space =  $this->indent();
        echo $space."<CardComponent";
        $this->output_component_props();
        echo '>'.PHP_EOL;

        if (!@$this->data['meta']['custom']['headless']){
            echo $this->indent(1) . "<template #header>".PHP_EOL;
            foreach ($myItems['head'] as $view){
                $view->increase_indent(1);
                $view->output();
            }
            echo $this->indent(1) . "</template>".PHP_EOL;
        }

        if ($myItems['inBody']){
            echo $this->indent(1) . "<template #inbody>".PHP_EOL;
            foreach ($myItems['inBody'] as $view){
                $view->increase_indent(1);
                $view->output();
            }
            echo $this->indent(1) . "</template>".PHP_EOL;
        }
        if ($myItems['outBody']){
            echo $this->indent(1) . "<template #outbody>".PHP_EOL;
            foreach ($myItems['outBody'] as $view){
                $view->increase_indent(1);
                $view->output();
            }
            echo $this->indent(1) . "</template>".PHP_EOL;
        }

        if (!@$this->data['meta']['custom']['footless']){
            echo $this->indent(1) . "<template #footer>".PHP_EOL;
            foreach ($myItems['foot'] as $view){
                $view->increase_indent(1);
                $view->output();
            }
            echo $this->indent(1) . "</template>".PHP_EOL;
        }

        echo $space."</CardComponent>".PHP_EOL;
    }
    public function build_code(): Base_Code_Fragment
    {
        $fragment = $this->vueBuildCode();
        $fragment->add_import('@/components/CardComponent.vue', [], 'CardComponent');
        return $fragment;
    }
}
