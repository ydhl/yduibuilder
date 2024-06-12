<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class List_View extends Preview_View {
    use Vant_Popup,Html_Code_Helper;
    private function list_theme($item) {
        $cssMap = parent::css_map();
        $css = [];
        if (@$cssMap['backgroundTheme']) $css[] = $cssMap['backgroundTheme'];
        if (@$cssMap['foregroundTheme']) $css[] = $cssMap['foregroundTheme'];
        return join(' ', $css);
    }
    private function list_style($item) {
        $styleMap = parent::style_map();
        $style = [];
        if (@$styleMap['color']) {
            $style[] = $styleMap['color'];
        }
        if (@$styleMap['background-color']) {
            $style[] = $styleMap['background-color'];
            $style[] = "border-color:".$this->data['meta']['style']['background-color']." !important";
        }
        return join(';', $style);
    }
    protected function css_map()
    {
        $arrMap = parent::css_map();
        unset($arrMap['backgroundTheme'], $arrMap['foregroundTheme']);
        $arr = [];
        $arr[] = 'van-list';
        $arrMap['-'] = join(' ', $arr);
        return $arrMap;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta);
        unset($styleArray['color'], $styleArray['background-color']);
        return $styleArray;
    }

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '1' ], [ "text"=> 'Sample 2', "value"=> '2' ]];
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ((array)@$values as $item){
            echo $this->indent(1) . "<div";
            echo $this->wrap_output('class', "van-cell van-align-items-center ".$this->list_theme($item));
            echo $this->wrap_output('style', $this->list_style($item));
            echo ">\r\n";

            echo $this->indent(2) . "<div";
            echo $this->wrap_output('class', "van-cell__value van-cell__value--alone ".$this->list_theme($item));
            echo $this->wrap_output('style', $this->list_style($item));
            echo ">\r\n";
            echo $this->indent(3) . @$item['text']."\r\n";
            echo $this->indent(2) . "</div>\r\n";

            if (@$item['checked']){
                echo $this->indent(2);
                echo '<i';
                echo $this->wrap_output("class",'van-badge__wrapper van-icon van-icon-success van-text-success'.$this->list_theme($item));
                echo $this->wrap_output("style",'font-size: 1.5rem;'.$this->list_style($item));
                echo '></i>';
            }
            echo $this->indent(1) . "</div>\r\n";
        }

        echo "{$space}</div>\r\n";
    }
}
