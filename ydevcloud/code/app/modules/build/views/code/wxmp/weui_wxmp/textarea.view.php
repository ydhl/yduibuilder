<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Textarea_View as Preview_Textarea_View;

class Textarea_View extends Preview_Textarea_View {
    use Wxmp;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }
    public function build_code():Base_Code_Fragment
    {
        parent::build_code();
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

    public function build_style($justSelf = true)
    {
        $style =  parent::build_style($justSelf);
        if (@$this->data['meta']['custom']['autoRow']){
            $style['[data-uiid=' . $this->myId().' textarea]'] = 'resize: none';
        }
        return $style;
    }

    public function build_ui()
    {
        echo "\r\n".$this->indent(4);
        echo '<textarea class="weui-textarea"';
        echo $this->build_form_attrs();
        echo ' bindinput="'.$this->myId().'Input"';
        if (@$this->data['meta']['custom']['maxLength']){
            echo ' maxlength="'.$this->data['meta']['custom']['maxLength'].'"';
        }
        echo ' rows="'.@$this->data['meta']['custom']['row'].'"';
        echo ' value="{{'.$this->myId().'Value}}"';
        echo '>';
        echo "</textarea>\r\n";

        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(4);
            echo "<view class='ml-3'><text class='word-count'>{{".$this->myId()."Value.length}}</text>";
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
