<?php
namespace app\modules\build\views\preview\layui;



use app\modules\build\views\preview\Preview_View;

class Progress_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;

    private function bar_class() {
        $class = ['layui-progress-bar layui-d-flex layui-justify-content-end layui-align-items-center'];
        $cssMap = parent::css_map();
        // 前景样式会转变成style设置
        unset($cssMap['foregroundTheme']);
        if ($this->data['meta']['custom']['striped']) {
            $class[] = 'layui-progress-bar-striped';
        }
        if ($this->data['meta']['custom']['animatedStrip']) {
            $class[] = 'layui-progress-bar-animated';
        }
        return join(' ', $class);
    }
    private function bar_Style() {
        $cssMap = parent::css_map();
        $uiStyle = parent::style_map();
        $value = $this->data['meta']['value']?:50;
        $style = [];
        $style[] = "width:{$value}% !important";
        $color = '';
        if (@$uiStyle['color']){
            $color = $uiStyle['color'];
        }else if($cssMap['foregroundTheme']) {
            $color = $this->cssTranslate['themeColor'][$this->data['meta']['css']['foregroundTheme']];
        }

        if ($color){
            $style[] = "background-color:{$color} !important";
        }
        if (@$uiStyle['height']){
            $style[] = $uiStyle['height'];
        }
        return join(';', $style);
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $style = parent::style_map($meta, $state);
        unset($style['color']);
        return $style;
    }

    protected function css_map()
    {
        $cssMap = parent::css_map();
        //前景色作为进度条颜色
        unset($cssMap['foregroundTheme']);
        $cssMap['-'] = 'layui-progress';
        return $cssMap;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";

        echo $this->indent(1).'<div';
        echo $this->wrap_output('class', $this->bar_class());
        echo $this->wrap_output('style', $this->bar_style());
        $value = $this->data['meta']['value']?:50;
        echo ' lay-percent="'.$value.'%"';
        echo ">\r\n";

        if (@$this->data['meta']['custom']['label']){
            echo $this->indent(2)."<div>{$value}%</div>\r\n";
        }
        echo $this->indent(1)."</div>\r\n";
        echo "{$space}</div>\r\n";
    }
}
