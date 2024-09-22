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
 * <input class="form-control">
 * <div class='action'></div>
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
        $eventHandlers = $this->get_event_listen_props();

        echo "{$space}<div";
        echo $this->wrap_output("style", "position: relative;");
        echo $this->wrap_output(":data-index", $this->get_iterator_index_name());
        echo ">";
        echo $this->indent(1);
        $this->wrap_icon(function() use($iteratorDataName, $isArr, $outputDataName, $inputDataName, $eventHandlers){
            echo '<input'.$this->wrap_output('type', @$this->data['meta']['custom']['inputType'] ?: 'text');
            $this->build_main_attrs(true, true, false);
            echo $this->wrap_output("autocomplete", $this->data['meta']['custom']['autocomplete']?:NULL);
            echo $this->wrap_output('maxlength', $this->data['meta']['custom']['maxLength']?:NULL);
            echo $this->build_form_attrs(true, false);

            echo $this->wrap_output('@blur', $eventHandlers['@blur']);
            echo $this->wrap_output('@focus', $eventHandlers['@focus']);

            if ($this->data['meta']['custom']['wordCountVisible'] || $this->data['meta']['custom']['clearButtonVisible']){
                echo $this->wrap_output('@keyup', $this->myid().'_keyup');
            }
            if (!$outputDataName['VALUE']){
                echo $this->wrap_output(':value', $inputDataName);
            }else{
                echo $this->wrap_output(':value', $isArr ? $iteratorDataName : $outputDataName['VALUE']);
            }
            echo ">".PHP_EOL;
        },$this->get_build()->get_indent() + 1);

        if (@$this->data['meta']['custom']['wordCountVisible'] || $this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(1) . '<div';
            echo $this->wrap_output('style', $this->get_action_style());
            echo $this->wrap_output('class', $this->get_action_class());
            echo '>'.PHP_EOL;
        }
        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(2) . "<span class='word-count' x-text='alpinejs_get_value(\$el, \"{$myid}_wordCount{$indexSuffix}\")'></span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo PHP_EOL;
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(2) . "<div @click='".$this->myid()."_clear' class='cursor' x-show='alpinejs_get_value(\$el, \"{$myid}_clearButtonVisible{$indexSuffix}\")'>×</div>".PHP_EOL;
        }
        if (@$this->data['meta']['custom']['wordCountVisible'] || $this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(1) . "</div>".PHP_EOL;
        }

        echo "{$space}</div>".PHP_EOL;
    }
    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $style['[data-uiid='.$this->myid().'] .action'] = 'position: absolute; right: 10px; display: flex;column-gap: 10px;top: 0px;height: 100%;line-height: 2.4;';
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
        $myCss[] = 'form-control input';
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing']!='normal'){
            $myCss[] = 'form-control-'.$this->data['meta']['css']['formSizing'];
        }
        if (@$this->data['meta']['custom']['borderless']){
            $myCss[] = 'border-0';
        }

        if (@$this->data['meta']['custom']['wordCountVisible'] || $this->data['meta']['custom']['clearButtonVisible']){
            $myCss[] = 'pe-5';
        }

        if (@$this->data['meta']['form']['state']=='disabled'){
            $myCss[] = 'disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $myCss[] = 'readonly';
        }
        if (@$this->data['meta']['form']['state']=='hidden'){
            $myCss[] = 'd-none';
        }
        $css['-'] = join(' ', $myCss);
        return $css;
    }
    protected function get_action_style(){
        $style = [];
        if ($this->data['meta']['style']['color']){
            $style[] = 'color: '.$this->data['meta']['style']['color'];
        }
        $style[] = "position:absolute;right:10px;top:0px;height:100%;align-items:center;display:flex;gap:10px";
        return join(';', $style);
    }
    protected function get_action_class(){
        $cssMap = $this->css_map();
        if (!$this->data['meta']['style']['color'] && $cssMap['foreground']){
            return $cssMap['foreground'];
        }
        return null;
    }
}
