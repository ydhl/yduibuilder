<?php
namespace app\modules\build\views\preview\layui;


use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Preview_View;

class Textarea_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    public function check_master(){
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }

    private function body_css() {
        $arr = ['layui-textarea layui-d-flex layui-align-items-end'];
        if (@$this->data['meta']['form']['state']=='disabled'){
            $css[] = 'layui-disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $css[] = 'readonly';
        }
        return $arr ? join(' ', $arr) : '';
    }

    private function body_style(){
        $style = parent::style_map();
        $newStyle = [];
        foreach ($style as $name => $value){
            if (preg_match("/^height/", $name)){
                $newStyle[$name] = $value;
            }
        }
        return join(array_values($newStyle));
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
        document.querySelector('#<?= $this->myId(true)?> .layui-reset').classList.remove('d-none')
    }else{
        document.querySelector('#<?= $this->myId(true)?> .layui-reset').classList.add('d-none')
    }
})
<?php
        }
        if (@$this->data['meta']['custom']['clearButtonVisible']){
?>
document.querySelector('#<?= $this->myId(true)?> .layui-reset').addEventListener('click', function (event) {
    document.querySelector('#<?= $this->myId(true)?> textarea').value = ''
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

        $this->wrap_icon(function (){
            echo '<textarea';
            echo $this->wrap_output('class', "layui-input-custom");
            if (@$this->data['meta']['custom']['autoRow']){
                echo $this->wrap_output('style', 'resize: none');
            }
            echo $this->build_form_attrs();
            if (@$this->data['meta']['custom']['maxLength']){
                echo $this->wrap_output('length', $this->data['meta']['custom']['maxLength']);
            }
            echo $this->wrap_output('rows', @$this->data['meta']['custom']['row']);
            echo '>';
            echo @$this->data['meta']['value'];
            echo "</textarea>\r\n";
        },3);


        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(3);
            echo "<div class='layui-ml-3'><span class='word-count'>0</span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>\r\n";
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(3);
            echo "<button type='button' class='layui-btn layui-btn-primary layui-btn-xs layui-border-0 layui-reset d-none'><i class='layui-icon layui-icon-close'></i></button>\r\n";
        }

        echo "{$space}</div>\r\n";
    }
}
