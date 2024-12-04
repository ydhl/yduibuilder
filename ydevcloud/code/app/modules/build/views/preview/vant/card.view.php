<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Card_View extends Preview_View {
    use  Vant_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $map = parent::css_map();
        $styleMap = parent::style_map();
        $map['-'] = 'van-card';
        if ($styleMap['color']) unset($map['foregroundTheme']);
        if ($styleMap['background-color']) unset($map['backgroundTheme']);
        return $map;
    }

    public function build_ui()
    {
        $myItems = ['head'=>[], 'inBody'=>[], 'foot'=>[]];
        foreach ((array)@$this->childViews as $view){
            if (@$view->data['placeInParent'] == 'head'){
                $myItems['head'][] = $view;
            }else if (@$view->data['placeInParent'] == 'foot') {
                $myItems['foot'][] = $view;
            }else{
                $myItems['inBody'][] = $view;
            }
        }

        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->output_main_attrs();
        echo ">".PHP_EOL;

        if (!@$this->data['meta']['custom']['headless']){
            echo $this->indent(1) . "<div class='van-card__header'>".PHP_EOL;
            foreach ($myItems['head'] as $view){
                $view->increase_indent(2);
                $view->output();
            }
            echo $this->indent(1) . "</div>".PHP_EOL;
        }

        if ($myItems['inBody']){
            echo $this->indent(1) . "<div class='van-card__body'>".PHP_EOL;

            foreach ($myItems['inBody'] as $view){
                $view->increase_indent(2);
                $view->output();
            }
            echo $this->indent(1) . "</div>".PHP_EOL;
        }

        if (!@$this->data['meta']['custom']['footless']){
            echo $this->indent(1) . "<div class='van-card__footer'>".PHP_EOL;
            foreach ($myItems['foot'] as $view){
                $view->increase_indent(2);
                $view->output();
            }
            echo $this->indent(1) . "</div>".PHP_EOL;
        }

        echo "{$space}</div>".PHP_EOL;
    }
}
