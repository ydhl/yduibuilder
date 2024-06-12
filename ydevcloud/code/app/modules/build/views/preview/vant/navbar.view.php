<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Navbar_View extends Preview_View {
    use Vant_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $map = parent::css_map();
        $arr = [];
        $arr[] = 'navbar navbar-expand-lg';

        if ($this->data['meta']['custom']['color']){
            $arr[] = $this->data['meta']['custom']['color'];
        }
        $map['-'] = join(' ', $arr);
        return $map;
    }
    protected function itemCss($item) {
        $css[] ="nav-link";
        $cssMap = parent::css_map();
        $css[] = $cssMap['foregroundTheme'];
        return join(' ', $css);
    }
    protected function itemStyle($item) {
        $styleMap = parent::style_map();
        if ($item['checked']){
            if (!$styleMap['color']) return '';
            $rgba = $this->get_Rgba_Info($this->data['meta']['style']['color']);
            return "color:rgba(".$rgba['r'].",".$rgba['g'].",".$rgba['b'].",".($rgba['a'] * 0.7).") !important";
        }else{
            return $styleMap['color'];
        }
    }
    public function build_ui()
    {
        $items = ['right'=>[], 'main'=>[], 'brand'=>[]];
        foreach ((array)@$this->childViews as $view){
            if (@$view->data['placeInParent'] == 'right'){
                $items['right'][] = $view;
            }else if (@$view->data['placeInParent'] == 'brand') {
                $items['brand'][] = $view;
            }else{
                $items['main'][] = $view;
            }
        }
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '1' ], [ "text"=> 'Sample 2', "value"=> '2' ]];
        $space =  $this->indent();
        echo "{$space}<nav ";
        echo $this->build_main_attrs();
        echo ">\r\n";

        echo $this->indent(1) . "<div class='navbar-brand'>\r\n";
        foreach ((array)@$items['brand'] as $view){
            $view->output();
        }
        echo $this->indent(1) . "</div>\r\n";

        echo $this->indent(1) . "<button class='navbar-toggler' type='button' data-toggle='collapse'";
        echo ' data-target="#navbar'.$this->myId(true).'" aria-controls="#navbar'.$this->myId(true)
                .'" aria-expanded="false" aria-label="Toggle navigation">';
        echo '<span class="navbar-toggler-icon"></span>';
        echo "</button>\r\n";

        echo $this->indent(1) . "<div class='collapse navbar-collapse' id='navbar".$this->myId(true)."'>\r\n";
        echo $this->indent(2) . "<div class='navbar-nav mr-auto'>\r\n";
        foreach ((array)@$values as $item){
            echo $this->indent(3) . "<div class='nav-item ".(@$item['checked'] ? 'active' :'')."'>";
            echo '<a'.$this->wrap_output('class', $this->itemCss($item)).$this->wrap_output('style', $this->itemStyle($item)).' href="#">' . @$item['text'];
            if (@$item['checked']){
                echo '<span class="sr-only">(current)</span>';
            }
            echo "</a></div>\r\n";
        }

        foreach ((array)@$items['main'] as $view){
            $view->output();
        }
        echo $this->indent(2) . "</div>\r\n";
        echo $this->indent(1) . "</div>\r\n";
        foreach ((array)@$items['right'] as $view){
            $view->output();
        }
        echo "{$space}</nav>\r\n";
    }
}
