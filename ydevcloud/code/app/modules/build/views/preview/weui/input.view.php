<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Valuable_View;

/**
 * <pre>
 *  <label for="js_input2" class="weui-cell weui-cel1l_active">
 *      <div class="weui-cell__hd"><span class="weui-label">持卡人</span></div>
 *      <div class="weui-cell__bd weui-flex">
 *          <input id="js_input2" class="weui-input" type="text" placeholder="请输入持卡人姓名">
 *      </div>
 *  </label>
 * </pre>
 */
class Input_View extends Preview_View implements Valuable_View {
    use Weui_Popup,Html_Code_Helper,Alpine {
        Alpine::build_code as alpineBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $inputDataName = $this->get_input_data_name($isArr);
        $outputDatas = $this->get_output_datas($outputDataNames);
        $indexSuffix = '';
        if ($this->get_iterator_index_name()){
            $indexSuffix = "[-1]";
        }
        $iteratorDataName = $this->get_iterator_data_name();
        $myid = $this->myid();
        $meta = $this->data['meta'];

        echo "{$space}<label";
        $this->build_main_attrs();
        echo $this->wrap_output(':for', "\$id('{$myid}','-input')");
        echo ">".PHP_EOL;
        echo $this->indent(1).'<div class="weui-cell__hd"><span class="weui-label">'.$meta['title'].'</span></div>'.PHP_EOL;
        echo $this->indent(1).'<div class="weui-cell__bd weui-flex align-items-center">';

        $this->wrap_icon(function() use($iteratorDataName, $isArr, $outputDataNames, $inputDataName, $myid){
            echo '<input'.$this->wrap_output('type', @$this->data['meta']['custom']['inputType'] ?: 'text');
            echo ' class="w-100 border-0 bg-transparent weui-input input" ';// input 用于前端jas处理时找input元素
            echo $this->wrap_output("autocomplete", $this->data['meta']['custom']['autocomplete']?:NULL);
            echo $this->wrap_output('maxlength', $this->data['meta']['custom']['maxLength']?:NULL);
            echo $this->wrap_output(':id', "\$id('{$myid}','-input')");
            echo $this->build_form_attrs();

            if ($this->data['meta']['custom']['wordCountVisible'] || $this->data['meta']['custom']['clearButtonVisible']){
                echo $this->wrap_output('@keyup', $this->myid().'_keyup');
            }
            if (!$outputDataNames['VALUE']){
                echo $this->wrap_output(':value', $inputDataName);
            }else{
                echo $this->wrap_output(':value', $isArr ? $iteratorDataName : $outputDataNames['VALUE']);
            }
            echo ">".PHP_EOL;
        },2);

        if (@$meta['custom']['wordCountVisible']){
            echo $this->indent(2) . "<div class='ml-1'><span class='word-count' x-text='alpinejs_get_value(\$el, \"{$myid}_wordCount{$indexSuffix}\")'></span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>".PHP_EOL;
        }

        if (@$meta['custom']['clearButtonVisible']){
            echo $this->indent(2) . "<button @click='".$this->myid()."_clear' type='button' class='weui-btn_reset weui-btn_icon ml-2' x-show='alpinejs_get_value(\$el, \"{$myid}_clearButtonVisible{$indexSuffix}\")'><i class=\"weui-icon-clear\"></i></button>".PHP_EOL;
        }

        echo $this->indent(1) . "</div>".PHP_EOL;

        echo "{$space}</label>".PHP_EOL;
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
        return parent::output_as_prop($outputAs, $outputData);
    }

    protected function css_map() {
        $css = parent::css_map();
        $styleMap = parent::style_map();
        if ($styleMap['background-color']) unset($css['backgroundTheme']);
        if ($styleMap['color']) unset($css['foregroundTheme']);
        $myCss = ['weui-cell weui-cell_active'];

        if (@$this->data['meta']['form']['state']=='disabled'){
            $myCss[] = 'disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $myCss[] = 'readonly';
        }
        if (@$this->data['meta']['form']['state']=='hidden'){
            $myCss[] = 'd-none';
        }else{
            $myCss[] = '';
        }
        $css['-'] = join(' ', $myCss);
        return $css;
    }
}
