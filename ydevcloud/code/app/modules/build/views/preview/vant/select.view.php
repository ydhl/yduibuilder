<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

use yangzie\YZE_View_Component;

class Select_View extends Preview_View {
    use Vant_Popup,Html_Code_Helper;

    protected function css_map() {
        $map = parent::css_map();
        $meta = $this->data['meta'];
        $css = ['van-align-items-center van-justify-content-between'];
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
    private function default_value(){
        if (!$this->data['meta']['values']) return 'Sample 1';
        foreach ($this->data['meta']['values'] as $item){
            if ($item['checked']) return $item['text'];
        }
        return '';
    }
    public function build_ui()
    {
        $space =  $this->indent(2);
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(3);
        echo $this->default_value().'<i class="van-icon van-icon-arrow"></i>'.PHP_EOL;

        echo "{$space}</div>".PHP_EOL;
    }
}
