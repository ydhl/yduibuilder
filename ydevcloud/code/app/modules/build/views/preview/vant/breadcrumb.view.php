<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Breadcrumb_View extends Preview_View {
    use  Vant_Popup, Html_Code_Helper;
    protected function values() {
        if (@!$this->data['meta']['values']){
            return [["text"=> 'Page A', "value"=> '#1' ], [ "text"=> 'Page B', "value"=> '#2' ]];
        }
        return $this->data['meta']['values'];
    }

    protected function foregroundCss(){
        $css = [];
        $cssMap = parent::css_map();
        if ($cssMap['foregroundTheme']){
            $css[] = $cssMap['foregroundTheme'];
        }
        return join(' ', $css);
    }
    protected function foregroundStyle() {
        $styleMap = parent::style_map();
        $style = [];
        if ($styleMap['color']) {
            $style[] = $styleMap['color'];
        }
        return join(";", $style);
    }
    protected function css_map()
    {
        $cssArray = parent::css_map();
        $cssArray['breadcrumb'] = 'van-breadcrumb';
        unset($cssArray['foregroundTheme']);
        return $cssArray;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $map = parent::style_map($meta);
        unset($map['color']);
        return $map;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<ol";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        foreach ($this->values() as $item){
            echo $this->indent();
            echo "<li class='van-breadcrumb-item";
            echo @$item['checked'] ? ' active' : '';
            echo "'>".PHP_EOL;
            if (!@$item['checked']){
                echo $this->indent();
                echo "<span"
                    .$this->wrap_output('class', $this->foregroundCss())
                    .$this->wrap_output('style', $this->foregroundStyle()).">{$item['text']}</span>".PHP_EOL;
            }else{
                echo $this->indent(2);
                echo "{$item['text']}".PHP_EOL;
            }
            echo $this->indent(1);
            echo "</li>".PHP_EOL;
        }

        echo $space;
        echo "</ol>".PHP_EOL;
    }
}
