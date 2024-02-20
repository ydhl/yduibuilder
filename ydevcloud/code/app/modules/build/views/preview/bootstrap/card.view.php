<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;

class Card_View extends Preview_View {
    use Html_Event_Binding, Bootstrap_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $map = parent::css_map();
        $map['-'] = 'card';
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
                if (strtolower($view->data['type']) == 'list' || strtolower($view->data['type']) == 'table'){
                    $myItems['outBody'][] = $view;
                }else{
                    $myItems['inBody'][] = $view;
                }
            }
        }

        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";

        if (!@$this->data['meta']['custom']['headless']){
            echo $this->indent(1) . "<div class='card-header'>\r\n";
            foreach ($myItems['head'] as $view){
                $view->increase_indent(2);
                $view->output();
            }
            echo $this->indent(1) . "</div>\r\n";
        }

        if ($myItems['inBody']){
            echo $this->indent(1) . "<div class='card-body'>\r\n";

            foreach ($myItems['inBody'] as $view){
                $view->increase_indent(2);
                $view->output();
            }
            echo $this->indent(1) . "</div>\r\n";
        }
        if ($myItems['outBody']){
            foreach ($myItems['outBody'] as $view){
                $view->increase_indent(1);
                $view->output();
            }
        }

        if (!@$this->data['meta']['custom']['footless']){
            echo $this->indent(1) . "<div class='card-footer'>\r\n";
            foreach ($myItems['foot'] as $view){
                $view->increase_indent(2);
                $view->output();
            }
            echo $this->indent(1) . "</div>\r\n";
        }

        echo "{$space}</div>\r\n";
    }
}
