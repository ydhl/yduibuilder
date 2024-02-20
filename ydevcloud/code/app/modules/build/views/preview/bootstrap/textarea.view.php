<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Html_Event_Binding;
use app\modules\build\views\preview\Preview_View;

class Textarea_View extends Preview_View {
    use Html_Event_Binding, Bootstrap_Popup,Html_Code_Helper;

    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $style['[data-uiid='.$this->myid().$this->data['type'].']'] = 'font-style: inherit !important';
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
        $space =  $this->indent(2);
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";
        echo $this->indent(3);
        echo '<textarea class="w-100 border-0"';
        if (@$this->data['meta']['custom']['autoRow']){
            echo ' style="resize: none" ';
        }
        if (@$this->data['meta']['custom']['maxLength']){
            echo ' maxlength='.$this->data['meta']['custom']['maxLength'];
        }
        echo $this->build_form_attrs();
        echo ' rows="'.@$this->data['meta']['custom']['row'].'">';
        echo @$this->data['meta']['value'];
        echo "</textarea>\r\n";

        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(3);
            echo "<div class='ml-3'><span class='word-count'>0</span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>\r\n";
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(3);
            echo "<div class='cursor ml-3 d-none'>×</div>\r\n";
        }

        echo "{$space}</div>\r\n";
    }
    public function build_code():Base_Code_Fragment
    {
        parent::build_code();
        ob_start();
?>
document.querySelector('#<?= $this->myId(true)?> textarea').addEventListener('keyup', function (event) {
<?php  if (@$this->data['meta']['custom']['wordCountVisible']){?>
    document.querySelector('#<?= $this->myId(true)?> .word-count').innerText = event?.target?.value.length
<?php }
     if (@$this->data['meta']['custom']['clearButtonVisible']){
?>
    if (event?.target?.value.length>0){
        document.querySelector('#<?= $this->myId(true)?> .cursor').classList.remove('d-none')
    }else{
        document.querySelector('#<?= $this->myId(true)?> .cursor').classList.add('d-none')
    }
<?php }?>
})
<?php
        if (@$this->data['meta']['custom']['clearButtonVisible']){
?>
document.querySelector('#<?= $this->myId(true)?> .cursor').addEventListener('click', function (event) {
    document.querySelector('#<?= $this->myId(true)?> textarea').value = ''
    document.querySelector('#<?= $this->myId(true)?> .word-count').innerText = 0
})
<?php
        }
        $this->get_code_Fragment()->add_code(ob_get_clean());
        return $this->get_code_fragment();
    }
}
