<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Input_View as Preview_Input_View;

class Input_View extends Preview_Input_View {
    use Wxmp;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }
    public function build_code():Base_Code_Fragment
    {
        ob_start();
        ?>
this.setData({
    <?=$this->myId()?>Value: e.detail.value
})
<?php
        $this->get_code_fragment()->add_function($this->myId()."Input(e)", ob_get_clean());
        $this->get_code_fragment()->add_data($this->myId().'Value', "{$this->data['meta']['value']}");
        if (@$this->data['meta']['custom']['clearButtonVisible']){
            ob_start();
?>
this.setData({
    <?=$this->myId()?>Value: ''
})
<?php
            $this->get_code_fragment()->add_function("clear".$this->myId()."Value(e)", ob_get_clean());
        }
        return $this->get_code_fragment();
    }

    public function build_ui()
    {
        $this->wrap_icon(function(){
            echo '<input type="';
            echo @$this->data['meta']['custom']['inputType'] ?: 'text';
            echo '" class="weui-input" ';
            echo 'bindinput="'.$this->myId().'Input"';
            echo $this->build_form_attrs();
            if (@$this->data['meta']['custom']['maxLength']){
                echo ' maxlength="'.$this->data['meta']['custom']['maxLength'].'"';
            }
            echo " value='{{".$this->myId()."Value}}'/>\r\n";
        }, 4);

        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(4) . "<view class='ml-3'><text class='word-count'>{{".$this->myId()."Value.length}}</text>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</view>\r\n";
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(4) . "<button wx:if='{{".$this->myId()."Value.length>0}}' class='weui-btn_reset weui-btn_icon flex-shrink-0' bindtap='clear".$this->myId()."Value'><view class='weui-icon-clear'></view></button>\r\n";
        }
    }
}
