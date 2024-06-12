<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\code\Io_Data_Fetch;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use yangzie\YZE_View_Component;


class Input_View extends Preview_View {
    use Bootstrap_Popup,Html_Code_Helper;

    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $style['[data-uiid='.$this->myid().$this->data['type'].']'] = 'font-style: inherit !important;color: inherit;';
        return $style;
    }

    protected function css_map() {
        $css = parent::css_map();
        $css[] = 'form-control justify-content-between align-items-center';
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing']!='normal'){
            $css[] = 'form-control-'.$this->data['meta']['css']['formSizing'];
        }
        if (@$this->data['meta']['custom']['borderless']){
            $css[] = 'border-0';
        }

        if (@$this->data['meta']['form']['state']=='disabled'){
            $css[] = 'disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $css[] = 'readonly';
        }
        if (@$this->data['meta']['form']['state']=='hidden'){
            $css[] = 'd-none';
        }else{
            $css[] = 'd-flex';
        }
        return $css;
    }

    function build_code(): Base_Code_Fragment
    {
        parent::build_code();
        $hasIterate = $this->need_iterate_data($iterateOutputAs, $outputDataName, $iterateDataName);
        $codeLines = [];
        $codeFragment = $this->get_code_Fragment();
        $inputData = $this->get_input_data($dataName);
        $wordCountVisible = $this->data['meta']['custom']['wordCountVisible'];
        $clearButtonVisible = $this->data['meta']['custom']['clearButtonVisible'];
        // 如果没有数据绑定的话，定义一个临时数据
        if (!$dataName){
            $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid().'_temp: "",');
            $dataName = $this->myid().'_temp';
        }
        if($hasIterate && $this->is_array($inputData)){
            if ($wordCountVisible){
                $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid().'_wordCount: [],');
            }
            if ($clearButtonVisible){
                $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid().'_clearButtonVisible: [],');
            }
        }else{
            if ($wordCountVisible){
                $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid().'_wordCount: 0,');
                $codeFragment->add_code(Html_Code_Fragment::SECTION_INIT, 'this.'.$this->myid().'_wordCount = this.'.$dataName.'.length;');
            }
            if ($clearButtonVisible){
                $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid().'_clearButtonVisible: false,');
                $codeFragment->add_code(Html_Code_Fragment::SECTION_INIT, 'this.'.$this->myid().'_clearButtonVisible = this.'.$dataName.'.length > 0;');
            }
        }
        $indexSuffix = '';
        if ($hasIterate && $this->is_array($inputData)) {
            $indexSuffix = '[index]';
        }
        // keyup事件
        if ($wordCountVisible || $clearButtonVisible){
            $codeLines[] = $this->myid().'_keyup (event) {';
            $codeLines[] = $this->indent(1, true)."const page = this";
            if ($hasIterate && $this->is_array($inputData)) {
                $codeLines[] = $this->indent(1, true) . "const ui = event.target.closest(\"[data-uiid='".$this->myid()."']\")";
                $codeLines[] = $this->indent(1, true) . "const index = ui.dataset.index";
            }

            if ($wordCountVisible){
                $codeLines[] =  $this->indent(1, true).'page.'.$this->myid().'_wordCount'.$indexSuffix.' = page.'.$dataName.$indexSuffix.'.length;';
            }
            if ($clearButtonVisible){
                $codeLines[] = $this->indent(1, true).'if (page.'.$dataName.$indexSuffix.'.length>0){';
                $codeLines[] = $this->indent(2, true).'page.'.$this->myid().'_clearButtonVisible'.$indexSuffix.' = true';
                $codeLines[] = $this->indent(1, true).'}else{';
                $codeLines[] = $this->indent(2, true).'page.'.$this->myid().'_clearButtonVisible'.$indexSuffix.' = false';
                $codeLines[] = $this->indent(1, true).'}';
            }
            $codeLines[] = '},';
        }

        // click事件
        if (@$clearButtonVisible){
            $codeLines[] = $this->myid().'_clear (event) {';
            $codeLines[] = $this->indent(1, true)."const page = this";
            if ($hasIterate && $this->is_array($inputData)) {
                $codeLines[] = $this->indent(1, true) . "const ui = event.target.closest(\"[data-uiid='".$this->myid()."']\")";
                $codeLines[] = $this->indent(1, true) . "const index = ui.dataset.index";
            }

            // 清空值
            $codeLines[] = $this->indent(1, true).'page.'.$dataName.$indexSuffix.'= "";';
            //清空状态
            $codeLines[] = $this->indent(1, true).'page.'.$this->myid().'_wordCount'.$indexSuffix.' = 0';
            $codeLines[] = $this->indent(1, true).'page.'.$this->myid().'_clearButtonVisible'.$indexSuffix.' = false';

            $codeLines[] = '},';
        }
        $this->get_code_Fragment()->add_code(Html_Code_Fragment::SECTION_EVENT, $codeLines);
        return $this->get_code_fragment();
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $inputData = $this->get_input_data($dataName);
        $outputDatas = $this->get_output_datas($outputDataName);
        $hasIterate = $this->need_iterate_data($iterateOutputAs, $dataName, $iterateDataName);
        echo "{$space}<div";
        echo $this->build_main_attrs();
        $indexSuffix = '';
        if ($hasIterate && $this->is_array($inputData)){
            $indexSuffix = "[idxOf{$iterateDataName}]";
            echo $this->wrap_output(':data-index', "idxOf{$iterateDataName}");
        }
        echo ">";
        echo $this->indent(1);
        $this->wrap_icon(function() use($inputData, $outputDataName){
            echo '<input type="';
            echo @$this->data['meta']['custom']['inputType'] ?: 'text';
            echo '" class="w-100 border-0 bg-transparent input" ';
            if (@$this->data['meta']['custom']['autocomplete']){
                echo " autocomplete='".$this->data['meta']['custom']['autocomplete']."'";
            }
            echo $this->build_form_attrs();
            if(!$inputData){
                echo $this->wrap_output('x-model.fill', $this->myid().'_temp');
            }

            if ($this->data['meta']['custom']['wordCountVisible'] || $this->data['meta']['custom']['clearButtonVisible']){
                echo $this->wrap_output('@keyup', $this->myid().'_keyup');
            }
            if (@$this->data['meta']['custom']['maxLength']){
                echo ' maxlength='.$this->data['meta']['custom']['maxLength'];
            }
            if ($outputDataName['VALUE']){
                echo $this->wrap_output(':value', $outputDataName['VALUE']);
            }else{
                echo $this->wrap_output('value', @$this->data['meta']['value']);
            }
            echo ">".PHP_EOL;
        },1);

        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(1) . "<div class='ml-3'><span class='word-count' x-text='".$this->myid()."_wordCount{$indexSuffix}'></span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>".PHP_EOL;
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(1) . "<div @click='".$this->myid()."_clear' class='cursor ml-3' x-show='".$this->myid()."_clearButtonVisible{$indexSuffix}'>×</div>".PHP_EOL;
        }

        echo "{$space}</div>".PHP_EOL;
    }
}
