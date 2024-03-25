<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Input_View as Preview_Input_View;

class Input_View extends Preview_Input_View {
    use Vue;
    function build_code():Base_Code_Fragment
    {
        parent::build_code();
        $myid = $this->myId();
        $defaultValue = @$this->data['meta']['value'];
        $this->get_code_fragment()->add_ref($myid, '', true);
        $this->get_code_fragment()->add_ref("{$myid}Value", "{$defaultValue}", true);
        $this->get_code_fragment()->add_import('vue', ['ref']);
        $this->get_code_fragment()->add_import('bootstrap');

        if (@$this->data['meta']['custom']['wordCountVisible']){
            $this->get_code_fragment()->add_ref("{$myid}WordCount", 0, true);

            ob_start();
?>
() => {
  <?= $this->myId()?>WordCount.value = <?= $this->myId()?>Value.value.length
}
<?php
            $this->get_code_fragment()->add_function("{$myid}Keyup", ob_get_clean(), true);
        }
        if (@$this->data['meta']['custom']['clearButtonVisible']){
            ob_start();
?>
() => {
  <?= $this->myId()?>WordCount.value = 0
  <?= $this->myId()?>Value.value = ''
}
<?php
            $this->get_code_fragment()->add_function("{$myid}Clean", ob_get_clean(), true);
        }
        return $this->get_code_fragment();
    }
    public function build_ui()
    {
        $space =  $this->indent(2);
        $myid = $this->myId();
        echo "{$space}<div";
        echo $this->wrap_output('class', $this->body_css());
        echo $this->wrap_output('style', $this->body_style());

        echo " ref='{$myid}'>\r\n";
        $this->indent(3);
        $this->wrap_icon(function() use ($myid){
            echo $this->indent(4).'<input type="';
            echo @$this->data['meta']['custom']['inputType'] ?: 'text';
            echo '" class="w-100 border-0" ';
            if (@$this->data['meta']['custom']['autocomplete']){
                echo " autocomplete='".$this->data['meta']['custom']['autocomplete']."'";
            }
            echo $this->build_form_attrs();
            if (@$this->data['meta']['custom']['maxLength']){
                echo ' maxlength='.$this->data['meta']['custom']['maxLength'];
            }
            if (@$this->data['meta']['custom']['wordCountVisible']){
                echo " @keyup='{$myid}Keyup'";
            }
            echo " v-model='{$myid}Value'>\r\n";
        });

        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(3) . "<div class='word-count ml-3'>{{{$myid}WordCount}}";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>\r\n";
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(3) . "<div class='cursor ml-3' @click='{$myid}Clean'>×</div>\r\n";
        }

        echo "{$space}</div>\r\n";
    }
}
