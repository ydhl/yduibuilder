<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Valuable_View;

class File_View extends Preview_View implements Valuable_View{
    use  Vant_Popup,Html_Code_Helper;

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}";
        echo "<div";
        $this->output_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1).'<div class="van-uploader">'.PHP_EOL;
        echo $this->indent(2).'<div class="van-uploader__wrapper">'.PHP_EOL;
        echo $this->indent(3).'<div class="van-uploader__upload">'.PHP_EOL;
        echo $this->indent(4).'<i class="van-badge__wrapper van-icon van-icon-photograph van-uploader__upload-icon"></i>'.PHP_EOL;
        echo $this->indent(4).'<input type="file"';
        echo $this->wrap_output('accept', $this->data['meta']['custom']['accept']?:NULL);
        echo $this->wrap_output('class', 'van-uploader__input');
        echo $this->wrap_output('multiple', null, $this->data['meta']['custom']['multiple']?:null);
        $this->output_form_attrs();
        echo ">".PHP_EOL;
        echo $this->indent(3).'</div>'.PHP_EOL;
        echo $this->indent(2).'</div>'.PHP_EOL;
        echo $this->indent(1).'</div>'.PHP_EOL;

        echo "{$space}</div>".PHP_EOL;
    }
    protected function css_map()
    {
        $map = parent::css_map();
        $meta = $this->data['meta'];
        $css = ['van-align-items-center'];
        if ($meta['form']['state'] == 'disabled'){
            $css[] = 'van-disabled';
        }
        if ($meta['form']['state'] == 'readonly'){
            $css[] = 'van-readonly';
        }
        if (@$meta['form']['state'] == 'hidden'){
            $css[] = 'van-d-none';
        }else{
            $css[] = 'van-d-flex';
        }
        $map['-'] = join(' ', $css);
        return $map;
    }

}
