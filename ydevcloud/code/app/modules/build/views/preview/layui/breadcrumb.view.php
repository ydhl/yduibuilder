<?php
namespace app\modules\build\views\preview\layui;


use app\modules\build\views\preview\Preview_View;

class Breadcrumb_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    private function values() {
        if (@!$this->data['meta']['values']){
            return [["text"=> 'Page A', "value"=> '#1' ], [ "text"=> 'Page B', "value"=> '#2' ]];
        }
        return $this->data['meta']['values'];
    }

    /**
     * item上的前进主题class
     * @return mixed
     */
    private function item_class() {
        $map = parent::css_map();
        return $map['foregroundTheme'];
    }
    /**
     * item上的前进主题style
     * @return mixed
     */
    private function item_Style() {
        $style = parent::style_map();
        return $style['color'] ? "color:${$style['color']} !important" : '';
    }

    /**
     * 激活项颜色
     * @return string
     */
    private function active_item_style() {
        $css = parent::css_map();
        $style = parent::style_map();
        if (!@$css['foregroundTheme'] && !@$style['color']) return '';
        $color = @$style['color'] ?: $this->cssTranslate['themeColor'][$this->data['meta']['css']['foregroundTheme']];
        if (!$color) return '';
        $rgba = $this->get_Rgba_Info($color);
        $rgba['a'] = $rgba['a'] * 0.70;
        return "color: rgba({$rgba['r']},{$rgba['g']},{$rgba['b']},{$rgba['a']}) !important";
    }
    protected function css_map()
    {
        $map = parent::css_map();
        // 前景色放到item上
        unset($map['foregroundTheme']);
        $map['-'] = 'layui-breadcrumb';
        return $map;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $style = parent::style_map($meta);
        // 前景色放到item上
        unset($style['color']);
        $style['visibility'] = 'visibility:visible !important';
        return $style;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<nav";
        echo $this->build_main_attrs();
        echo ">\r\n";

        $values = $this->values();
        foreach ($values as $index => $item){
            echo $this->indent(1);
            echo "<a ";
            if (!@$item['checked']){
                echo "href='".$item['value']."'";
            }else{
                echo "href='javascript:;'";
            }
            echo "'>";
            if (@$item['checked']){
                echo "<cite";
                echo $this->wrap_output('style', $this->active_item_style());
                echo ">{$item['text']}</cite>";
            }else{
                echo "<span";
                echo $this->wrap_output('style', $this->item_style());
                echo $this->wrap_output('class', $this->item_class());
                echo ">{$item['text']}</span>";
            }
            echo "</a>\r\n";
            if ($index!=count($values)-1){
                echo "<span lay-separator='/'";
                echo $this->wrap_output('style', $this->item_style());
                echo $this->wrap_output('class', $this->item_class());
                echo ">/</span>";
            }
        }

        echo "{$space}</nav>\r\n";
    }
}
