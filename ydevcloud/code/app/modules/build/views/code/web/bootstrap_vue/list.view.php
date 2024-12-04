<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\List_View as Preview_List_View;
use app\modules\build\views\code\web\Vue;
use app\modules\build\views\preview\Preview_View;

class List_View extends Preview_List_View {
    use Vue {
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<ListComponent";
        $this->output_component_props();
        echo $this->wrap_output(":items", $this->items());
        echo $this->wrap_output('itemStyle', $this->itemStyle());
        echo $this->wrap_output('itemCss', $this->itemCss());
        echo $this->wrap_output('checkedItemStyle', $this->checkedItemStyle());
        echo $this->wrap_output('checkedItemCss', $this->checkedItemCss());
        echo PHP_EOL."{$space}";
        $this->output_v_model();
        echo "></ListComponent>".PHP_EOL;
    }
    public function build_code():Base_Code_Fragment
    {
        $this->vueBuildCode();
        $fragment = $this->get_code_fragment();
        $fragment->add_import('@/components/ListComponent.vue', [], 'ListComponent');
        return $fragment;
    }

    protected function itemCss() {
        $styleMap = Preview_View::style_map();
        $cssMap = Preview_View::css_map();
        $backgroundTheme = $this->data['meta']['css']['backgroundTheme'];

        $css = [];
        if ($backgroundTheme && !$styleMap['background-color'] && $backgroundTheme != 'default'){
            $css[] = "list-group-item-{$backgroundTheme}";
        }
        if (!$styleMap['color'] && @$cssMap['foregroundTheme']) {
            $css[] = $cssMap['foregroundTheme'];
        }
        return join(' ', $css)?:null;
    }
    protected function checkedItemCss() {
        $styleMap = Preview_View::style_map();
        $backgroundTheme = $this->data['meta']['css']['backgroundTheme'];

        $css = [];
        if ($backgroundTheme && !$styleMap['background-color'] && $backgroundTheme != 'default'){
            $css[] = "{$this->cssTranslate['backgroundTheme'][$backgroundTheme]} {$this->cssTranslate['borderColorClass'][$backgroundTheme]}";
        }
        return join(' ', $css)?:null;
    }
    protected function itemStyle() {
        $styleMap = Preview_View::style_map();
        $backgroundColor = $this->data['meta']['style']['background-color'];
        $fixedStyle = [];
        if (@$styleMap['color']) {
            $fixedStyle[] = $styleMap['color'];
        }
        if (@$styleMap['background-color']) {
            $fixedStyle[] = $styleMap['background-color'];
            $fixedStyle[] = "border-color:{$backgroundColor} !important";
        }
        return join(';',$fixedStyle)?:NULL;
    }
    protected function checkedItemStyle() {
        $styleMap = Preview_View::style_map();
        $backgroundColor = $this->data['meta']['style']['background-color'];
        if (@$styleMap['background-color']) {
            $rgba = $this->get_Rgba_Info($backgroundColor);
            return "background-color:rgba(".$rgba['r'].",".$rgba['g'].",".$rgba['b'].",".($rgba['a'] * 0.75).") !important";
        }
        return null;
    }
    private function items(){
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
