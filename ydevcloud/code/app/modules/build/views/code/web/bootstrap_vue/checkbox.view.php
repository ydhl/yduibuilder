<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\web\Vue;

class Checkbox_View extends Radio_View {
    use Vue {
        Vue::build_code as vueBuildCode;
    }

    protected $component = 'CheckboxComponent';

    public function build_code():Base_Code_Fragment
    {
        $this->vueBuildCode();
        $fragment = $this->get_code_fragment();
        $fragment->add_import('@/components/CheckboxComponent.vue', [], 'CheckboxComponent');
        return $fragment;
    }

    protected function output_v_model(){
        $needIterate = $this->get_iterator_index_names();
        $inputDataName = $this->get_input_data_name($inputIsArr, $inputDataConfig);
        echo $needIterate && $inputIsArr ? $this->wrap_output('v-input.checkbox', $inputDataName) : $this->wrap_output('v-model', $inputDataName);
    }
}
