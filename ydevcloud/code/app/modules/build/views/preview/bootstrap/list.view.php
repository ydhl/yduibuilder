<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;

class List_View extends Preview_View {
    use Html_Event_Binding,Bootstrap_Popup,Html_Code_Helper;
    private function list_theme($item) {
        $css = ['list-group-item'];
        if (@$item['checked']){
            $css[] = 'active';
            if (@$this->data['meta']['css']['backgroundTheme']){
                $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['backgroundTheme']];
                $css[] = $this->cssTranslate['borderColorClass'][$this->data['meta']['css']['backgroundTheme']];
            }
        }
        $cssMap = parent::css_map();
        if (@$this->data['meta']['css']['backgroundTheme']
            && $this->data['meta']['css']['backgroundTheme']!='default'){
            $css[] = 'list-group-item-' . $this->data['meta']['css']['backgroundTheme'];
        }
        if (@$cssMap['foregroundTheme']) {
            $css[] = $cssMap['foregroundTheme'];
        }
        return join(' ', $css);
    }
    private function list_style($item) {
        $styleMap = parent::style_map();
        $style = [];
        if (@$styleMap['color']) {
            $style[] = $styleMap['color'];
        }
        if (@$styleMap['background-color']) {
            if ($item['checked']){
                $rgba = $this->get_Rgba_Info($this->data['meta']['style']['background-color']);
                $style[] = "background-color:rgba(".$rgba['r'].",".$rgba['g'].",".$rgba['b'].",".($rgba['a'] * 0.75).") !important";
            }else{
                $style[] = $styleMap['background-color'];
            }
            $style[] = "border-color:".$this->data['meta']['style']['background-color']." !important";
        }
        return join(';', $style);
    }
    protected function css_map()
    {
        $arrMap = parent::css_map();
        unset($arrMap['backgroundTheme'], $arrMap['foregroundTheme']);
        $arr = [];
        if (@$this->data['meta']['custom']['horizontal']){
            $arr[] = 'list-group-horizontal';
        }
        if (@$this->data['meta']['custom']['flush']){
            $arr[] = 'list-group-flush';
        }
        $arr[] = 'list-group';
        $arrMap['-'] = join(' ', $arr);
        return $arrMap;
    }
    protected function style_map()
    {
        $styleArray = parent::style_map();
        unset($styleArray['color'], $styleArray['background-color']);
        return $styleArray;
    }

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '1' ], [ "text"=> 'Sample 2', "value"=> '2' ]];
        $space =  $this->indent();
        echo "{$space}<ul ";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ((array)@$values as $item){
            echo $this->indent(1) . "<li ".$this->wrap_output('class', $this->list_theme($item))
                .$this->wrap_output('style', $this->list_style($item)).">\r\n";
            echo $this->indent(2) . @$item['text']."\r\n";
            echo $this->indent(1) . "</li>\r\n";
        }

        echo "{$space}</ul>\r\n";
    }
}
