<?php
namespace app\modules\build\views\preview\vant;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Valuable_View;
use yangzie\YZE_View_Component;


class Input_View extends Preview_View implements Valuable_View {
    use  Vant_Popup,Html_Code_Helper,Alpine {
        Alpine::build_code as alpineBuildCode;
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
        echo $this->output_main_attrs();
        echo ">";
        $this->wrap_icon(function() use($outputDataName, $inputDataName, $isArr, $iteratorDataName){
            echo '<input';
            echo $this->wrap_output('type', @$this->data['meta']['custom']['inputType'] ?: 'text');
            echo $this->wrap_output('class', 'van-field__control');
            echo $this->wrap_output('autocomplete', $this->data['meta']['custom']['autocomplete']?:NULL);
            echo $this->wrap_output('maxlength', $this->data['meta']['custom']['maxLength']?:NULL);

            if (!$outputDataName['VALUE']){
                echo $this->wrap_output(':value', $inputDataName);
            }else{
                echo $this->wrap_output(':value', $isArr ? $iteratorDataName : $outputDataName['VALUE']);
            }

            if ($this->data['meta']['custom']['wordCountVisible'] || $this->data['meta']['custom']['clearButtonVisible']){
                echo $this->wrap_output('@keyup', $this->myid().'_keyup');
            }

            echo $this->output_form_attrs();
            echo ">".PHP_EOL;
        },1);

        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(1) . "<div class='van-ml-3'><span x-text='alpinejs_get_value(\$el, \"{$myid}_wordCount{$indexSuffix}\")||0'></span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>".PHP_EOL;
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(1) . '<div';
            echo $this->wrap_output('@click', $this->myid().'_clear');
            echo $this->wrap_output('x-show', 'alpinejs_get_value($el, \''.$myid.'_clearButtonVisible'.$indexSuffix.'\')');
            echo ' class="cursor van-ml-3"><i class="van-badge__wrapper van-icon van-icon-cross"></i></div>'.PHP_EOL;
        }

        echo "{$space}</div>".PHP_EOL;
    }
    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $style['[data-uiid='.$this->myid().$this->data['type'].']'] = 'font-style: inherit !important;color: inherit;';
        return $style;
    }
    protected function css_map() {
        $map = parent::css_map();
        $css = ['van-justify-content-between van-align-items-center van-bg-transparent'];
        if (@$this->data['meta']['custom']['borderless']){
            $css[] = 'van-border-0';
        }
        if (@$this->data['meta']['form']['state']=='disabled'){
            $css[] = 'van-disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $css[] = 'van-readonly';
        }
        if (@$this->data['meta']['form']['state'] == 'hidden'){
            $css[] = 'van-d-none';
        }else{
            $css[] = 'van-d-flex';
        }
        $map['-'] = join(' ', $css);
        return $map;
    }

    protected function output_as_prop($outputAs, $outputData)
    {
        if (strtolower($outputAs) == 'value'){// value绑定到内部input上
            return null;
        }
        return parent::output_as_prop($outputAs, $outputData);
    }
    public function build_code(): Base_Code_Fragment
    {
        $this->alpineBuildCode();
        $hasIterate = $this->need_iterate_data($iterateOutputAs, $outputDataName, $iterateDataName);
        $codeLines = [];
        $codeFragment = $this->get_code_Fragment();
        $inputDataName = $this->get_input_data_name($inputIsArr);
        $wordCountVisible = $this->data['meta']['custom']['wordCountVisible'];
        $clearButtonVisible = $this->data['meta']['custom']['clearButtonVisible'];
        $myId = $this->myid();

        if($hasIterate && $inputIsArr){
            if ($wordCountVisible){
                $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, "{$myId}_wordCount: [],");
            }
            if ($clearButtonVisible){
                $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, "{$myId}_clearButtonVisible: [],");
            }
        }else{
            if ($wordCountVisible){
                $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, "{$myId}_wordCount: 0,");
                $codeFragment->add_code(Html_Code_Fragment::SECTION_INIT, "this.{$myId}_wordCount = this.{$inputDataName}.length;");
            }
            if ($clearButtonVisible){
                $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, "{$myId}_clearButtonVisible: false,");
                $codeFragment->add_code(Html_Code_Fragment::SECTION_INIT, "this.{$myId}_clearButtonVisible = this.{$inputDataName}.length > 0;");
            }
        }

        // keyup事件
        if ($wordCountVisible || $clearButtonVisible){
            $codeLines[] = "{$myId}_keyup (event) {";
            $codeLines[] = $this->indent(1, true)."alpinejs_input_keyup(this, event.target, '{$myId}', '{$inputDataName}')";
            $codeLines[] = '},';
        }

        // click事件
        if (@$clearButtonVisible){
            $codeLines[] = "{$myId}_clear (event) {";
            $codeLines[] = $this->indent(1, true)."alpinejs_input_clear(this, event.target, '{$myId}', '{$inputDataName}')";
            $codeLines[] = "},";
        }
        $this->get_code_Fragment()->add_code(Html_Code_Fragment::SECTION_EVENT, $codeLines);
        return $this->get_code_fragment();
    }

}
