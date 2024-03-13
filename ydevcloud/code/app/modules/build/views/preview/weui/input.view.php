<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use phpseclib3\Crypt\EC\BaseCurves\Base;

class Input_View extends Preview_View {
    use Weui_Event_Binding, Weui_Popup,Html_Code_Helper;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }

    function build_code():Base_Code_Fragment
    {
        parent::build_code();
        ob_start();
?>
document.querySelector('#<?= $this->myId(true)?> input').addEventListener('keyup', function (event) {
<?php
        if (@$this->data['meta']['custom']['wordCountVisible']){
?>
    document.querySelector('#<?= $this->myId(true)?> .word-count').innerText = event?.target?.value.length
<?php }
        if (@$this->data['meta']['custom']['clearButtonVisible']){
?>
    if (event?.target?.value.length>0){
        document.querySelector('#<?= $this->myId(true)?> .weui-btn_reset').classList.remove('d-none')
    }else{
        document.querySelector('#<?= $this->myId(true)?> .weui-btn_reset').classList.add('d-none')
    }
<?php }
?>
})
<?php
        if (@$this->data['meta']['custom']['clearButtonVisible']){
?>
document.querySelector('#<?= $this->myId(true)?> .weui-btn_reset').addEventListener('click', function (event) {
    document.querySelector('#<?= $this->myId(true)?> input').value = ''
    document.querySelector('#<?= $this->myId(true)?> .word-count').innerText = 0
})
<?php
        }
        $this->get_code_Fragment()->add_code(ob_get_clean());
        return $this->get_code_fragment();
    }

    public function build_ui()
    {
        $this->indent(4);
        $this->wrap_icon(function(){
            echo $this->indent(5).'<input type="';
            echo @$this->data['meta']['custom']['inputType'] ?: 'text';
            echo '" class="weui-input" ';
            if (@$this->data['meta']['custom']['autocomplete']){
                echo " autocomplete='".$this->data['meta']['custom']['autocomplete']."'";
            }
            if (@$this->data['meta']['custom']['maxLength']){
                echo " maxLength='".$this->data['meta']['custom']['maxLength']."'";
            }
            echo $this->build_form_attrs();
            echo " value='".@$this->data['meta']['value']."'>\r\n";
        });

        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(4) . "<div class='ml-3'><span class='word-count'>0</span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>\r\n";
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(4) . "<button class='weui-btn_reset weui-btn_icon d-none'><i class='weui-icon-clear'></i></button>\r\n";
        }
    }
}
