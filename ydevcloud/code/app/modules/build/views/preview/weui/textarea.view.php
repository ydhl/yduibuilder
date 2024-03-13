<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Textarea_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup, Html_Code_Helper;
    public function check_master(){
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }

    public function build_ui()
    {
        echo $this->indent(4);
        echo '<textarea class="weui-textarea"';
        if (@$this->data['meta']['custom']['autoRow']){
            echo ' style="resize: none" ';
        }
        if (@$this->data['meta']['custom']['maxLength']){
            echo " maxLength='".$this->data['meta']['custom']['maxLength']."'";
        }
        echo $this->build_form_attrs();
        echo ' rows="'.@$this->data['meta']['custom']['row'].'">';
        echo @$this->data['meta']['value'];
        echo "</textarea>\r\n";

        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(4);
            echo "<div class='ml-3 weui-textarea-counter'><span class='word-count'>0</span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>\r\n";
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(4) . "<button class='weui-btn_reset weui-btn_icon'><i class='weui-icon-clear'></i></button>\r\n";
        }
    }
    public function build_code():Base_Code_Fragment
    {
        parent::build_code();
        ob_start();
        if (@$this->data['meta']['custom']['wordCountVisible']){
?>
document.querySelector('#<?= $this->myId(true)?> textarea').addEventListener('keyup', function (event) {
    document.querySelector('#<?= $this->myId(true)?> .word-count').innerText = event?.target?.value.length
    if (event?.target?.value.length>0){
        document.querySelector('#<?= $this->myId(true)?> .weui-btn_reset').classList.remove('d-none')
    }else{
        document.querySelector('#<?= $this->myId(true)?> .weui-btn_reset').classList.add('d-none')
    }
})
<?php
        }
        if (@$this->data['meta']['custom']['clearButtonVisible']){
?>
document.querySelector('#<?= $this->myId(true)?> .weui-btn_reset').addEventListener('click', function (event) {
    document.querySelector('#<?= $this->myId(true)?> textarea').value = ''
    document.querySelector('#<?= $this->myId(true)?> .word-count').innerText = 0
})
<?php
        }
        $this->get_code_Fragment()->add_code(ob_get_clean());
        return $this->get_code_fragment();
    }
}
