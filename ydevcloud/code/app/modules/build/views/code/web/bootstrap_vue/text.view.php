<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Text_View as Preview_Text_View;
use app\modules\build\views\code\web\Vue;

class Text_View extends Preview_Text_View {
    use Vue{
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $type = strtolower(@$this->data['meta']['custom']['type'] ?: 'span');
        echo "{$space}<TextComponent";
        echo $this->wrap_output('type', $type);
        $this->output_component_props();
        echo ">".$this->body_text()."</TextComponent>".PHP_EOL;
    }

    public function build_code(): Base_Code_Fragment{
        $fragment = $this->vueBuildCode();
        $fragment->add_import('@/components/TextComponent.vue', [], 'TextComponent');
        return $fragment;
    }
}
