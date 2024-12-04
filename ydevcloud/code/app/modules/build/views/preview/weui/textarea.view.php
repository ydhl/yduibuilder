<?php
namespace app\modules\build\views\preview\weui;

/**
 * <pre>
 *  <div class="weui-cell weui-cell_active">
 *      <div class="weui-cell__bd">
 *          <textarea class="weui-textarea" placeholder="请描述你所发生的问题" rows="3"></textarea>
 *          <div role="option" aria-live="polite" class="weui-textarea-counter"><span>0</span>/200</div>
 *      </div>
 *  </div>
 * </pre>
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
        echo $this->output_main_attrs();
        echo ">".PHP_EOL;
        echo $this->indent(1).'<div class="weui-cell__hd w-100"><label class="weui-label">'.$this->data['meta']['title'].'</label></div>'.PHP_EOL;
        echo $this->indent(1).'<div class="weui-cell__bd w-100">'.PHP_EOL;
        echo $this->indent(2).'<textarea class="weui-textarea input"';
        echo $this->wrap_output('style',$this->data['meta']['custom']['autoRow'] ? 'resize: none' : null);
        echo $this->wrap_output('maxlength', $this->data['meta']['custom']['maxLength']?:NULL);
        $this->output_form_attrs();
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

        if ($wordCountVisible || $clearButtonVisible){
            echo $this->indent(2).'<div class="weui-flex align-items-center justify-content-end">';
        }

        if ($wordCountVisible){
            echo $this->indent(3);
            echo "<div class='ml-1'><span class='word-count' x-text='alpinejs_get_value(\$el, \"{$myid}_wordCount{$indexSuffix}\")'></span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>".PHP_EOL;
        }

        if ($clearButtonVisible){
            echo $this->indent(3);
            echo "<button @click='".$this->myid()."_clear' type='button' class='weui-btn_reset weui-btn_icon ml-2' x-show='alpinejs_get_value(\$el, \"{$myid}_clearButtonVisible{$indexSuffix}\")'><i class=\"weui-icon-clear\"></i></button>".PHP_EOL;
        }

        if ($wordCountVisible || $clearButtonVisible){
            echo $this->indent(2).'</div>';
        }
        echo $this->indent(1).'</div>';
        echo "{$space}</div>".PHP_EOL;
    }
    protected function css_map() {
        $css = parent::css_map();
//        $css['-'] = preg_replace('{align-items-\S+}', '', $css['-']);
        $css['-'] .= ' flex-column align-items-start';
        return $css;
    }
}
