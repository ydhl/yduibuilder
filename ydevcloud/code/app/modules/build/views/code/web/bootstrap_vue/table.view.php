<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Table_View as Preview_Table_View;
use app\modules\build\views\code\web\Vue;

class Table_View extends Preview_Table_View {
    use Vue {
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<TableComponent";
        $this->output_component_props();
        echo PHP_EOL."{$space}";
        echo $this->wrap_output(":datas", $this->datas());
        echo $this->wrap_output('headerCss', $this->header_css());
        echo $this->wrap_output('footerCss', $this->footer_css());
        echo $this->wrap_output('tableCss', $this->table_class());
        echo $this->wrap_output('tdCss', $this->td_css());
        echo $this->wrap_output(':headless', $this->data['meta']['custom']['headless'] ? 'true' : 'false');
        echo $this->wrap_output(':footless', $this->data['meta']['custom']['footless'] ? 'true' : 'false');
        echo $this->wrap_output(':headerRow', @!$this->data['meta']['custom']['headless'] ? intval($this->data['meta']['custom']['headerRow'])?:1 : 0);
        echo $this->wrap_output(':footerRow', !$this->data['meta']['custom']['footless'] ? intval($this->data['meta']['custom']['footerRow'])?:1 : 1);
        echo ">".PHP_EOL;
        if(!$this->datas()) $this->build_static_table($this->get_table_data());
        echo "{$space}</TableComponent>".PHP_EOL;
    }
    public function build_code():Base_Code_Fragment
    {
        $this->vueBuildCode();
        $fragment = $this->get_code_fragment();
        $fragment->add_import('@/components/TableComponent.vue', [], 'TableComponent');
        return $fragment;
    }

    private function datas(){
        $bindOutputs = $this->get_output_datas($outDataName);
        if (!$bindOutputs){
            return null;
        }
        return $outDataName['VALUELIST'] ?: null;
    }

}
