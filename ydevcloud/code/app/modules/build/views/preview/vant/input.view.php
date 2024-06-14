<?php
namespace app\modules\build\views\preview\vant;
use app\modules\build\views\code\Base_Code_Fragment;

use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use yangzie\YZE_View_Component;


class Input_View extends Preview_View {
    use  Vant_Popup,Html_Code_Helper;
    protected function css_map() {
        $map = parent::css_map();
        $css = ['van-justify-content-between van-align-items-center van-bg-transparent'];
        if (@$this->data['meta']['custom']['borderless']){
            $css[] = 'van-border-0';
        }
        if (@$this->data['meta']['form']['state']=='disabled'){
            $css[] = 'van-disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            $css[] = 'van-readonly';
        }
        if (@$this->data['meta']['form']['state'] == 'hidden'){
            $css[] = 'van-d-none';
        }else{
            $css[] = 'van-d-flex';
        }
        $map['-'] = join(' ', $css);
        return $map;
    }


    function build_code(): Base_Code_Fragment
    {
        parent::build_code();
        return $this->get_code_fragment();
    }

    public function build_ui()
    {
        $space =  $this->indent(2);
        echo "{$space}<div";
        echo $this->build_main_attrs();

        echo ">".PHP_EOL;
        $this->indent(3);
        $this->wrap_icon(function(){
            echo '<input';
            echo $this->wrap_output('type', @$this->data['meta']['custom']['inputType'] ?: 'text');
            echo $this->wrap_output('class', 'van-field__control');
            echo $this->wrap_output('autocomplete', $this->data['meta']['custom']['autocomplete']?:NULL);
            echo $this->wrap_output('maxlength', $this->data['meta']['custom']['maxLength']?:NULL);
            echo $this->wrap_output('value', $this->data['meta']['value']?:NULL);
            echo $this->build_form_attrs();
            echo ">".PHP_EOL;
        },3);

        if (@$this->data['meta']['custom']['wordCountVisible']){
            echo $this->indent(3) . "<div class='van-ml-3'><span>0</span>";
            if (@$this->data['meta']['custom']['maxLength']){
                echo "/".$this->data['meta']['custom']['maxLength'];
            }
            echo "</div>".PHP_EOL;
        }

        if (@$this->data['meta']['custom']['clearButtonVisible']){
            echo $this->indent(3) . '<div class="cursor van-ml-3 van-d-none"><i class="van-badge__wrapper van-icon van-icon-cross"></i></div>'.PHP_EOL;
        }

        echo "{$space}</div>".PHP_EOL;
    }
}
