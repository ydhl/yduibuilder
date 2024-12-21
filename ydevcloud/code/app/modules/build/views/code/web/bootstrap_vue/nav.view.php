<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Nav_View as Preview_Nav_View;
use app\modules\build\views\code\web\Vue;

class Nav_View extends  Preview_Nav_View{
    use Vue {
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();

        echo "{$space}<NavComponent";
        $this->output_component_props();
        echo $this->wrap_output(":items", $this->menus());
        echo $this->wrap_output('itemStyle', $this->itemStyle());
        echo $this->wrap_output('itemCss', $this->itemCss());
        echo $this->wrap_output('checkedItemStyle', $this->checkedItemStyle());
        echo $this->wrap_output('checkedItemCss', $this->checkedItemCss());
        echo PHP_EOL."{$space}";
        $this->output_v_model();
        echo "></NavComponent>".PHP_EOL;
    }
    public function build_code():Base_Code_Fragment
    {
        $this->vueBuildCode();
        $fragment = $this->get_code_fragment();
        $fragment->add_import('@/components/NavComponent.vue', [], 'NavComponent');
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
    private function itemStyle(){
        $color = $this->data['meta']['style']['color'];
        if (!$color){
            return null;
        }

        $uncheckedStyle = "color:{$color} !important;";
        return $uncheckedStyle;
    }
    private function checkedItemStyle(){

        $color = $this->data['meta']['style']['color'];
        if (!$color){
            return null;
        }

        $checkedStyle = "background-color:{$color} !important;color:#fff !important;";
        return $checkedStyle;
    }

    private function checkedItemCss() {

        $css = [];
        $theme = $this->data['meta']['css']['foregroundTheme'];
        if ($theme && $theme !== 'default' && !$this->data['meta']['style']['color']){
            $checkedCss = $this->cssTranslate['backgroundTheme'][$theme].' text-white';
        }

        $css[] = "{$checkedCss} disabled";

        return join(' ', $css)?:null;
    }

    private function itemCss() {
        $css = ["nav-link"];
        $theme = $this->data['meta']['css']['foregroundTheme'];
        if ($theme && $theme !== 'default' && !$this->data['meta']['style']['color']){
            $uncheckedCss = $this->cssTranslate['foregroundTheme'][$theme];
        }

        if ($uncheckedCss) $css[] = $uncheckedCss;

        return join(' ', $css) ?: null;
    }
}
