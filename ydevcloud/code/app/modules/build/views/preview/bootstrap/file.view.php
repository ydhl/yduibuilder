<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Valuable_View;
use yangzie\YZE_View_Component;

class File_View extends Preview_View implements Valuable_View {
    use Bootstrap_Popup,Html_Code_Helper;

    public function build_ui()
    {
        $space =  $this->indent(0);
        $inputDataName = $this->get_input_data_name($inputIsArr);
        echo "{$space}";
        echo "<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;
        echo $this->indent(1);
        echo '<input type="file" class="d-block"';
        echo $this->build_form_attrs(true, false);
        echo $this->wrap_output('x-input', $inputDataName);// file 组件总是用x-input，绑定的输入数据里面是FileList，而x-model得到的只是文件名
        echo $this->wrap_output('accept', $this->data['meta']['custom']['accept']?:null);
        echo $this->wrap_output('multiple', null, $this->data['meta']['custom']['multiple']?:null);
        echo ">".PHP_EOL;
        echo "{$space}";
        echo "</div>".PHP_EOL;
    }
    protected function css_map() {
        $css = parent::css_map();
        $styleMap = parent::style_map();
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
        if (@$styleMap['color']){
            unset($css['foregroundTheme']);
        }
        if (@$styleMap['background-color']){
            unset($css['backgroundTheme']);
        }
        return $css;
    }

}
