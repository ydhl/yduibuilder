<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Modal_View as Preview_Modal_View;
use app\modules\build\views\code\web\Vue;

class Modal_View extends Preview_Modal_View {
    use Vue{
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $myItems = ['head'=>[], 'body'=>[], 'foot'=>[]];
        foreach ((array)@$this->childViews as $view){
            if (@$view->data['placeInParent'] == 'head'){
                $myItems['head'][] = $view;
            }else if (@$view->data['placeInParent'] == 'foot') {
                $myItems['foot'][] = $view;
            }else{
                $myItems['body'][] = $view;
            }
        }

        $pageUiConfig = $this->build->get_page()->get_ui_config();
        $pageid = $pageUiConfig->meta->id;

        $space =  $this->indent();
        echo "{$space}<ModalComponent";
        $this->output_component_props();
        echo $this->wrap_output('id', $pageid);
        echo $this->wrap_output('contentCss', $this->body_class());
        echo $this->wrap_output('contentStyle', $this->body_style());
        echo ">".PHP_EOL;

        if (!@$this->data['meta']['custom']['headless']){
            echo $this->indent(1);
            echo "<template #header>".PHP_EOL;
            foreach ($myItems['head'] as $view){
                $view->increase_indent(1);
                $view->output();
            }
            echo $this->indent(1)."</template>".PHP_EOL;
        }

        echo $this->indent(1);
        echo "<template #body>".PHP_EOL;
        foreach ($myItems['body'] as $view){
            $view->increase_indent(1);
            $view->output();
        }
        echo $this->indent(1);
        echo "</template>".PHP_EOL;


        if (!@$this->data['meta']['custom']['footless']){
            echo $this->indent(1);
            echo "<template #footer>".PHP_EOL;
            foreach ($myItems['foot'] as $view){
                $view->increase_indent(1);
                $view->output();
            }
            echo $this->indent(1);
            echo "</template>".PHP_EOL;
        }

        echo "</ModalComponent>".PHP_EOL;
    }
    public function build_code(): Base_Code_Fragment{
        $fragment = $this->vueBuildCode();
        $fragment->add_import('@/components/ModalComponent.vue', [], 'ModalComponent');
        return $fragment;
    }
}
