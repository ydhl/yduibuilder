<?php
namespace app\modules\build\views\preview\vant;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\preview\Html_Code_Helper;

use app\modules\build\views\preview\Preview_View;

class Textarea_View extends Input_View {
    use  Vant_Popup,Html_Code_Helper;

    protected function css_map() {
        $map = parent::css_map();
        $css = ['van-justify-content-between van-align-items-end van-h-auto'];

        if (@$this->data['meta']['form']['state']=='disabled'){
            $css[] = ' van-disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $css[] = ' van-readonly';
        }
        if (@$this->data['meta']['form']['state']=='hidden'){
            $css[] = ' van-d-none';
        }else{
            $css[] = 'van-d-flex';
        }
        $map['-'] = join(' ', $css);
        return $map;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $inputDataName = $this->get_input_data_name($isArr);
        $outputDatas = $this->get_output_datas($outputDataName);

        $indexSuffix = '';
        if ($this->get_iterator_index_name()){
            $indexSuffix = "[-1]";
        }
        $iteratorDataName = $this->get_iterator_data_name();
        $myid = $this->myid();
        echo "{$space}<div";
        $this->output_main_attrs();
        echo ">".PHP_EOL;
        echo $this->indent(1);
        echo '<textarea class="van-field__control"';
        echo $this->wrap_output('style', $this->data['meta']['custom']['autoRow'] ? 'resize: none' : NULL);
        echo $this->output_form_attrs();
        echo $this->wrap_output('maxlength', $this->data['meta']['custom']['maxLength'] ?: NULL);
        echo $this->wrap_output('rows', $this->data['meta']['custom']['row']);

        if (!$outputDataName['VALUE']){
            echo $this->wrap_output(':value', $inputDataName);
        }else{
            echo $this->wrap_output(':value', $isArr ? $iteratorDataName : $outputDataName['VALUE']);
        }
        if ($this->data['meta']['custom']['wordCountVisible'] || $this->data['meta']['custom']['clearButtonVisible']){
            echo $this->wrap_output('@keyup', $this->myid().'_keyup');
        }
        echo "></textarea>".PHP_EOL;

        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(1);
            echo "<div class='van-ml-3'><span  x-text='alpinejs_get_value(\$el, \"{$myid}_wordCount{$indexSuffix}\")||0'></span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>".PHP_EOL;
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(1);
            echo '<div';
            echo $this->wrap_output('@click', $this->myid().'_clear');
            echo $this->wrap_output('x-show', 'alpinejs_get_value($el, \''.$myid.'_clearButtonVisible'.$indexSuffix.'\')');
            echo 'class="cursor van-ml-3"><i class="van-badge__wrapper van-icon van-icon-cross"></i></div>'.PHP_EOL;
        }

        echo "{$space}</div>".PHP_EOL;
    }
    public function build_code(): Base_Code_Fragment
    {
        return parent::build_code();
    }
}
