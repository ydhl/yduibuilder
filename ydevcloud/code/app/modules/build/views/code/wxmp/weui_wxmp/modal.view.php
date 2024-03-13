<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Modal_View as Preview_Modal_View;

class Modal_View extends Preview_Modal_View {
    use Wxmp;
    public function build_ui()
    {
        $myItems = ['head'=>[], 'inBody'=>[], 'outBody'=>[], 'foot'=>[]];
        foreach ((array)@$this->childViews as $view){
            if (@$view->data['placeInParent'] == 'head'){
                $myItems['head'][] = $view;
            }else if (@$view->data['placeInParent'] == 'foot') {
                $myItems['foot'][] = $view;
            }else{
                $myItems['body'][] = $view;
            }
        }

        echo $this->indent().'<view class="weui-mask"></view><view';
        echo $this->build_main_attrs();
        echo ">\r\n";

        if (!@$this->data['meta']['custom']['headless']){
            echo $this->indent(1);
            echo "<view class='weui-dialog__hd align-items-center'>\r\n";
            echo $this->indent(2);
            echo "<view class='d-flex  move-handler'>\r\n";
            foreach ($myItems['head'] as $view){
                $view->increase_indent(3);
                $view->build->set_in_Parent_Placement('head');
                $view->output();
            }
            echo $this->indent(2);
            echo "</view>\r\n";

            echo $this->indent(1);
            echo "</view>\r\n";
        }


        echo $this->indent(1);
        echo "<view class='weui-dialog__bd'>\r\n";
        foreach ($myItems['body'] as $view){
            $view->increase_indent(1);
            $view->build->set_in_Parent_Placement('body');
            $view->output();
        }
        echo $this->indent(1);
        echo "</view>\r\n";


        if (!@$this->data['meta']['custom']['footless']){
            echo $this->indent(1);
            echo "<view class='weui-dialog__ft'>\r\n";
            foreach ($myItems['foot'] as $view){
                $view->increase_indent(1);
                $view->build->set_in_Parent_Placement('foot');
                $view->output();
            }
            echo $this->indent(1);
            echo "</view>\r\n";
        }

        echo $this->indent()."</view>\r\n";
    }
}
