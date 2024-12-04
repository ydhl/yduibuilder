<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Hr_View as Preview_Hr_View;
use app\modules\build\views\code\web\Vue;

class Hr_View extends Preview_Hr_View {
    use Vue {
        Vue::output_as_prop as vue_output_as_prop;
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {

        $outputData = $this->get_output_datas($outputDataName);

        $space =  $this->indent();
        echo "{$space}<HrComponent";
        $this->output_component_props();
        echo $this->wrap_output('lineCss', $this->lineCss());
        echo $this->wrap_output('textCss', $this->textCss());
        echo ">";
        // 文字
        if ($outputData['HTML']){
            $htmlDataName = $this->get_output_data_name('HTML', $outputData['HTML'], $outputDataName['HTML']);
            echo '<span v-html="'.$htmlDataName.'"></span>';
        }else if ($outputData['TEXT']){
            $textDataName = $this->get_output_data_name('TEXT', $outputData['TEXT'], $outputDataName['TEXT']);
            echo "{{{$textDataName}}}";
        }else{
            if ($this->data['meta']['value']){
                echo htmlentities($this->data['meta']['value']);
            }
        }
        echo "</HrComponent>".PHP_EOL;

    }

    protected function output_as_prop($outputAs, $outputData){
        if (in_array(strtolower($outputAs), ['html', 'text'])){
            return ;// 不在主元素上输出，在子元素上输出，所以这里返回null
        }
        return $this->vue_output_as_prop($outputAs, $outputData);
    }

    public function build_code(): Base_Code_Fragment{
        $fragment = $this->vueBuildCode();
        $fragment->add_import('@/components/HrComponent.vue', [], 'HrComponent');
        return $fragment;
    }
}
