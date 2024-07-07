<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

use yangzie\YZE_View_Component;

class Select_View extends Preview_View {
    use Vant_Popup,Html_Code_Helper, Alpine{
        Alpine::build_code as alpineBuildCode;
    }

    protected function css_map() {
        $map = parent::css_map();
        $meta = $this->data['meta'];
        $css = ['van-cell van-cell--clickable  van-align-items-center van-justify-content-between'];
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
    public function build_code(): Base_Code_Fragment
    {
        $fragment = $this->alpineBuildCode();

        $this->get_input_data($inputDataName);
        if (!$inputDataName){
            $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid().'_value: "'.$this->default_value().'",');
            $inputDataName = $this->myid().'_value';
        }

        $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid().'_open: false,');

        $openMenu = [];
        $openMenu[] = 'open_'.$this->myid().'_menu($el){';
        $openMenu[] = $this->indent(1, true).'this.'.$this->myid().'_open = true';
        $openMenu[] = '},';
        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $openMenu);

        $itemClick = [];
        $itemClick[] = $this->myid().'_item_click($el, index){';
        $itemClick[] = $this->indent(1, true).'this.'.$this->myid().'_open = false';
        $itemClick[] = $this->indent(1, true).'this.'.$inputDataName.' = $el.innerText'; // TODO 通过index获取
        $itemClick[] = '},';
        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $itemClick);
        return $fragment;
    }

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "name"=> 'Sample 1', "value"=> '1' ], [ "name"=> 'Sample 2', "value"=> '2' ]];

        $this->get_input_data($inputDataName);
        if (!$inputDataName){
            $inputDataName = $this->myid().'_value';
        }

        $space =  $this->indent(2);
        echo $space.'<div';
        echo $this->wrap_output('@click', 'open_'.$this->myid().'_menu($el)');
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(3);
        echo '<span x-text="'.$inputDataName.'"></span><i class="van-icon van-icon-arrow"></i>'.PHP_EOL;

        echo "{$space}</div>".PHP_EOL;

        echo $space.'<div :style="'.$this->menuStyle().'">'.PHP_EOL;
        echo $this->indent(3).'<div class="van-overlay" @click="'.$this->myid().'_open=false" style="z-index: 2012;"></div>'.PHP_EOL;
        echo $this->indent(3).'<div class="van-popup van-popup--round van-popup--bottom van-popup--safe-area-inset-bottom van-action-sheet" style="z-index: 2013;">'.PHP_EOL;
        echo $this->indent(4).'<div class="van-action-sheet__content">'.PHP_EOL;

        foreach ((array)$values as $index => $item){
            echo $this->indent(5).'<button type="button" @click="'.$this->myid().'_item_click($el, '.$index.')" class="van-action-sheet__item"><span class="van-action-sheet__name">'.$item['text'].'</span></button>'.PHP_EOL;
        }

        echo $this->indent(4).'</div>'.PHP_EOL;
        echo $this->indent(3).'</div>'.PHP_EOL;
        echo $space.'</div>'.PHP_EOL;
    }
    private function menuStyle(){
        return $this->myid()."_open ? '' : 'display:none'";
    }
}
