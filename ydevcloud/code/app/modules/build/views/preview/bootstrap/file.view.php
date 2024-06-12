<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use yangzie\YZE_View_Component;

class File_View extends Preview_View {
    use Bootstrap_Popup,Html_Code_Helper;
    protected function css_map() {
        $css = parent::css_map();
        $css[] = 'd-flex align-items-center overflow-hidden';
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing'] != 'normal'){
            $css[] = ' form-control-'.$this->data['meta']['css']['formSizing'];
        }
        if (@$this->data['meta']['form']['state']=='disabled'){
            $css[] = ' disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $css[] = ' readonly';
        }
        return $css;
    }

    public function build_ui()
    {
        $space =  $this->indent(0);
        $inputData = $this->get_input_data($dataName);
        echo "{$space}";
        echo "<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;
        echo $this->indent(1);
        echo '<input type="file" class="d-block"';
        echo $this->build_form_attrs();
        if(!$inputData){
            echo $this->wrap_output('x-model', $this->myid().'_temp');
        }

        if (@$this->data['meta']['custom']['accept']){
            echo ' accept="'.$this->data['meta']['custom']['accept'].'"';
        }
        if (@$this->data['meta']['custom']['multiple']){
            echo ' multiple';
        }
        echo ">".PHP_EOL;
        echo "{$space}";
        echo "</div>".PHP_EOL;
    }

    function build_code(): Base_Code_Fragment
    {
        parent::build_code();
        $codeFragment = $this->get_code_Fragment();
        $inputData = $this->get_input_data($dataName);
        // 如果没有数据绑定的话，定义一个临时数据
        if (!$dataName) {
            $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid() . '_temp: "",');
        }
        return $codeFragment;
    }
}
