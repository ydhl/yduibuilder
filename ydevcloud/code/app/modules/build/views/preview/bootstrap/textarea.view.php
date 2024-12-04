<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;

use app\modules\build\views\preview\Preview_View;

/**
 * <div>
 *  <textarea></textarea>
 * <div class='action'>
 *  <div class='ms-1'><span class='word-count'></span></div>
 *  <div>×</div>
 * </div>
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
        $eventHandlers = $this->get_event_listen_props();

        echo "{$space}<div";
        echo $this->wrap_output("style", "position: relative;");
        echo $this->wrap_output("data-root", null, true);
        echo $this->wrap_output(":data-index", $this->get_iterator_index_name());
        echo ">".PHP_EOL;
        echo $this->indent(1);
        echo '<textarea';
        echo $this->output_main_attrs(true, true, false);
        echo $this->wrap_output('style',$this->data['meta']['custom']['autoRow'] ? 'resize: none' : null);
        echo $this->wrap_output('maxlength', $this->data['meta']['custom']['maxLength']?:NULL);
        $this->output_form_attrs(true, false);
        echo $this->wrap_output('@keyup', ($wordCountVisible || $clearButtonVisible) ? $this->myid().'_keyup' : null);
        echo $this->wrap_output('rows', @$this->data['meta']['custom']['row']);

        echo $this->wrap_output('@blur', $eventHandlers['@blur']);
        echo $this->wrap_output('@focus', $eventHandlers['@focus']);

        if ($inputDataName && !$outputDataName['VALUE']){
            echo $this->wrap_output(':value', $inputDataName);
        }elseif ($outputDataName['VALUE']){
            echo $this->wrap_output(':value', $isArr ? $iteratorDataName : $outputDataName['VALUE']);
        }else{
            echo $this->wrap_output('value', @$this->data['meta']['value']);
        }

        echo '>';
        echo "</textarea>".PHP_EOL;

        if ($wordCountVisible || $clearButtonVisible){
            echo $this->indent(1) . '<div';
            echo $this->wrap_output('style', $this->get_action_style());
            echo $this->wrap_output('class', $this->get_action_class());
            echo '>'.PHP_EOL;
        }
        if ($wordCountVisible){
            echo $this->indent(2);
            echo "<span class='word-count' x-text='alpinejs_get_value(\$el, \"{$myid}_wordCount{$indexSuffix}\")'></span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo " / ".$this->data['meta']['custom']['maxLength'];
            }
            echo PHP_EOL;
        }

        if ($clearButtonVisible){
            echo $this->indent(2);
            echo "<div @click='{$myid}_clear' class='cursor' x-show='alpinejs_get_value(\$el, \"{$myid}_clearButtonVisible{$indexSuffix}\")'>×</div>".PHP_EOL;
        }

        if ($clearButtonVisible || $wordCountVisible){
            echo $this->indent(1) . "</div>".PHP_EOL;
        }
        echo "{$space}</div>".PHP_EOL;
    }
}
