<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class List_View extends Preview_View {
    use Vant_Popup,Html_Code_Helper,Alpine{
        Alpine::build_code as alpineBuildCode;
    }
    private function list_theme($item) {
        $cssMap = parent::css_map();
        $css = [];
        if (@$cssMap['backgroundTheme']) $css[] = $cssMap['backgroundTheme'];
        if (@$cssMap['foregroundTheme']) $css[] = $cssMap['foregroundTheme'];
        return join(' ', $css);
    }
    private function list_style($item) {
        $styleMap = parent::style_map();
        $style = [];
        if (@$styleMap['color']) {
            $style[] = $styleMap['color']. ' !important';
        }
        if (@$styleMap['background-color']) {
            $style[] = $styleMap['background-color'];
        }
        return join(';', $style);
    }
    protected function css_map()
    {
        $arrMap = parent::css_map();
        unset($arrMap['backgroundTheme'], $arrMap['foregroundTheme']);
        $arr = [];
        $arr[] = 'van-list';
        $arrMap['-'] = join(' ', $arr);
        return $arrMap;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta, $state);
        unset($styleArray['color'], $styleArray['background-color']);
        return $styleArray;
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
        $fragment = parent::build_code();

        $this->get_input_data($inputDataName);
        if (!$inputDataName){
            $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid().'_value: "'.$this->default_value().'",');
            $inputDataName = $this->myid().'_value';
        }

        $click = [];
        $click[] = $this->myid().'_click($el){';
        $click[] = $this->indent(1, true).'this.'.$inputDataName.' = $el.innerText';
        $click[] = '},';
        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $click);
        return $fragment;
    }

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "name"=> 'Sample 1', "value"=> '1' ], [ "name"=> 'Sample 2', "value"=> '2' ]];
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        foreach ((array)@$values as $item){
            echo $this->indent(1) . '<div @click="'.$this->myid().'_click($el)"';
            echo $this->wrap_output('class', "van-cell van-cell--clickable van-align-items-center ".$this->list_theme($item));
            echo $this->wrap_output('style', $this->list_style($item));
            echo ">".PHP_EOL;

            echo $this->indent(2) . "<div";
            echo $this->wrap_output('class', "van-cell__value van-cell__value--alone ".$this->list_theme($item));
            echo $this->wrap_output('style', $this->list_style($item));
            echo ">".PHP_EOL;
            echo $this->indent(3) . @$item['text']."".PHP_EOL;
            echo $this->indent(2) . "</div>".PHP_EOL;

            if (@$item['checked']){
                echo $this->indent(2);
                echo '<i';
                echo $this->wrap_output("class",'van-badge__wrapper van-icon van-icon-success van-text-success '.$this->list_theme($item));
                echo $this->wrap_output("style",'font-size: 1.5rem;'.$this->list_style($item));
                echo '></i>';
            }
            echo $this->indent(1) . "</div>".PHP_EOL;
        }

        echo "{$space}</div>".PHP_EOL;
    }
}
