<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\Preview_View;

class Rangeinput_View extends Preview_View {
    use Wxmp;
    public function check_master()
    {
        $this->master_view = new Formgroup_View($this->data, $this->build->get_controller(), $this->build);
        return true;
    }

    public function build_ui()
    {
        $space =  $this->indent(4);
        echo "\r\n{$space}";
        echo "<slider ";
        echo 'class="w-100" show-value="true" ';
        echo $this->build_form_attrs();

        $forceColor = '';
        if (@$this->data['meta']['custom']['theme'] && $this->data['meta']['custom']['theme'] != 'default'){
            $forceColor = $this->cssTranslate['themeColor'][$this->data['meta']['custom']['theme']];
        }
        if (@$this->data['meta']['custom']['color']){
            $forceColor = $this->data['meta']['custom']['color'];
        }
        if ($forceColor){
            echo ' activeColor="'.$forceColor.'"';
            echo ' block-color="'.$forceColor.'"';
        }
        if (@$this->data['meta']['custom']['backgroundColor']){
            echo ' backgroundColor="'.$this->data['meta']['custom']['backgroundColor'].'"';
        }

        echo ' min="'.(@$this->data['meta']['custom']['min']??1);
        echo '" max="'.(@$this->data['meta']['custom']['max']??100);
        echo '" step="'.(@$this->data['meta']['custom']['step']??1);
        echo '"';
        echo ' value="'.@$this->data['meta']['value'].'"';
        echo "/>\r\n";
    }
}
