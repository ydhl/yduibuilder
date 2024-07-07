<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Card_View extends Preview_View {
    use Weui_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $map = parent::css_map();
        $map['-'] = 'weui-panel weui-panel_access';
        return $map;
    }

    public function build_ui()
    {
        $myItems = ['head'=>[], 'inBody'=>[], 'outBody'=>[], 'foot'=>[]];
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
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        if (!@$this->data['meta']['custom']['headless']){
            echo $this->indent(1) . "<div class='weui-panel__hd'>".PHP_EOL;
            foreach ($myItems['head'] as $view){
                $view->increase_indent(2);
                $view->output();
            }
            echo $this->indent(1) . "</div>".PHP_EOL;
        }

        if ($myItems['inBody']){
            echo $this->indent(1) . "<div class='weui-panel__bd'>".PHP_EOL;

            foreach ($myItems['inBody'] as $view){
                $view->increase_indent(2);
                $view->output();
            }
            echo $this->indent(1) . "</div>".PHP_EOL;
        }
        if ($myItems['outBody']){
            foreach ($myItems['outBody'] as $view){
                $view->increase_indent(1);
                $view->output();
            }
        }

        if (!@$this->data['meta']['custom']['footless']){
            echo $this->indent(1) . "<div class='weui-panel__ft'>".PHP_EOL;
            foreach ($myItems['foot'] as $view){
                $view->increase_indent(2);
                $view->output();
            }
            echo $this->indent(1) . "</div>".PHP_EOL;
        }

        echo "{$space}</div>".PHP_EOL;
    }
}
