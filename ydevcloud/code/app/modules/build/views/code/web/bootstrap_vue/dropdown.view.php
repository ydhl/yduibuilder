<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Dropdown_View as Preview_Dropdown_View;
use app\modules\build\views\code\web\Vue;

class Dropdown_View extends Preview_Dropdown_View{
    use Vue {
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<DropdownComponent";
        $this->output_component_props();
        echo $this->wrap_output(":items", $this->menus());
        echo $this->wrap_output(':isSplit', $this->data['meta']['custom']['isSplit']?"true":"false");
        echo $this->wrap_output('btnCss', $this->btnCss());
        echo $this->wrap_output('btnStyle', $this->btnStyle());
        echo $this->wrap_output('splitBtnCss', $this->splitBtnCss());
        echo $this->wrap_output(':syncTitle', $this->data['meta']['custom']['syncTitle']?"true":"false");
        echo $this->wrap_output('dropdownMenuCss', 'dropdown-menu'. (@$this->data['meta']['custom']['menuAlign']=='right' ? ' dropdown-menu-right': ''));
        echo $this->wrap_output('btnTitle', $this->data['meta']['title'] ?: $this->data['type']);
        echo $this->wrap_output('icon', $this->data['meta']['custom']['icon']);
        echo $this->wrap_output('iconPosition', $this->data['meta']['custom']['iconPosition']);
        echo PHP_EOL."{$space}";
        $this->output_v_model();
        echo "></DropdownComponent>".PHP_EOL;
    }
    public function build_code():Base_Code_Fragment
    {
        $this->vueBuildCode();
        $fragment = $this->get_code_fragment();
        $fragment->add_import('@/components/DropdownComponent.vue', [], 'DropdownComponent');
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
