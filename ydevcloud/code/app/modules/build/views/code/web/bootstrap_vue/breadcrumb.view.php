<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Breadcrumb_View as Preview_Breadcrumb_View;
use app\modules\build\views\code\web\Vue;

class Breadcrumb_View extends Preview_Breadcrumb_View {
    use Vue {
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();

        echo "{$space}<BreadcrumbComponent";
        $this->output_component_props();
        echo $this->wrap_output(":items", $this->menus());
        echo $this->wrap_output('foregroundClass', $this->foregroundCss());
        echo $this->wrap_output('foregroundStyle', $this->foregroundStyle());
        echo PHP_EOL."{$space}";

        $this->output_v_model();
        echo "></BreadcrumbComponent>".PHP_EOL;
    }

    public function build_code(): Base_Code_Fragment
    {
        $this->vueBuildCode();
        $fragment = $this->get_code_fragment();
        $fragment->add_import('@/components/BreadcrumbComponent.vue', [], 'BreadcrumbComponent');
        return $fragment;
    }

    private function menus(){
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
}
