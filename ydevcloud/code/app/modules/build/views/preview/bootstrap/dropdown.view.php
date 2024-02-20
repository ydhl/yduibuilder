<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;
class Dropdown_View extends Preview_View {
    use Html_Event_Binding, Bootstrap_Popup,Html_Code_Helper;
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
    protected function style_map()
    {
        $styleArray = parent::style_map();
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
        return $styleArray ? join(';', array_values($styleArray)) : '';

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

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '#', 'type'=>'action' ], [ "text"=> 'Sample 2', "value"=> '#', 'type'=>'action'  ]];
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        echo ">\r\n";

        if (@$this->data['meta']['custom']['isSplit']){
            echo $this->indent(1) . '<a class="'.$this->btnCss().'" ';
            echo $this->wrap_output('style', $this->btyStyle());
            echo ' id="'.$this->myId(true).'MenuLink" href="javascript:;">';
            $this->wrap_icon(function(){
                echo $this->data['meta']['title'] ?: $this->data['type'];
            }, $this->build->get_indent() + 2);
            echo "\r\n";
            echo $this->indent(1);
            echo "</a>\r\n";
            echo $this->indent(1) . '<button class="dropdown-toggle dropdown-toggle-split '.$this->splitBtnCss().'" role="button" ';
            echo $this->wrap_output('style', $this->btyStyle());
            echo ' data-toggle="dropdown" aria-haspopup="true" href="#" aria-expanded="false">';
            echo "</button>\r\n";
        }else{
            echo $this->indent(1) . '<a class="dropdown-toggle '.$this->btnCss().'"';
            echo ' role="button" id="'.$this->myId(true).'MenuLink" ';
            echo $this->wrap_output('style', $this->btyStyle());
            echo ' data-toggle="dropdown" aria-haspopup="true" href="#" aria-expanded="false">';
            $this->wrap_icon(function(){
                echo $this->data['meta']['title'] ?: $this->data['type'];
            }, $this->build->get_indent() + 2);
            echo "\r\n";
            echo $this->indent(1);
            echo "</a>\r\n";
        }

        echo $this->indent(1) . '<div class="dropdown-menu';
        echo @$this->data['meta']['custom']['menuAlign']=='right' ? ' dropdown-menu-right': '';
        echo '" aria-labelledby="'.$this->myId(true).'MenuLink">'."\r\n";
        foreach ((array)@$values as $item){
            echo $this->indent(2);
            if (@$item['type']=='action'){
                echo "<a href='{$item['value']}' class='dropdown-item ".(@$item['checked'] ? 'active' : '')."'>{$item['text']}</a>";
            }elseif (@$item['type']=='header'){
                echo "<h6 class='dropdown-header'>{$item['text']}</h6>";
            }elseif (@$item['type']=='divider'){
                echo '<div class="dropdown-divider"></div>';
            }else{
                echo "<p>{$item['text']}</p>";
            }
            echo "\r\n";
        }
        echo $this->indent(1) . "</div>\r\n";

        echo "{$space}</div>\r\n";
    }
}
