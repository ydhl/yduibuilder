<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Radio_View as Preview_Radio_View;
use app\modules\build\views\code\web\Vue;
use app\modules\build\views\preview\Preview_View;

class Radio_View extends Preview_Radio_View {
   use Vue {
       Vue::build_code as vueBuildCode;
   }

    protected $component = 'RadioComponent';
    public function build_ui()
    {
        $space =  $this->indent();

        echo "{$space}<{$this->component}";
        $this->output_component_props();
        echo $this->wrap_output(":formAttrs", $this->get_form_attrs());
        echo $this->wrap_output(":items", $this->items());
        echo PHP_EOL."{$space}";

        $this->output_v_model();
        echo "></{$this->component}>".PHP_EOL;
    }
    public function build_code():Base_Code_Fragment
    {
        $this->vueBuildCode();
        $fragment = $this->get_code_fragment();
        $fragment->add_import('@/components/RadioComponent.vue', [], 'RadioComponent');
        return $fragment;
    }

    protected function items(){
        $bindOutputs = $this->get_output_datas($outDataName);
        if (!$bindOutputs){
            return $this->formatVue3JSON($this->data['meta']['values'] ?: $this->demo_values());
        }
        if ($this->need_iterate_ui('VALUELIST', $bindOutputs['VALUELIST'])){
            // 二维数组，第二维迭代
            $valueListDataName = $bindOutputs['VALUELIST']['name'];
            return 'itemOf'.$valueListDataName;
        }else if($bindOutputs['VALUELIST']){
            // 一维数组迭代
            return $outDataName['VALUELIST'];
        }else{
            return $this->formatVue3JSON($this->data['meta']['values'] ?: $this->demo_values());
        }
    }
    protected function get_form_attrs(){
        $attrs = ['{'];
        $attrs[] = "'data-uiid':'" . $this->myId().$this->data['type'] . "',";
        if (@$this->data['meta']['form']['state']=='disabled'){
            $attrs[] = 'disabled: true,';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $attrs[] = 'readonly: true,';
        }
        if (@$this->data['meta']['form']['required']){
            $attrs[] = 'required: true,';
        }
        if (@$this->data['meta']['form']['placeholder']) {
            $attrs[] = "'placeholder':" . addslashes($this->data['meta']['form']['placeholder']).',';
        }
        $attrs[] = "'data-root':'".$this->myid()."'";

        $attrs[] = '}';

        return join('', $attrs);
    }
}
