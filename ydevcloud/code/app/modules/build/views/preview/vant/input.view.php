<?php
namespace app\modules\build\views\preview\vant;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use yangzie\YZE_View_Component;


class Input_View extends Preview_View {
    use  Vant_Popup,Html_Code_Helper;
    protected function body_css() {
        $css = ['form-control d-flex justify-content-between align-items-center'];
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing']!='normal'){
            $css[] = 'form-control-'.$this->data['meta']['css']['formSizing'];
        }
        if (@$this->data['meta']['form']['state']=='disabled'){
            $css[] = 'disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $css[] = 'readonly';
        }
        return join(' ', $css);
    }
    protected function body_style() {
        $styleMap = parent::style_map();
        $newStyle = [];
        foreach ($styleMap as $key => $value) {
            if (preg_match("/height/", $key)) {
                $newStyle[$key] = $value;
            }
        }
        $newStyle = array_values($newStyle);
        return join(';', $newStyle);
    }
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }

    function build_code(): Base_Code_Fragment
    {
        parent::build_code();
        ob_start();
?>
document.querySelector('#<?= $this->myId(true)?> input').addEventListener('keyup', function (event) {
<?php if (@$this->data['meta']['custom']['wordCountVisible']){?>
    document.querySelector('#<?= $this->myId(true)?> .word-count').innerText = event?.target?.value.length
<?php }?>
    if (event?.target?.value.length>0){
        document.querySelector('#<?= $this->myId(true)?> .cursor').classList.remove('d-none')
    }else{
        document.querySelector('#<?= $this->myId(true)?> .cursor').classList.add('d-none')
    }
})
<?php
    if (@$this->data['meta']['custom']['clearButtonVisible']){
?>
document.querySelector('#<?= $this->myId(true)?> .cursor').addEventListener('click', function (event) {
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
        $space =  $this->indent(2);
        echo "{$space}<div";
        echo $this->wrap_output('class', $this->body_css());
        echo $this->wrap_output('style', $this->body_style());

        echo ">\r\n";
        $this->indent(3);
        $this->wrap_icon(function(){
            echo '<input type="';
            echo @$this->data['meta']['custom']['inputType'] ?: 'text';
            echo '" class="w-100 border-0" ';
            if (@$this->data['meta']['custom']['autocomplete']){
                echo " autocomplete='".$this->data['meta']['custom']['autocomplete']."'";
            }
            echo $this->build_form_attrs();
            if (@$this->data['meta']['custom']['maxLength']){
                echo ' maxlength='.$this->data['meta']['custom']['maxLength'];
            }
            echo " value='".@$this->data['meta']['value']."'>\r\n";
        },3);

        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(3) . "<div class='ml-3'><span class='word-count'>0</span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>\r\n";
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(3) . "<div class='cursor ml-3 d-none'>×</div>\r\n";
        }

        echo "{$space}</div>\r\n";
    }
}
