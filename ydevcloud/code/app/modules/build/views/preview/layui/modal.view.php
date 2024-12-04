<?php
namespace app\modules\build\views\preview\layui;

use app\modules\build\views\preview\Preview_View;


class Modal_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;

    private function head_class(){
        return "layui-card-header layui-d-flex layui-align-items-center layui-justify-content-between";
    }
    private function body_class(){
        return "layui-card-body";
    }
    private function foot_class(){
        return "layui-card-footer";
    }
    protected function css_map()
    {
        $css = parent::css_map();
        $css['-'] = 'layui-card';
        return $css;
    }

    public function build_ui()
    {
        $myItems = ['head'=>[], 'body'=>[], 'foot'=>[]];
        foreach ((array)@$this->childViews as $view){
            if (@$view->data['placeInParent'] == 'head'){
                $myItems['head'][] = $view;
            }else if (@$view->data['placeInParent'] == 'foot') {
                $myItems['foot'][] = $view;
            }else{
                $myItems['body'][] = $view;
            }
        }
        $pageUIConfig = $this->build->get_ui_config();
        $pageid = $pageUIConfig['meta']['id'];

        echo $this->indent().'<div onclick="YDECloud.layerTop(\''.$pageid.'\')"';
        echo $this->output_main_attrs();
        echo ">\r\n";

        if (!@$this->data['meta']['custom']['headless']){
            echo $this->indent(1);
            echo "<div";
            echo $this->wrap_output('class', $this->head_class());
            echo ">\r\n";
            echo "<div class='layui-d-flex move-handler'>\r\n";
            echo $this->indent(2);
            foreach ($myItems['head'] as $view){
                $view->increase_indent(3);
                $view->build->set_in_Parent_Placement('head');
                $view->output();
            }
            echo "</div>";
            echo $this->indent(2);
            echo '<button type="button" onclick="YDECloud.closeSelf(this)" class="layui-btn layui-btn-primary layui-btn-xs layui-border-0 layui-reset d-none"><i class="layui-icon layui-icon-close"></i></button>'."\r\n";

            echo $this->indent(1);
            echo "</div>\r\n";
        }


        echo $this->indent(1);
        echo "<div";
        echo $this->wrap_output('class', $this->body_class());
        echo ">\r\n";
        foreach ($myItems['body'] as $view){
            $view->increase_indent(2);
            $view->build->set_in_Parent_Placement('body');
            $view->output();
        }
        echo $this->indent(1);
        echo "</div>\r\n";


        if (!@$this->data['meta']['custom']['footless']){
            echo $this->indent(2);
            echo "<div";
            echo $this->wrap_output('class', $this->foot_class());
            echo ">\r\n";
            foreach ($myItems['foot'] as $view){
                $view->increase_indent(3);
                $view->build->set_in_Parent_Placement('foot');
                $view->output();
            }
            echo $this->indent(2);
            echo "</div>\r\n";
        }

        echo $this->indent()."</div>\r\n";
    }
}
