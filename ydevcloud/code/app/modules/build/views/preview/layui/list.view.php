<?php
namespace app\modules\build\views\preview\layui;



use app\modules\build\views\preview\Preview_View;

class List_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    private function item_class($item){
        $css = ['layui-list-group-item'];
        $cssMap = parent::css_map();
        if (@$cssMap['backgroundTheme']){
            $css[] = 'layui-list-group-item-'.$this->data['meta']['css']['backgroundTheme'];
        }
        if (@$cssMap['foregroundTheme']){
            $css[] = $cssMap['foregroundTheme'];
        }
        return join(' ', $css);
    }
    private function item_style($item){
        $style = [];
        $styleMap = parent::style_map();
        $cssMap = parent::css_map();
        if (@$styleMap['color']){
            $style[] = $styleMap['color'];
        }
        //选中项背景变淡
        if (!@$item['checked']){
            $color = @$this->data['meta']['style']['background-color'];
            if (!$color && $cssMap['backgroundTheme']){
                $color = $this->cssTranslate['themeColor'][$this->data['meta']['css']['backgroundTheme']];
            }
            if ($color){
                $rgba = $this->get_Rgba_Info($color);
                $rgba['a'] *= $rgba['a'] * 0.75;
                $style[] = "background-color:rgba({$rgba['r']},{$rgba['g']},{$rgba['b']},{$rgba['a']}) !important";
            }
        }else{
            if (@$styleMap['background-color']){
                $style[] = $styleMap['background-color'];
            }
        }
        return join(';', $style);
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $style = parent::style_map($meta, $state);
        //前进背景色都放到item上
        unset($style['color'],$style['background-color']);
        return $style;
    }

    protected function css_map()
    {
        $arrMap = parent::css_map();
        //前进背景色都放到item上
        unset($arrMap['backgroundTheme'],$arrMap['foregroundTheme']);
        $arr = [];
        $arr[] = 'layui-list-group';
        $arrMap['-'] = join(' ', $arr);
        return $arrMap;
    }

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "name"=> 'Sample 1', "value"=> '1' ], [ "name"=> 'Sample 2', "value"=> '2' ]];
        $space =  $this->indent();
        echo "{$space}<ul ";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ((array)@$values as $item){
            echo $this->indent(1) . "<li";
            echo $this->wrap_output('class', $this->item_class($item));
            echo $this->wrap_output('style', $this->item_style($item));
            echo ">\r\n";
            echo $this->indent(2) . @$item['text']."\r\n";
            echo $this->indent(1) . "</li>\r\n";
        }

        echo "{$space}</ul>\r\n";
    }
}
