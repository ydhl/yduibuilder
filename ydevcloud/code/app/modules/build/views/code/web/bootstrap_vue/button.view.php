<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Button_View as Preview_Button_View;
use app\modules\build\views\code\web\Vue;

class Button_View extends Preview_Button_View {
    use Vue{
        Vue::build_code as vueBuildCode;
        Vue::get_base_data_attrs as vueBaseAttrs;
    }
    public function build_ui()
    {
        $space =  $this->indent();
        $outputDatas = $this->get_output_datas($dataName);
        if ($outputDatas) {
            $htmlDataName = $this->get_output_data_name('HTML', $outputDatas['HTML'], $dataName['HTML']);
        }

        $meta = $this->data['meta'];
        $type = $meta['custom']['type'] ?: "button";
        echo $space."<ButtonComponent";
        $this->output_component_props();
        echo $this->wrap_output('buttonType', $type);
        echo '>';

        if (!$htmlDataName) {
            $this->wrap_icon(function () {
                echo $this->body_text();
            }, $this->get_build()->get_indent() + 1);
        }
        echo PHP_EOL;
        echo $space."</ButtonComponent>".PHP_EOL;
    }
    protected function get_base_data_attrs()
    {
        $attrs = $this->vueBaseAttrs();

        if($this->data['meta']['custom']['linkHref']) $attrs[] = 'href:"'.$this->data['meta']['custom']['linkHref'].'"';

        if (@$this->data['meta']['custom']['disabled']){
            $attrs[] = 'disabled:true';
        }
        if($this->data['meta']['title']) $attrs[] = 'title:"'.addslashes($this->data['meta']['title']).'"';
        return $attrs;
    }

    public function build_code(): Base_Code_Fragment
    {
        $fragment = $this->vueBuildCode();
        $fragment->add_import('@/components/ButtonComponent.vue', [], 'ButtonComponent');
        return $fragment;
    }
}
