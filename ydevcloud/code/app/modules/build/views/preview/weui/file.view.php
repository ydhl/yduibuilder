<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\bootstrap\Bootstrap_Popup;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class File_View extends Preview_View {
    use Bootstrap_Popup,Html_Code_Helper,Alpine {
        Alpine::build_code as alpineBuildCode;
    }
    protected function css_map() {
        $css = parent::css_map();
        $css[] = 'd-flex align-items-center overflow-hidden';
        if (@$this->data['meta']['css']['formSizing'] && $this->data['meta']['css']['formSizing'] != 'normal'){
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
        $space =  $this->indent(4);
        echo "{$space}";
        echo "<div class='weui-uploader__bd'>".PHP_EOL;
        echo $this->indent(5);
        echo "<div class='weui-uploader__input-box'>".PHP_EOL;
        echo $this->indent(6);
        echo '<input type="file" class="weui-uploader__input"';
        echo $this->build_form_attrs();
        if (@$this->data['meta']['custom']['accept']){
            echo ' accept="'.$this->data['meta']['custom']['accept'].'"';
        }
        if (@$this->data['meta']['custom']['multiple']){
            echo ' multiple';
        }
        echo ">".PHP_EOL;
        echo $this->indent(5);
        echo "</div>".PHP_EOL;
        echo "{$space}";
        echo "</div>".PHP_EOL;
    }
}
