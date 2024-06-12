<?php
namespace app\modules\build\views\preview\layui;



use app\modules\build\views\preview\Preview_View;

class Navbar_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    private function get_items() {
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
        return $items;
    }

    private function menu_style($item){
        $style = parent::style_map();
        return @$style['color'];
    }
    private function menu_class($item){
        $css = parent::css_map();
        return @$css['foregroundTheme'];
    }

    protected function css_map()
    {
        $map = parent::css_map();
        $arr = [];
        $arr[] = 'layui-custom-nav layui-nav';
        $map['-'] = join(' ', $arr);
        return $map;
    }

    public function build_ui()
    {
        $items = $this->get_items();
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '1' ], [ "text"=> 'Sample 2', "value"=> '2', "checked"=> true ]];
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        echo ">\r\n";

        // LOGO
        echo $this->indent(1) . "<div>\r\n";
        foreach ((array)@$items['brand'] as $view){
            $view->increase_indent(2);
            $view->build->set_in_Parent_Placement('brand');
            $view->output();
        }
        echo $this->indent(1) . "</div>\r\n";

        //中间菜单区域
        echo $this->indent(1) . "<div class='layui-mr-auto'>\r\n";
        foreach ((array)@$values as $item){
            echo $this->indent(2) . "<div class='layui-nav-item ".(@$item['checked'] ? 'layui-this' :'')."'>\r\n";
            echo $this->indent(3) . '<a';
            echo $this->wrap_output('href', $item['value']);
            echo $this->wrap_output('style', $this->menu_style($item));
            echo $this->wrap_output('class', $this->menu_class($item));
            echo '>' . @$item['text'] . "</a>\r\n";
            echo $this->indent(2) . "</div>\r\n";
        }
        foreach ((array)@$items['main'] as $view){
            $view->increase_indent(2);
            $view->build->set_in_Parent_Placement('main');
            $view->output();
        }
        echo $this->indent(1) . "</div>\r\n";

        // 右边部分
        echo $this->indent(1) . "<div>\r\n";
        foreach ((array)@$items['right'] as $view){
            $view->increase_indent(2);
            $view->build->set_in_Parent_Placement('right');
            $view->output();
        }
        echo $this->indent(1) . "</div>\r\n";

        echo "{$space}</div>\r\n";
    }
}
