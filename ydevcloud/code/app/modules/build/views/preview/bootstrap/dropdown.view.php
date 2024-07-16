<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\ValueList_View;

/**
 * <pre>
 * 单体按钮：
 * <div class="btn-group">
 *  <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
 *  Action
 *  </button>
 *  <div class="dropdown-menu">
 *      <a class="dropdown-item" href="#">Action</a>
 *      <a class="dropdown-item" href="#">Another action</a>
 *      <a class="dropdown-item" href="#">Something else here</a>
 *      <div class="dropdown-divider"></div>
 *      <a class="dropdown-item" href="#">Separated link</a>
 *  </div>
 * </div>
 * </pre>
 *
 * 分离按钮：
 * <pre>
 * <div class="btn-group">
 *  <button type="button" class="btn btn-danger">Action</button>
 *  <button type="button" class="btn btn-danger dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-expanded="false">
 *  <span class="sr-only">Toggle Dropdown</span>
 * </button>
 * <div class="dropdown-menu">
 *  <a class="dropdown-item" href="#">Action</a>
 *  <a class="dropdown-item" href="#">Another action</a>
 *  <a class="dropdown-item" href="#">Something else here</a>
 *  <div class="dropdown-divider"></div>
 *  <a class="dropdown-item" href="#">Separated link</a>
 *  </div>
 * </div>
 * </pre>
 */
class Dropdown_View extends ValueList_View {
    use Bootstrap_Popup,Html_Code_Helper;

    protected function build_valuelist($outputData, $itemName, $staticData=null, $staticDataIndex=null, $iteratorName='')
    {
        echo $this->indent(2);
        $myid = $this->myid();
        $staticValue = $staticData['value']?:$staticData['name'];
        // 动态数据
        if($outputData){
            list('name'=>$name, 'value'=>$value, "checked"=>$checked) = $this->get_bind_name_value($outputData, $itemName);
            echo '<a href="javascript:void(0)"';
            echo $this->wrap_output('data-root', $myid);
            echo $this->wrap_output(':data-value', $value);
            echo $this->wrap_output(':data-default', $checked ? "{$checked} ? {$value} : ''" : null);
            echo $this->wrap_output(':class', $this->itemCss($value));
            echo $this->wrap_output('x-text', $name);
            echo "></a>";
            echo PHP_EOL;
            return;
        }
        // 静态数据
        if (@$staticData['type']=='text'){
            echo '<div class="pl-4 pr-4 text-muted"><p>'.$staticData['name'].'</p></div>';
        }elseif (@$staticData['type']=='header'){
            echo "<h6 class='dropdown-header'>{$staticData['name']}</h6>";
        }elseif (@$staticData['type']=='divider'){
            echo '<div class="dropdown-divider"></div>';
        }else{
            echo "<a href='javascript:;'";
            echo $this->wrap_output('data-root', $myid);
            echo $this->wrap_output("data-value", $staticValue);
            echo $this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null);
            echo $this->wrap_output(':class', $this->itemCss("'{$staticValue}'"));
            echo ">{$staticData['name']}</a>";
        }
        echo PHP_EOL;
    }

    protected function build_ui_begin($iteratorName=null){
        $inputDataName = $this->get_input_data_name();
        $space =  $this->indent();
        $myid = $this->myid();
        $isSplit = $this->data['meta']['custom']['isSplit'];
        echo "{$space}<div";
        echo $this->build_main_attrs(false);
        echo ">".PHP_EOL;

        if ($isSplit){
            echo $this->indent(1) . '<button';
            echo $this->wrap_output('class', $this->btnCss());
            echo $this->wrap_output('style', $this->btyStyle());
            echo $this->wrap_output('type', 'button');
            $this->build_event_listen();
            echo '>';
            $this->wrap_icon(function(){
                $text = ($this->data['meta']['title'] ?: $this->data['type']);
                echo "<span>{$text}</span>";

            }, 2);
            echo PHP_EOL;
            echo $this->indent(1) . "</button>".PHP_EOL;
            echo $this->indent(1) . '<button role="button"';
            echo $this->wrap_output('class', $this->splitBtnCss());
            echo $this->wrap_output('style', $this->btyStyle());
            echo ' data-toggle="dropdown" aria-expanded="false">';
            echo "</button>".PHP_EOL;
        }else{
            echo $this->indent(1) . '<button role="button" type="button" data-toggle="dropdown" aria-expanded="false"';
            echo $this->wrap_output('class', 'dropdown-toggle '.$this->btnCss());
            echo $this->wrap_output('style', $this->btyStyle());
            echo '>';
            $this->wrap_icon(function() use($inputDataName, $iteratorName, $myid){
                $text = ($this->data['meta']['title'] ?: $this->data['type']);
                echo "<span";
                if ($iteratorName) {
                    echo $this->wrap_output('x-text', "alpinejs_checked_name(\$el, {$iteratorName}, '{$inputDataName}') || '{$text}'");
                }else{
                    echo $this->wrap_output('x-text', "alpinejs_checked_name(\$el, {$myid}_values(), '{$inputDataName}') || '{$text}'");
                }
                echo ">{$text}</span>";
            }, 2);
            echo PHP_EOL;
            echo $this->indent(1)."</button>".PHP_EOL;
        }

        echo $this->indent(1) . '<div class="dropdown-menu';
        echo @$this->data['meta']['custom']['menuAlign']=='right' ? ' dropdown-menu-right': '';
        echo '"';
        $this->build_event_listen();
        echo '>'.PHP_EOL;

    }
    protected function build_ui_end(){
        $space =  $this->indent();
        echo $this->indent(1) . "</div>".PHP_EOL;
        echo "{$space}</div>".PHP_EOL;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta, $state);
        unset($styleArray['color'], $styleArray['background-color']);
        return $styleArray;
    }
    protected function css_map()
    {
        $parentUI = $this->get_parent_UI();
        $buttonMeta = $this->data['meta'];

        $cssArray = parent::css_map();
        unset($cssArray['dropdownSizing'], $cssArray['backgroundTheme'], $cssArray['foregroundTheme']);
        $arr = [];
        $arr[] = @$this->data['meta']['custom']['direction'] ?: 'dropdown';

        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['nav']);

        if ($parentIsNavbar){
            $arr[] = 'nav-item';
        }

        if (@$buttonMeta['custom']['isSplit']){
            $arr[] = 'btn-group';
        }

        $cssArray['-'] = join(' ', $arr);
        return $cssArray;
    }

    private function dropdownMeta () {
        $parentUI = $this->get_parent_UI();
        $type = strtolower($parentUI['type']);
        $parentIsNavbar = in_array($type, ['nav']);
        if ($parentIsNavbar) {
            return $parentUI['meta'];
        }
        return $this->data['meta'];
    }
    private function itemCss($value){
        $inputDataName = $this->get_input_data_name();
        return "{'dropdown-item': true, 'bg-light': {$inputDataName} == $value}";
    }
    /**
     * 背景主题,如果自己有背景和前景则用自己的，否则用上层的
     * @return mixed
     */
    private function backgroundTheme() {
        $dropdownMeta = $this->dropdownMeta();
        $myTheme = $this->data['meta']['css']['backgroundTheme'] ?: $dropdownMeta['css']['backgroundTheme'];
        return $myTheme === 'default' ? '' : $myTheme;
    }
    /**
     * 前景主题,如果自己有背景和前景则用自己的，否则用上层的
     * @return mixed
     */
    private function foregroundTheme() {
        $dropdownMeta = $this->dropdownMeta();
        $myTheme = $this->data['meta']['css']['foregroundTheme'] ?: $dropdownMeta['css']['foregroundTheme'];
        return $myTheme === 'default' ? '' : $myTheme;
    }

    private function sizing() {
        $buttonMeta = $this->data['meta'];
        return $this->cssTranslate['dropdownSizing'][$buttonMeta['css']['dropdownSizing']];
    }

    /**
     * 分体式右侧箭头按钮
     * @return string
     */
    private function splitBtnCss() {
        $parentUI = $this->get_parent_UI();
        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['nav']);
        if ($parentIsNavbar) return 'dropdown-toggle dropdown-toggle-split  nav-link';
        $arr = ['btn dropdown-toggle dropdown-toggle-split '];
        $buttonMeta = $this->data['meta'];
        $isOutline = @$buttonMeta['custom']['isOutline'] ? 'outline-' :'';
        $forceTheme = $this->foregroundTheme();
        $backTheme = $this->backgroundTheme();
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

    /**
     * 一体式按钮或者分体式左侧按钮
     * @return string
     */
    private function btnCss() {
        $arr = [];
        $parentUI = $this->get_parent_UI();
        $cssMap = parent::css_map();
        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['nav']);
        $forceTheme = $this->foregroundTheme();
        $backTheme = $this->backgroundTheme();
        if ($parentIsNavbar) {
            if ($forceTheme) $arr[] = $this->cssTranslate['foregroundTheme'][$forceTheme];
            if ($backTheme) $arr[] = $this->cssTranslate['backgroundTheme'][$forceTheme];
            $arr[] = 'nav-link transparent';
            return join(' ', $arr);
        }

        $buttonMeta = $this->data['meta'];
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

    /**
     * 整体按钮样式
     * @return string|null
     */
    private function btyStyle () {
        $styleArray = parent::style_map();
        // 如果自己有背景和前景则用自己的，否则用上层的
        $dropdownMeta = $this->dropdownMeta();
        $color = $this->data['meta']['style']['color'] ?: $dropdownMeta['style']['color'];
        $backgroundColor = $this->data['meta']['style']['background-color'] ?: $dropdownMeta['style']['background-color'];
        $selfHasForeground = $this->foregroundTheme();
        $selfHasBackground = $this->backgroundTheme();
        if (!$selfHasForeground && $color){
            $styleArray['color'] = "color: ${color} !important";
        }
        if (!$selfHasBackground && $backgroundColor){
            $styleArray['background-color'] = "background-color: ${backgroundColor} !important";
            $styleArray['border-color'] = "border-color: ${backgroundColor} !important";
        }
        return $styleArray ? join(';', array_values($styleArray)) : NULL;

    }

}
