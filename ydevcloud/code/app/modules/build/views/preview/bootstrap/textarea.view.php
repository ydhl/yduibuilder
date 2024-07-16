<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;

use app\modules\build\views\preview\Preview_View;

/**
 * <div>
 *  <textarea></textarea>
 *  <div class='ml-1'><span class='word-count'></span></div>
 *  <div>×</div>
 * </div>
 */
class Textarea_View extends Input_View {
    public function build_ui()
    {
        $space =  $this->indent();
        $inputDataName = $this->get_input_data_name($isArr);
        $outputDatas = $this->get_output_datas($outputDataName);
        $wordCountVisible = $this->data['meta']['custom']['wordCountVisible'];
        $clearButtonVisible = $this->data['meta']['custom']['clearButtonVisible'];
        $indexSuffix = '';
        if ($this->get_iterator_index_name()){
            $indexSuffix = "[-1]";
        }
        $iteratorDataName = $this->get_iterator_data_name();
        $myid = $this->myid();

        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;
        echo $this->indent(1);
        echo '<textarea class="w-100 border-0 bg-transparent input"';
        echo $this->wrap_output('style',$this->data['meta']['custom']['autoRow'] ? 'resize: none' : null);
        echo $this->wrap_output('maxlength', $this->data['meta']['custom']['maxLength']?:NULL);
        $this->build_form_attrs();
        echo $this->wrap_output('@keyup', ($wordCountVisible || $clearButtonVisible) ? $this->myid().'_keyup' : null);
        echo $this->wrap_output('rows', @$this->data['meta']['custom']['row']);

        if ($inputDataName && !$outputDataName['VALUE']){
            echo $this->wrap_output(':value', $inputDataName);
        }elseif ($outputDataName['VALUE']){
            echo $this->wrap_output(':value', $isArr ? $iteratorDataName : $outputDataName['VALUE']);
        }else{
            echo $this->wrap_output('value', @$this->data['meta']['value']);
        }

        echo '>';
        echo "</textarea>".PHP_EOL;

        if ($wordCountVisible){
            echo $this->indent(1);
            echo "<div class='ml-1'><span class='word-count' x-text='alpinejs_get_value(\$el, \"{$myid}_wordCount{$indexSuffix}\")'></span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>".PHP_EOL;
        }

        if ($clearButtonVisible){
            echo $this->indent(1);
            echo "<div @click='{$myid}_clear' class='cursor ml-1' x-show='alpinejs_get_value(\$el, \"{$myid}_clearButtonVisible{$indexSuffix}\")'>×</div>".PHP_EOL;
        }

        echo "{$space}</div>".PHP_EOL;
    }
    protected function css_map() {
        $css = parent::css_map();
        $css['-'] = preg_replace('{align-items-\S+}', '', $css['-']);
        $css['-'] .= ' align-items-end h-auto overflow-hidden';
        return $css;
    }
}
