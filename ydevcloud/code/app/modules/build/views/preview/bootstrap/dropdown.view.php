<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Dropdown_View extends ValueList_View {

    private function dropdownMeta () {
        $parentUI = $this->get_parent_UI();
        $type = strtolower($parentUI['type']);
        $parentIsButtonGroup = $type == 'buttongroup';
        $parentIsNavbar = in_array($type, ['nav', 'navbar']);
        if ($parentIsButtonGroup || $parentIsNavbar) {
            return $parentUI['meta'];
        }
        return $this->data['meta'];
    }

    /**
     * 背景主题
     * @return mixed
     */
    private function theme() {
        // 如果自己有背景和前景则用自己的，否则用上层的，如buttongroup
        $cssMap = parent::css_map();
        $dropdownMeta = $this->dropdownMeta();
        $myTheme = $cssMap['backgroundTheme'] ? $this->data['meta']['css']['backgroundTheme'] : '';
        $myTheme = $myTheme ?: $dropdownMeta['css']['backgroundTheme'];
        return $myTheme === 'default' ? '' : $myTheme;
    }
    /**
     * 前景主题
     * @return mixed
     */
    private function forceTheme() {
        // 如果自己有背景和前景则用自己的，否则用上层的，如buttongroup
        $cssMap = parent::css_map();
        $dropdownMeta = $this->dropdownMeta();
        $myTheme = $cssMap['foregroundTheme'] ? $this->data['meta']['css']['foregroundTheme'] : '';
        $myTheme = $myTheme ?: $dropdownMeta['css']['foregroundTheme'];
        return $myTheme === 'default' ? '' : $myTheme;
    }

    private function sizing() {
        $parentUI = $this->get_parent_UI();
        $parentIsButtonGroup = strtolower($parentUI['type']) == 'buttongroup';
        $buttonMeta = $parentIsButtonGroup ? $parentUI['meta'] : $this->data['meta'];
        if ($parentIsButtonGroup) {
            return $this->cssTranslate['buttonSizing'][$buttonMeta['css']['buttonSizing']];
        }
        return $this->cssTranslate['dropdownSizing'][$buttonMeta['css']['dropdownSizing']];
    }
    private function splitBtnCss() {
        $parentUI = $this->get_parent_UI();
        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['navbar','nav']);
        if ($parentIsNavbar) return 'nav-link';
        $arr = ['btn'];
        $buttonMeta = $this->dropdownMeta();
        $isOutline = @$buttonMeta['custom']['isOutline'] ? 'outline-' :'';
        $forceTheme = $this->forceTheme();
        $backTheme = $this->theme();
        $arr[] = $backTheme ? 'btn-' . $isOutline . $backTheme : 'btn-' . $isOutline . 'primary';
        $css = $this->sizing();
        if ($css) {
            $arr[] = $css;
        }
        if ($forceTheme) {
            $arr[] = $this->cssTranslate['foregroundTheme'][$forceTheme];
        }
        return $arr ? join(' ', $arr) : '';
    }
    private function btnCss() {
        $arr = [];
        $parentUI = $this->get_parent_UI();
        $cssMap = parent::css_map();
        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['navbar','nav']);
        $forceTheme = $this->forceTheme();
        $backTheme = $this->theme();
        if ($parentIsNavbar) {
            if ($forceTheme) $arr[] = $this->cssTranslate['foregroundTheme'][$forceTheme];
            if ($backTheme) $arr[] = $this->cssTranslate['backgroundTheme'][$forceTheme];
            $arr[] = 'nav-link transparent';
            return join(' ', $arr);
        }

        $buttonMeta = $this->dropdownMeta();
        $arr = ['btn btn-block'];
        unset($cssMap['backgroundTheme'], $cssMap['foregroundTheme']);

        $isOutline = @$buttonMeta['custom']['isOutline'] ? 'outline-' :'';

        $arr[] = $backTheme ? 'btn-' . $isOutline . $backTheme : 'btn-' . $isOutline . 'primary';
        $css = $this->sizing();
        if ($css) {
            $arr[] = $css;
        }
        if ($forceTheme) {
            $arr[] = $this->cssTranslate['foregroundTheme'][$forceTheme];
        }
        return $arr ? join(' ', $arr) : '';
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta);
        unset($styleArray['color'], $styleArray['background-color']);
        return $styleArray;
    }
    private function btyStyle () {
        $styleArray = parent::style_map();
        // 如果自己有背景和前景则用自己的，否则用上层的，如buttongroup
        $dropdownMeta = $this->dropdownMeta();
        $color = $this->data['meta']['style']['color'] ?: $dropdownMeta['style']['color'];
        $backgroundColor = $this->data['meta']['style']['background-color'] ?: $dropdownMeta['style']['background-color'];
        $selfHasForeground = $this->data['meta']['css']['foregroundTheme'] && $this->data['meta']['css']['foregroundTheme'] !== 'default';
        $selfHasBackground = $this->data['meta']['css']['backgroundTheme'] && $this->data['meta']['css']['backgroundTheme'] !== 'default';
        if (!$selfHasForeground && $color){
            $styleArray['color'] = "color: ${color} !important";
        }
        if (!$selfHasBackground && $backgroundColor){
            $styleArray['background-color'] = "background-color: ${backgroundColor} !important";
            $styleArray['border-color'] = "border-color: ${backgroundColor} !important";
        }
        return $styleArray ? join(';', array_values($styleArray)) : NULL;

    }

    protected function css_map()
    {
        $parentUI = $this->get_parent_UI();
        $parentIsButtonGroup = strtolower($parentUI['type']) == 'buttongroup';
        $buttonMeta = $parentIsButtonGroup ? $parentUI['meta'] : $this->data['meta'];

        $cssArray = parent::css_map();
        unset($cssArray['dropdownSizing'], $cssArray['backgroundTheme'], $cssArray['foregroundTheme']);
        $arr = [];
        $arr[] = @$this->data['meta']['custom']['direction'] ?: 'dropdown';

        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['navbar','nav']);

        if ($parentIsNavbar){
            $arr[] = 'nav-item';
        }

        if ($parentIsButtonGroup || @$buttonMeta['custom']['isSplit']){
            $arr[] = 'btn-group';
        }

        $cssArray['-'] = join(' ', $arr);
        return $cssArray;
    }

    private function build_begin($outDataName, $is2D=false){
        $inputData = $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_temp';
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs(false);
        if ($inputDataName) {
            if ($is2D){
                echo $this->wrap_output('x-input', $this->is_array($inputData) ? "{$inputDataName}[idxOf{$outDataName}]" : $inputDataName);
            }else{
                echo $this->wrap_output('x-input', $inputDataName);
            }
        }
        echo ">".PHP_EOL;

        if (@$this->data['meta']['custom']['isSplit']){
            echo $this->indent(1) . '<a class="'.$this->btnCss().'" ';
            echo $this->wrap_output('style', $this->btyStyle());
            $this->build_data_output_bind();
            echo ' id="'.$this->myId(true).'MenuLink" href="javascript:;">';
            $this->wrap_icon(function() use($inputDataName){
                echo "<span";
                if ($inputDataName) {
                    echo $this->wrap_output('x-text', $inputDataName."?.text || '".($this->data['meta']['title'] ?: $this->data['type'])."'");
                }
                echo ">".($this->data['meta']['title'] ?: $this->data['type'])."</span>";

            }, $this->build->get_indent() + 2);
            echo PHP_EOL;
            echo $this->indent(1);
            echo "</a>".PHP_EOL;
            echo $this->indent(1) . '<button class="dropdown-toggle dropdown-toggle-split '.$this->splitBtnCss().'" role="button" ';
            echo $this->wrap_output('style', $this->btyStyle());
            echo ' data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
            echo "</button>".PHP_EOL;
        }else{
            echo $this->indent(1) . '<a class="dropdown-toggle '.$this->btnCss().'"';
            echo ' role="button" id="'.$this->myId(true).'MenuLink" ';
            echo $this->wrap_output('style', $this->btyStyle());
            $this->build_data_output_bind();
            echo ' data-toggle="dropdown" aria-haspopup="true" href="javascript:;" aria-expanded="false">';
            $this->wrap_icon(function() use($inputDataName){
                echo "<span";
                if ($inputDataName) {
                    echo $this->wrap_output('x-text', $inputDataName."?.text || '".($this->data['meta']['title'] ?: $this->data['type'])."'");
                }
                echo ">".($this->data['meta']['title'] ?: $this->data['type'])."</span>";
            }, 2);
            echo PHP_EOL;
            echo $this->indent(1);
            echo "</a>".PHP_EOL;
        }

        echo $this->indent(1) . '<div class="dropdown-menu';
        echo @$this->data['meta']['custom']['menuAlign']=='right' ? ' dropdown-menu-right': '';
        echo '"';
        $this->build_event_listen();
        echo ' aria-labelledby="'.$this->myId(true).'MenuLink">'.PHP_EOL;

    }
    private function build_end(){
        $space =  $this->indent();
        echo $this->indent(1) . "</div>".PHP_EOL;
        echo "{$space}</div>".PHP_EOL;
    }
    protected function build_ui_static()
    {
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '#', 'type'=>'action' ], [ "text"=> 'Sample 2', "value"=> '#', 'type'=>'action'  ]];
        $this->build_begin(null);

        foreach ((array)@$values as $item){
            echo $this->indent(2);
            if (@$item['type']=='action'){
                echo "<a href='javascript:;' data-value='{$item['value']}' class='dropdown-item ".(@$item['checked'] ? 'active' : '')."'>{$item['text']}</a>";
            }elseif (@$item['type']=='header'){
                echo "<h6 class='dropdown-header'>{$item['text']}</h6>";
            }elseif (@$item['type']=='divider'){
                echo '<div class="dropdown-divider"></div>';
            }else{
                echo '<div class="pl-4 pr-4 text-muted"><p>'.$item['text'].'</p></div>';
            }
            echo PHP_EOL;
        }
        $this->build_end();
    }

    protected function build_ui_2d_array($bindOutput, $outDataName)
    {
        $this->build_dropdown_item($bindOutput, $outDataName, true);
    }

    protected function build_ui_array($bindOutput, $outDataName)
    {
        $this->build_dropdown_item($bindOutput, $outDataName, false);
    }

    private function build_item($bindOutput, $itemName, $outDataName){
        list('name'=>$name, 'value'=>$value) = $this->get_bind_name_value($bindOutput, $itemName);
        echo $this->indent(2).'<a href="javascript:void(0)"';
        echo $this->wrap_output(':data-bound', "'{$outDataName}[\''+idxOf{$itemName}+'\']'");
        echo $this->wrap_output(':data-value', $value);
        echo $this->wrap_output('class', "dropdown-item");
        echo $this->wrap_output('x-text', $name);
        echo "></a>";
        echo PHP_EOL;
    }

    private function build_dropdown_item($bindOutput, $outDataName, $is2D){
        $this->build_begin($outDataName, $is2D);

        $itemName = $bindOutput['name'];
        if ($is2D) $itemName .= '2';

        echo $this->indent(2).'<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '
            .($is2D?"itemOf":"").$outDataName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;

        if ($is2D){
            $this->build_item($bindOutput['item'], $itemName, "itemOf{$outDataName}");
        }else{
            $this->build_item($bindOutput, $itemName, $outDataName);
        }

        echo $this->indent(2).'</template>'.PHP_EOL;
        $this->build_end();
    }

    function build_code(): Base_Code_Fragment
    {
        parent::build_code();
        $codeFragment = $this->get_code_Fragment();
        $inputData = $this->get_input_data($dataName);
        // 如果没有数据绑定的话，定义一个临时数据
        if (!$dataName) {
            $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid() . '_temp: "",');
        }
        return $codeFragment;
    }
}
