<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

use app\modules\build\views\preview\ValueList_View;
use yangzie\YZE_View_Component;

class Select_View extends ValueList_View {
    use Vant_Popup,Html_Code_Helper, Alpine{
        Alpine::build_code as alpineBuildCode;
    }

    protected function build_ui_begin($iteratorName = null)
    {
        $myid = $this->myid();
        $inputDataName = $this->get_input_data_name();

        $space =  $this->indent();
        echo $space.'<div';
        echo $this->wrap_output('@click', $this->myid().'_menu_switch($el, true)');
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1);
        echo '<span';
        if ($iteratorName){
            echo $this->wrap_output('x-text', "alpinejs_checked_name(\$el, {$iteratorName}, '{$inputDataName}')");
        }else{
            echo $this->wrap_output('x-text', "alpinejs_checked_name(\$el, {$myid}_values(), '{$inputDataName}')");
        }
        echo '></span><i class="van-icon van-icon-arrow"></i>'.PHP_EOL;
        echo "{$space}</div>".PHP_EOL;

        echo $space.'<div x-show="'.$myid.'_open">'.PHP_EOL;
        echo $this->indent(1).'<div class="van-overlay" @click="' . $myid . '_menu_switch($el, false)" style="z-index: 2012;"></div>'.PHP_EOL;
        echo $this->indent(1).'<div class="van-popup van-popup--round van-popup--bottom van-popup--safe-area-inset-bottom van-action-sheet" style="z-index: 2013;">'.PHP_EOL;
        echo $this->indent(2).'<div class="van-action-sheet__content">'.PHP_EOL;
    }

    protected function build_ui_end()
    {
        $space =  $this->indent();
        echo $this->indent(2).'</div>'.PHP_EOL;
        echo $this->indent(1).'</div>'.PHP_EOL;
        echo $space.'</div>'.PHP_EOL;
    }

    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex=null, $iteratorName='')
    {
        $myid = $this->myid();
        list('name'=>$xText, 'value'=>$xValue, 'checked'=>$checked, 'data'=>$boundData) = $this->get_bind_name_value($outputData, $itemName);
        $idxData = "idxOf{$itemName}";
        if (!$outputData){
            $staticValue = strlen($staticData['value'])?$staticData['value']:$staticData['name'];
            $xText = "'{$staticData['name']}'";
            $xValue = "'{$staticValue}'";
            $idxData = $staticDataIndex;
        }

        $inputDataName = $this->get_input_data_name($isArr);
        $inputDataNameString = $isArr ? "alpinejs_get_value(\$el, '{$inputDataName}')" : $inputDataName;

        echo $this->indent(3).'<button type="button"';
        echo $this->wrap_output('@click', $this->myid().'_item_click($el, '.$idxData.')');
        echo $this->wrap_output('class', 'van-action-sheet__item');
        echo $this->wrap_output(':data-value', $xValue);
        if ($outputData){
            echo $this->wrap_output('data-bound', $boundData);
            echo $this->wrap_output(':data-default', $checked ? "{$checked} ? {$xValue} : ''" : null);
        }else{
            echo $this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null);
        }
        echo $this->wrap_output('data-root', $myid);

        echo '><span class="van-action-sheet__name" x-text="'.$xText.'"></span></button>'.PHP_EOL;
    }

    public function build_code(): Base_Code_Fragment
    {
        $fragment = $this->alpineBuildCode();
        $inputDataName = $this->get_input_data_name($isArr);
        $myid = $this->myid();
        $suffix = '';
        if ($isArr){
            $suffix = '[-1]';
        }
        $fragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, "{$myid}_open{$suffix}: false,");

        $openMenu = [];
        $openMenu[] = $this->myid()."_menu_switch(\$el, state){";
        $openMenu[] = $this->indent(1, true)."this.{$myid}_open{$suffix} = state";
        $openMenu[] = '},';
        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $openMenu);

        $itemClick = [];
        $itemClick[] = $this->myid().'_item_click($el, index){';
        $itemClick[] = $this->indent(1, true)."this.{$myid}_open = false";
        $itemClick[] = $this->indent(1, true)."this.{$inputDataName} = \$el.dataset.value";
        $itemClick[] = '},';
        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $itemClick);
        return $fragment;
    }
    protected function css_map() {
        $map = parent::css_map();
        $styleMap = parent::style_map();
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

        if($styleMap['color']) unset($map['foregroundTheme']);
        if($styleMap['background-color']) unset($map['backgroundTheme']);
        $map['-'] = join(' ', $css);
        return $map;
    }
}
