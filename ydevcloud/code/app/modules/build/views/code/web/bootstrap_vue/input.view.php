<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Input_View as Preview_Input_View;
use app\modules\build\views\code\web\Vue;
use app\modules\build\views\preview\Preview_View;

class Input_View extends Preview_Input_View {
    use Vue {
        Vue::build_code as vueBuildCode;
        Vue::get_base_data_attrs as vueBaseAttrs;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $outputDatas = $this->get_output_datas($outputDataName);
        $iteratorDataName = $this->get_iterator_data_name();

        echo "{$space}<InputComponent";
        $this->output_component_props();
        echo $this->wrap_output("icon", $this->data['meta']['custom']['icon']);
        echo $this->wrap_output("iconPosition", $this->data['meta']['custom']['iconPosition']);
        echo $this->wrap_output("color", $this->data['meta']['style']['color']);
        echo $this->wrap_output("foregroundCss", $this->data['meta']['css']['foregroundTheme']);
        echo $this->wrap_output(":wordCountVisible", $this->data['meta']['custom']['wordCountVisible'] ? 'true' : 'false');
        echo $this->wrap_output(":clearButtonVisible", $this->data['meta']['custom']['clearButtonVisible'] ? 'true' : 'false');
        echo $this->wrap_output(":maxLength", $this->data['meta']['custom']['maxLength']);
        echo PHP_EOL."{$space}";

        if ($outputDataName['VALUE']){
            echo $this->wrap_output(':defaultValue', $iteratorDataName ?: $outputDataName['VALUE']);
        }

        $this->output_v_model();
        echo "></InputComponent>".PHP_EOL;
    }
    public function build_code(): Base_Code_Fragment
    {
        $this->vueBuildCode();
        $fragment = $this->get_code_fragment();
        $fragment->add_import('@/components/InputComponent.vue', [], 'InputComponent');
        return $fragment;
    }

    protected function get_event_action_codes(){
        return Preview_View::get_event_action_codes();
    }
    protected function get_base_data_attrs(){
        $attrs = $this->vueBaseAttrs();

        if(!is_a($this, Textarea_View::class)) $attrs[] = "type:'".(@$this->data['meta']['custom']['inputType'] ?: 'text')."'";
        if (@$this->data['meta']['custom']['autocomplete']) $attrs[] = "autocomplete:'".(@$this->data['meta']['custom']['autocomplete'])."'";
        if (@$this->data['meta']['form']['state']=='disabled') $attrs[] = 'disabled:true';
        if (@$this->data['meta']['form']['state']=='readonly') $attrs[] = 'readonly:true';
        if (@$this->data['meta']['form']['required']) $attrs[] = 'required:true';
        if (@$this->data['meta']['form']['placeholder']) $attrs[] = "placeholder:'".$this->data['meta']['form']['placeholder']."'";
        if (@$this->data['meta']['custom']['maxLength']) $attrs[] = 'maxlength:'.$this->data['meta']['custom']['maxLength'];
        if (@$this->data['meta']['custom']['rows']) $attrs[] = 'rows:'.$this->data['meta']['custom']['rows'];

        return $attrs;
    }
}
