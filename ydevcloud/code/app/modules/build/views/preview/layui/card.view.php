<?php
namespace app\modules\build\views\preview\layui;



use app\modules\build\views\preview\Preview_View;

class Card_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    private function sub_items() {
        $myItems = ['head'=>[], 'main'=>[], 'foot'=>[]];
        foreach ((array)@$this->childViews as $view){
            if (@$view->data['placeInParent'] == 'head'){
                $myItems['head'][] = $view;
            }else if (@$view->data['placeInParent'] == 'foot') {
                $myItems['foot'][] = $view;
            }else{
                $myItems['main'][] = $view;
            }
        }
        return $myItems;
    }
    protected function css_map()
    {
        $map = parent::css_map();
        $map['-'] = 'layui-card';
        return $map;
    }

    protected function card_Head_Class(){
        $arr = [];
        $hasTab = false;
        foreach ((array)$this->data['items'] as $item){
            if (strtolower($item['type']) == 'nav' && $item['meta']['custom']['type'] == 'tabs' ){
                $hasTab = true;
                break;
            }
        }
        if (!$hasTab) {
            $arr[] = 'layui-card-header';
        }
        return $arr ? join(' ', $arr) : '';
    }

    protected function card_foot_Class(){
        return 'layui-card-footer';
    }

    public function build_ui()
    {
        $myItems = $this->sub_items();
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->output_main_attrs();
        echo ">\r\n";

        if (!@$this->data['meta']['custom']['headless']){
            echo $this->indent(1) . "<div";
            echo $this->wrap_output('class', $this->card_Head_Class());
            echo ">\r\n";
            foreach ($myItems['head'] as $view){
                $view->increase_indent(2);
                $view->build->set_in_Parent_Placement('head');
                $view->output();
            }
            echo $this->indent(1) . "</div>\r\n";
        }

        echo $this->indent(1) . "<div class='layui-card-body'>\r\n";
        foreach ($myItems['main'] as $view){
            $view->increase_indent(2);
            $view->build->set_in_Parent_Placement('main');
            $view->output();
        }
        echo $this->indent(1) . "</div>\r\n";

        if (!@$this->data['meta']['custom']['footless']){
            echo $this->indent(1) . "<div";
            echo $this->wrap_output('class', $this->card_foot_Class());
            echo ">\r\n";
            foreach ($myItems['foot'] as $view){
                $view->increase_indent(2);
                $view->build->set_in_Parent_Placement('foot');
                $view->output();
            }
            echo $this->indent(1) . "</div>\r\n";
        }

        echo "{$space}</div>\r\n";
    }
}
