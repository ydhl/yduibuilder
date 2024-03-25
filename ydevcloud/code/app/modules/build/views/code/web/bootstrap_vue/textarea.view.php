<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Textarea_View as Preview_Textarea_View;

class Textarea_View extends Preview_Textarea_View {
    use Vue;
    public function build_code():Base_Code_Fragment
    {
        parent::build_code();
        $myid = $this->myId();
        $defaultValue = @$this->data['meta']['value'];
        $this->get_code_fragment()->add_import('vue', ['ref']);
        $this->get_code_fragment()->add_import('bootstrap');
        $this->get_code_fragment()->add_ref($myid, '', true);
        $this->get_code_fragment()->add_ref("{$myid}Value", $defaultValue, true);

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
        $myid = $this->myId();
        $space =  $this->indent(2);
        echo "{$space}<div";
        echo $this->wrap_output('class', $this->body_css());
        echo $this->wrap_output('style', $this->body_style());
        echo ">\r\n";
        echo $this->indent(3);
        echo '<textarea class="w-100 border-0"';
        if (@$this->data['meta']['custom']['autoRow']){
            echo ' style="resize: none" ';
        }
        echo $this->build_form_attrs();
        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo " @keyup='{$myid}Keyup'";
        }
        if (@$this->data['meta']['custom']['maxLength']){
            echo ' maxlength='.$this->data['meta']['custom']['maxLength'];
        }
        echo ' v-model="'.$myid.'Value" rows="'.@$this->data['meta']['custom']['row'].'">';
        echo "</textarea>\r\n";

        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(3);
            echo "<div class='word-count ml-3'>{{{$myid}WordCount}}";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>\r\n";
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(3);
            echo "<div class='cursor ml-3' @click='{$myid}Clean'>×</div>\r\n";
        }

        echo "{$space}</div>\r\n";
    }
}
