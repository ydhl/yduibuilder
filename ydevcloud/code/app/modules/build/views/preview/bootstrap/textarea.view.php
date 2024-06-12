<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;

use app\modules\build\views\preview\Preview_View;

class Textarea_View extends Input_View {
    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $style['[data-uiid='.$this->myid().$this->data['type'].']'] = 'font-style: inherit !important;color: inherit';
        return $style;
    }

    protected function css_map() {
        $css = parent::css_map();
        $css[] = 'form-control d-flex justify-content-between align-items-end h-auto overflow-hidden';
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing']!='normal'){
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
        $space =  $this->indent();
        $inputData = $this->get_input_data($dataName);
        $outputDatas = $this->get_output_datas($outputDataName);
        $hasIterate = $this->need_iterate_data($iterateOutputAs, $dataName, $iterateDataName);
        $wordCountVisible = $this->data['meta']['custom']['wordCountVisible'];
        $clearButtonVisible = $this->data['meta']['custom']['clearButtonVisible'];
        echo "{$space}<div";
        echo $this->build_main_attrs();
        $indexSuffix = '';
        if ($hasIterate && $this->is_array($inputData)){
            $indexSuffix = "[idxOf{$iterateDataName}]";
            echo $this->wrap_output(':data-index', "idxOf{$iterateDataName}");
        }
        echo ">".PHP_EOL;
        echo $this->indent(1);
        echo '<textarea class="w-100 border-0 bg-transparent input"';
        if (@$this->data['meta']['custom']['autoRow']){
            echo $this->wrap_output('style','resize: none');
        }
        if (@$this->data['meta']['custom']['maxLength']){
            echo $this->wrap_output('maxlength', $this->data['meta']['custom']['maxLength']);
        }
        echo $this->build_form_attrs();

        if(!$inputData){
            echo $this->wrap_output('x-model.fill', $this->myid().'_temp');
        }

        if ($wordCountVisible || $clearButtonVisible){
            echo $this->wrap_output('@keyup', $this->myid().'_keyup');
        }
        echo $this->wrap_output('rows', @$this->data['meta']['custom']['row']);
        if($outputDataName['VALUE']) echo $this->wrap_output('x-text', $outputDataName['VALUE']);
        echo '>';

        if(!$outputDataName['VALUE']) echo @$this->data['meta']['value'];
        echo "</textarea>".PHP_EOL;

        if ($wordCountVisible){
            echo $this->indent(1);
            echo "<div class='ml-3'><span class='word-count' x-text='".$this->myid()."_wordCount{$indexSuffix}'></span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>".PHP_EOL;
        }

        if ($clearButtonVisible){
            echo $this->indent(1);
            echo "<div @click='".$this->myid()."_clear' class='cursor ml-3' x-show='".$this->myid()."_clearButtonVisible{$indexSuffix}'>×</div>".PHP_EOL;
        }

        echo "{$space}</div>".PHP_EOL;
    }
}
