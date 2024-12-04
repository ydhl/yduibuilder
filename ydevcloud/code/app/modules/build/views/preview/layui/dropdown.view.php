<?php
namespace app\modules\build\views\preview\layui;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\ValueList_View;

class Dropdown_View extends ValueList_View {
    use Layui_Popup,Layui_Code_Helper, Alpine{
        Alpine::build_code as alpineBuildCode;
    }

    private function dropdown_meta() {
        $parentUI = $this->get_parent_UI();
        $type = strtolower($parentUI['type']);
        $parentIsNavbar = in_array($type, ['nav']);
        if ($parentIsNavbar) {
            return $parentUI['meta'];
        }
        return $this->data['meta'];
    }

    /**
     * 背景
     * @return mixed
     */
    private function theme() {
        $dropdownMeta = $this->dropdown_meta();
        // 如果自己有背景和前景则用自己的，否则用上层的，如buttongroup
        $myBackgruondTheme = $this->data['meta']['css']['backgroundTheme'] !== 'default' ? $this->data['meta']['css']['backgroundTheme'] : '';
        $myBackgruondTheme = $myBackgruondTheme ?: $dropdownMeta['css']['backgroundTheme'];
        return $myBackgruondTheme === 'default' ? '' : $myBackgruondTheme;
    }
    /**
     * 前景
     * @return mixed
     */
    private function fore_theme() {
        $dropdownMeta = $this->dropdown_meta();
        // 如果自己有背景和前景则用自己的，否则用上层的，如buttongroup
        $myForegroundTheme = $this->data['meta']['css']['foregroundTheme'] !== 'default' ? $this->data['meta']['css']['foregroundTheme'] : '';
        $myForegroundTheme = $myForegroundTheme ?: $dropdownMeta['css']['foregroundTheme'];
        return $myForegroundTheme === 'default' ? '' : $myForegroundTheme;
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

    private function get_split_style_css() {
        $parentUI = $this->get_parent_UI();
        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['navbar','nav']);

        $cssMap = parent::css_map();
        // 作用在内部按钮innerBtnCss上
        unset($cssMap['dropdownSizing'], $cssMap['backgroundTheme'], $cssMap['borderColorClass']);
        $cssList = [];
        $cssList[] = 'layui-btn-group layui-d-inline-block';
        if ($parentIsNavbar){
            $cssList[] = 'laui-nav-item';
        }
        $cssMap['-'] = join(' ', $cssList);
        return $cssMap;
    }
    private function get_normal_style_css() {
        $parentUI = $this->get_parent_UI();
        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['navbar','nav']);
        $cssMap = parent::css_map();
        $cssList = [$this->btn_css()];
        if ($parentIsNavbar){
            $cssList[] = 'layui-nav-item';
        }
        $cssMap['-'] = join(' ', $cssList);
        return $cssMap;
    }

    /**
     * 按钮基础css属性
     */
    private function btn_css(){
        $parentUI = $this->get_parent_UI();
        $parentIsNavbar = in_array(strtolower($parentUI['type']), ['navbar','nav']);
        $arr = [];
        $foreTheme = $this->fore_theme();
        $theme = $this->theme();
        $dropdownMeta = $this->dropdown_meta();
        $size = $this->sizing();

        if ($parentIsNavbar){
            if ($foreTheme){
                $arr[] = $this->cssTranslate['foregroundTheme'][$foreTheme];
            }
            if ($theme){
                $arr[] = $this->cssTranslate['backgroundTheme'][$theme ];
            }
            $arr[] = 'layui-nav-item';
            return join(' ', $arr);
        }
        $arr[] = 'layui-btn';
        if ($dropdownMeta['custom']['isOutline']) {
            $arr[] = 'layui-btn-primary';
            $arr[] = $this->cssTranslate['borderColorClass'][$theme];
        }
        if ($size) {
            $arr[] = $size;
        }
        if ($foreTheme){
            $arr[] = $this->cssTranslate['foregroundTheme'][$foreTheme];
        }
        if ($theme){
            $arr[] = $this->cssTranslate['backgroundTheme'][$theme ];
        }
        return join(' ', $arr);
    }

    /**
     * 按钮基础style属性
     */
    private function btn_style(){
        $style = $this->style_map();
        $dropdownMeta = $this->dropdown_meta();
        $color = $this->data['meta']['style']['color'] ?: $dropdownMeta['style']['color'];
        $backgroundColor = $this->data['meta']['style']['background-color'] ?: $dropdownMeta['style']['background-color'];
        $selfHasForeground = $this->data['meta']['css']['foregroundTheme'] && $this->data['meta']['css']['foregroundTheme'] !== 'default';
        $selfHasBackground = $this->data['meta']['css']['backgroundTheme'] && $this->data['meta']['css']['backgroundTheme'] !== 'default';

        if (!$selfHasForeground && $color) { // 有css时用预定义css
            $style['color'] = "color: {$color} !important";
        }
        if (!$selfHasBackground && $backgroundColor) {
            $style['background-color'] = "background-color:{$backgroundColor} !important";
            if (!$style['border-color']){
                $style['border-color'] = "border-color:{$backgroundColor} !important";
            }
        }
        return $style ? join(";", $style) : null;
    }
    /**
     * 分体式左侧按钮css
     * @return string
     */
    private function left_split_btn_css(){
        $css = ['layui-rounded-left'];
        $css[] = $this->btn_css();
        return join(' ', $css);
    }
    /**
     * 分体式右侧按钮css
     * @return string
     */
    private function right_split_btn_css(){
        $css = ['layui-ml-0 layui-rounded-right layui-pl-1 layui-pr-1'];
        $css[] = $this->btn_css();
        return join(' ', $css);
    }
    /**
     * 分体式右侧按钮style
     * @return string
     */
    private function right_split_btn_style(){
        $style = parent::style_map();
        $color = 'rgba(255, 255, 255, 0.5)';
        if ($style['border-color']){
            $color = $style['border-color'];
        }
        return $this->btn_style(). ";border-left-color:{$color} !important";
    }
    private function get_icon(){
        switch ($this->data['meta']['custom']['direction']){
            case 'dropup': return 'layui-icon-up';
            case 'dropleft': return 'layui-icon-left';
            case 'dropright': return 'layui-icon-right';
            case 'dropdown':
            default : return 'layui-icon-down';
        }
    }
    protected function css_map(){
        // slipt风格
        if ($this->data['meta']['custom']['isSplit']) {
            return $this->get_split_style_css();
        }

        // 下面是普通风格
        return $this->get_normal_style_css();
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $style = parent::style_map($meta, $state);
        // 如果不是分体按钮，那么需要考虑属性的继承性；如果是分体按钮，属性的继承由btnStyle处理
        if ($this->data['meta']['custom']['isSplit']) {
            unset($style['background-color']);
            foreach ($style as $key){
                if (preg_match("/^border-/", $key)){
                    unset($style[$key]);
                }
            }
            return $style;
        }
        $dropdownMeta = $this->dropdown_meta();
        $color = $meta['style']['color'] ?: $dropdownMeta['style']['color'];
        $backgroundColor = $meta['style']['background-color'] ?: $dropdownMeta['style']['background-color'];
        $selfHasForeground = $meta['css']['foregroundTheme'] && $meta['css']['foregroundTheme'] !== 'default';
        $selfHasBackground = $meta['css']['backgroundTheme'] && $meta['css']['backgroundTheme'] !== 'default';

        if (!$selfHasForeground && $color) { // 有css时用预定义css
            $style['color'] = "color: {$color} !important";
        }
        if (!$selfHasBackground && $backgroundColor) {
            $style['background-color'] = "background-color:{$backgroundColor} !important";
            if (!$style['border-color']){
                $style['border-color'] = "border-color:{$backgroundColor} !important";
            }
        }
        return $style;
    }
    public function build_split_ui(){
        $space =  $this->indent();
        echo $space . '<div ';
        echo $this->output_main_attrs();
        echo ">".PHP_EOL;
        echo $this->indent(1) . '<span';
        echo $this->wrap_output('class', $this->left_split_btn_css());
        echo $this->wrap_output('style', $this->btn_style());
        echo '>';
        $this->wrap_icon(function(){
            echo $this->data['meta']['title'] ?: $this->data['type'];
            echo PHP_EOL;
        }, $this->build->get_indent() + 2);
        echo $this->indent(1) . "</span>".PHP_EOL;
        echo $this->indent(1) . '<span data-uiid="'.$this->myId().'-split" data-root="'.$this->myId().'"';
        echo $this->wrap_output('class', $this->right_split_btn_css());
        echo $this->wrap_output('style', $this->right_split_btn_style());
        echo '>';
        echo '<i class="layui-icon layui-font-12 ';
        echo $this->get_icon();
        echo '"></i>';
        echo "</span>".PHP_EOL;
        echo $space."</div>".PHP_EOL;
    }

    public function build_normal_ui(){
        $space =  $this->indent();
        echo $space . '<div ';
        echo $this->output_main_attrs();
        echo ">";
        $this->wrap_icon(function(){
            echo $this->data['meta']['title'] ?: $this->data['type'];
        }, $this->build->get_indent() + 2);
        echo '<i class="layui-icon layui-font-12 ';
        echo $this->get_icon();
        echo '"></i>';
        echo "</div>".PHP_EOL;
    }
    public function build_ui()
    {
        if (@$this->data['meta']['custom']['isSplit']){
            $this->build_split_ui();
        }else{
            $this->build_normal_ui();
        }

    }

    protected function build_ui_begin($iteratorName = null)
    {
        // layui dropdown 是代码生成的，非静态生成的，这里不用重载
    }

    protected function build_ui_end()
    {
        // layui dropdown 是代码生成的，非静态生成的，这里不用重载
    }

    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex = null, $iteratorName = '')
    {
        // layui dropdown 是代码生成的，非静态生成的，这里不用重载
    }

    public function build_code():Base_Code_Fragment
    {
        $fragment = $this->alpineBuildCode();

        $myid = $this->data['meta']['custom']['isSplit'] ? $this->myId().'-split' : $this->myId();
        $menuAlign = $this->data['meta']['custom']['menuAlign'];

        $inputDataName = $this->get_input_data_name($inputIsArr, $inputDataConfig);
        $outputDataNames = [];
        $outputDatas = $this->get_output_datas($outputDataNames);

        $values = $this->data['meta']['values'] ?: $this->demo_values();
        $dropdItems = [];
        foreach ($values as $value) {
            $item = [
                'title'=>$value['name'],
                'id'=>$value['value']?:$value['name']
            ];
            switch ($value['type']) {
                case 'header':
                    $item['type'] = 'group';
                    break;
                case 'divider':
                    $item['type'] = '-';
                case 'action':
                default:
                    break;
            }
            $dropdItems[] = $item;
        }
        $staticDatas = json_encode($dropdItems, JSON_UNESCAPED_UNICODE);

        $code = <<<DROPDOWN
this.\$nextTick(() => {
    layui_dropdown_init("{$myid}","{$menuAlign}",{$staticDatas}, "{$outputDataNames['VALUELIST']}","{$inputDataName}", Alpine);
})
DROPDOWN;
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $code);
        return $fragment;
    }

}
