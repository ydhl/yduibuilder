<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\code\Io_Data_Fetch;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Valuable_View;
use yangzie\YZE_View_Component;

/**
 * <pre>
 * <div class="form-control justify-content-between align-items-center d-flex">
 *  <input type="text" class="w-100 border-0 bg-transparent input">
 * </div>
 * </pre>
 */
class Input_View extends Preview_View implements Valuable_View {
    use Bootstrap_Popup,Html_Code_Helper,Alpine {
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
        $this->build_main_attrs();
        echo ">";
        echo $this->indent(1);
        $this->wrap_icon(function() use($iteratorDataName, $isArr, $outputDataName, $inputDataName){
            echo '<input'.$this->wrap_output('type', @$this->data['meta']['custom']['inputType'] ?: 'text');
            echo ' class="w-100 border-0 bg-transparent input" ';// input 用于前端jas处理时找input元素
            if (@$this->data['meta']['custom']['autocomplete']){
                echo " autocomplete='".$this->data['meta']['custom']['autocomplete']."'";
            }
            echo $this->build_form_attrs();

            if ($this->data['meta']['custom']['wordCountVisible'] || $this->data['meta']['custom']['clearButtonVisible']){
                echo $this->wrap_output('@keyup', $this->myid().'_keyup');
            }
            if (@$this->data['meta']['custom']['maxLength']){
                echo ' maxlength='.$this->data['meta']['custom']['maxLength'];
            }
            if ($inputDataName && !$outputDataName['VALUE']){
                echo $this->wrap_output(':value', $inputDataName);
            }elseif ($outputDataName['VALUE']){
                echo $this->wrap_output(':value', $isArr ? $iteratorDataName : $outputDataName['VALUE']);
            }else{
                echo $this->wrap_output('value', @$this->data['meta']['value']);
            }
            echo ">".PHP_EOL;
        },1);

        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(1) . "<div class='ml-1'><span class='word-count' x-text='alpinejs_get_value(\$el, \"{$myid}_wordCount{$indexSuffix}\")'></span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>".PHP_EOL;
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(1) . "<div @click='".$this->myid()."_clear' class='cursor ml-1' x-show='alpinejs_get_value(\$el, \"{$myid}_clearButtonVisible{$indexSuffix}\")'>×</div>".PHP_EOL;
        }

        echo "{$space}</div>".PHP_EOL;
    }
    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $style['[data-uiid='.$this->myid().$this->data['type'].']'] = 'font-style: inherit !important;color: inherit;';
        return $style;
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
        $indexSuffix = '';
        if ($hasIterate && $inputIsArr) {
            $indexSuffix = '[-1]';
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
    protected function output_as_prop($outputAs, $outputData)
    {
        if (strtolower($outputAs) == 'value'){// value绑定到内部input上
            return null;
        }
        return parent::output_as_prop($outputAs, $outputData); // TODO: Change the autogenerated stub
    }

    protected function css_map() {
        $css = parent::css_map();
        $styleMap = parent::style_map();
        if ($styleMap['background-color']) unset($css['backgroundTheme']);
        if ($styleMap['color']) unset($css['foregroundTheme']);
        $myCss[] = 'form-control justify-content-between align-items-center';
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing']!='normal'){
            $myCss[] = 'form-control-'.$this->data['meta']['css']['formSizing'];
        }
        if (@$this->data['meta']['custom']['borderless']){
            $myCss[] = 'border-0';
        }

        if (@$this->data['meta']['form']['state']=='disabled'){
            $myCss[] = 'disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $myCss[] = 'readonly';
        }
        if (@$this->data['meta']['form']['state']=='hidden'){
            $myCss[] = 'd-none';
        }else{
            $myCss[] = 'd-flex';
        }
        $css['-'] = join(' ', $myCss);
        return $css;
    }
}
