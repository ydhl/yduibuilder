<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use yangzie\YZE_View_Component;
use app\modules\build\views\preview\Html_Event_Binding;

class Input_View extends Preview_View {
    use Html_Event_Binding, Bootstrap_Popup,Html_Code_Helper;

    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $style['[data-uiid='.$this->myid().$this->data['type'].']'] = 'font-style: inherit !important';
        return $style;
    }

    protected function css_map() {
        $css = parent::css_map();
        $css[] = 'form-control justify-content-between align-items-center';
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing']!='normal'){
            $css[] = 'form-control-'.$this->data['meta']['css']['formSizing'];
        }
        if (@$this->data['meta']['custom']['borderless']){
            $css[] = 'border-0';
        }

        if (@$this->data['meta']['form']['state']=='disabled'){
            $css[] = 'disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $css[] = 'readonly';
        }
        if (@$this->data['meta']['form']['state']=='hidden'){
            $css[] = 'd-none';
        }else{
            $css[] = 'd-flex';
        }
        return $css;
    }

    function build_code(): Base_Code_Fragment
    {
        parent::build_code();
        ob_start();
        $codeLines = [];
        $build = $this->get_build();
        if ($this->data['meta']['custom']['wordCountVisible'] || $this->data['meta']['custom']['clearButtonVisible']){
            $codeLines = array_merge($codeLines, $build->indent_code(0, 'document.querySelector("#'.$this->myId(true).' input").addEventListener("keyup", function (event) {'));

            if (@$this->data['meta']['custom']['wordCountVisible']){
                $codeLines = array_merge($codeLines, $build->indent_code(1, 'document.querySelector("#'.$this->myId(true).' .word-count").innerText = event?.target?.value.length;'));
            }
            if (@$this->data['meta']['custom']['clearButtonVisible']){
                $codeLines = array_merge($codeLines, $build->indent_code(1, 'if (event?.target?.value.length>0){'));
                $codeLines = array_merge($codeLines, $build->indent_code(2, 'document.querySelector("#'.$this->myId(true).' .cursor").classList.remove("d-none");'));
                $codeLines = array_merge($codeLines, $build->indent_code(1, '}else{'));
                $codeLines = array_merge($codeLines, $build->indent_code(2, 'document.querySelector("#'.$this->myId(true).' .cursor").classList.add("d-none");'));
                $codeLines = array_merge($codeLines, $build->indent_code(1, '}'));
            }
            $codeLines = array_merge($codeLines, $build->indent_code(0, '})'));
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            $codeLines = array_merge($codeLines, $build->indent_code(0, 'document.querySelector("#'.$this->myId(true).' .cursor").addEventListener("click", function (event) {'));
            $codeLines = array_merge($codeLines, $build->indent_code(1, 'document.querySelector("#'.$this->myId(true).' input").value = "";'));
            $codeLines = array_merge($codeLines, $build->indent_code(1, 'document.querySelector("#'.$this->myId(true).' .word-count").innerText = 0;'));
            $codeLines = array_merge($codeLines, $build->indent_code(0, '})'));
        }
        $this->get_code_Fragment()->add_code($codeLines);
        return $this->get_code_fragment();
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">";
        echo $this->indent(1);
        $this->wrap_icon(function(){
            echo '<input type="';
            echo @$this->data['meta']['custom']['inputType'] ?: 'text';
            echo '" class="w-100 border-0 bg-transparent" ';
            if (@$this->data['meta']['custom']['autocomplete']){
                echo " autocomplete='".$this->data['meta']['custom']['autocomplete']."'";
            }
            echo $this->build_form_attrs();
            if (@$this->data['meta']['custom']['maxLength']){
                echo ' maxlength='.$this->data['meta']['custom']['maxLength'];
            }
            echo " value='".@$this->data['meta']['value']."'>\r\n";
        },1);

        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(1) . "<div class='ml-3'><span class='word-count'>0</span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>\r\n";
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(1) . "<div class='cursor ml-3 d-none'>×</div>\r\n";
        }

        echo "{$space}</div>\r\n";
    }
}
