<?php
namespace app\modules\build\views\preview\layui;



use app\modules\build\views\preview\Preview_View;

class Nav_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    private function get_values () {
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '#' ], [ "text"=> 'Sample 2', "value"=> '#', 'checked'=> true ]];
        return $values;
    }


    private function tab_body_css() {
        $css = ['layui-tab-title'];
        if ($this->data['meta']['custom']['justified']) {
            $css[] = 'layui-nav-justified';
        }
        if ($this->data['meta']['custom']['filled']) {
            $css[] = 'layui-nav-fill';
        }

        return join(' ', $css);
    }
    private function tab_item_css($item=null){
        $css = ['layui-nav-item'];
        if ($item && $item['checked']){
            $css[] = 'layui-this';
        }
        $cssMap = parent::css_map();
        $css[] = $cssMap['foregroundTheme'];
        return join(' ', $css);
    }
    private function tab_item_style($item=null){
        $style = parent::style_map();
        return @$style['color'];
    }
    private function pill_item_css($item=null){
        $cssMap = parent::css_map();
        if (!$item){ // 包含的子元素
            $css = ['layui-nav-item layui-d-flex layui-align-items-center layui-justify-content-center'];
            $css[] = $cssMap['foregroundTheme'];
        }else{
            $css = ['layui-nav-item layui-btn'];
            if ($item && $item['checked']){// 选中的pill，把前景色转换成背景色
                if (@$cssMap['foregroundTheme']){
                    $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['foregroundTheme']];
                }
            }else {
                $css[] = 'layui-btn-primary layui-border-0';
                $css[] = $cssMap['foregroundTheme'];
            }
        }
        return join(' ', $css);
    }
    private function pill_item_style($item=null){
        $style = parent::style_map();
        return @$style['color'];
    }

    /**
     * 普通和tab样式
     */
    private function output_tabs() {
        $values = $this->get_values();
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";

        echo $this->indent(1)."<ul";
        echo $this->wrap_output('class', $this->tab_body_css());
        echo ">\r\n";

        foreach ((array)@$values as $item){
            echo $this->indent(2) . "<li";
            echo $this->wrap_output('class', $this->tab_item_css($item));
            echo $this->wrap_output('style', $this->tab_item_style($item));
            echo ">";
            echo $item['text'];
            echo "</li>\r\n";
        }

        foreach ((array)@$this->childViews as $view){
            echo $this->indent(2) . "<li";
            echo $this->wrap_output('class', $this->tab_item_css());
            echo $this->wrap_output('style', $this->tab_item_style());
            echo ">\r\n";
            $view->increase_indent(3);
            $view->output();
            echo $this->indent(2) . "</li>\r\n";
        }
        echo $this->indent(1);
        echo "</ul>\r\n";
        echo $space;
        echo "</div>\r\n";
    }

    /**
     * 药丸样式
     */
    private function output_pill() {
        $values = $this->get_values();
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ((array)@$values as $item){
            echo $this->indent(1);
            echo "<button type='button'";
            echo $this->wrap_output('class', $this->pill_item_css($item));
            echo $this->wrap_output('style', $this->pill_item_style($item));
            echo ">";
            echo $item['text'];
            echo "</button>\r\n";
        }


        foreach ((array)@$this->childViews as $view){
            echo $this->indent(1);
            echo "<div";
            echo $this->wrap_output('class', $this->pill_item_css());
            echo $this->wrap_output('style', $this->pill_item_style());
            echo ">\r\n";

            $view->increase_indent(2);
            $view->output();

            echo $this->indent(1);
            echo "</div>\r\n";
        }

        echo $space;
        echo "</div>\r\n";
    }

    protected function css_map()
    {
        $cssMap = parent::css_map();
        // 前景样式作用在item上
        unset($cssMap['foregroundTheme']);
        $type = $this->data['meta']['custom']['type'];
        // normal 和tab的对齐用在tabBody上
        if ($type == 'pill'){
            $cssMap['pill'] = 'layui-d-flex';
            if ($this->data['meta']['custom']['justified']){
                $cssMap['justified'] = 'layui-nav-justified';
            }
            if ($this->data['meta']['custom']['filled']){
                $cssMap['filled'] = 'layui-nav-filled';
            }
        }
        if ($type == 'tab'){
            $cssMap['-'] .= ' layui-tab';
        }
        if (!$type || $type == 'normal'){
            $cssMap['-'] .= ' layui-tab layui-tab-brief';
        }
        return $cssMap;
    }

    protected function style_map($meta=null, $state = 'normal')
    {
        $style = parent::style_map($meta);
        $type = $this->data['meta']['custom']['type'];
        if (!$type || $type=='normal') {
            $style['visibility'] = 'visibility: visible !important';
        }
        return $style;
    }

    public function build_ui()
    {
        if ($this->data['meta']['custom']['type'] == 'pill') {
            $this->output_pill();
        }else{
            $this->output_tabs();
        }
    }
}
