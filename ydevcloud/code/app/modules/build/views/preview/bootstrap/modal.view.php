<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;

class Modal_View extends Preview_View {
    use Html_Event_Binding, Bootstrap_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $css_map = parent::css_map();
        unset($css_map['backgroundTheme'], $css_map['foregroundTheme']);
        return $css_map;
    }

    private function body_class(){
        $css = ['modal-content shadow'];
        $cssMap = parent::css_map();
        $css[] = $cssMap['backgroundTheme'];
        $css[] = $cssMap['foregroundTheme'];
        return join(' ', $css);
    }
    private function body_style()
    {
        $map = parent::style_map();
        $newMap = [];
        foreach ($map as $name => $value){
            if (preg_match("/^border|^outline/", $name, $matches)){
                $newMap[] = $value;
            }
        }
        return join(';', $newMap);
    }
    protected function style_map()
    {
        $map = parent::style_map();
        $map['width'] = 'width:100%';
        $map['flex-grow'] = 'flex-grow:1';
        $map['display'] = 'display:flex';
        $map['justify-content'] = 'justify-content:center';
        $map['align-items'] = 'align-items:stretch';
        foreach ($map as $name => $value){
            if (preg_match("/^border|^outline/", $name, $matches)){
                unset($map[$name]);
            }
        }
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
                $myItems['body'][] = $view;
            }
        }

        $pageUiConfig = $this->build->get_page()->get_ui_config();
        $pageid = $pageUiConfig->meta->id;

        echo $this->indent().'<div onclick="YDECloud.layerTop(\''.$pageid.'\')"';
        echo $this->build_main_attrs();
        echo ">\r\n";

        echo $this->indent(1);
        echo "<div class='modal-dialog'>\r\n";
        echo $this->indent(2);
        echo "<div".$this->wrap_output('class', $this->body_Class()).$this->wrap_output('style', $this->body_style()).">\r\n";

        if (!@$this->data['meta']['custom']['headless']){
            echo $this->indent(3);
            echo "<div class='modal-header align-items-center'>\r\n";
            echo $this->indent(4);
            echo "<div class='d-flex  move-handler'>\r\n";
            foreach ($myItems['head'] as $view){
                $view->increase_indent(4);
                $view->output();
            }
            echo $this->indent(4);
            echo "</div>\r\n";
            echo $this->indent(4);
            echo '<button type="button" onclick="YDECloud.closeModal(\''.$pageid.'\')" class="close" ><span>×</span></button>'."\r\n";

            echo $this->indent(3);
            echo "</div>\r\n";
        }


        echo $this->indent(3);
        echo "<div class='modal-body'>\r\n";
        foreach ($myItems['body'] as $view){
            $view->increase_indent(3);
            $view->output();
        }
        echo $this->indent(3);
        echo "</div>\r\n";


        if (!@$this->data['meta']['custom']['footless']){
            echo $this->indent(3);
            echo "<div class='modal-footer'>\r\n";
            foreach ($myItems['foot'] as $view){
                $view->increase_indent(3);
                $view->output();
            }
            echo $this->indent(3);
            echo "</div>\r\n";
        }


        echo $this->indent(2);
        echo "</div>\r\n";
        echo $this->indent(1);
        echo "</div>\r\n";
        echo $this->indent()."</div>\r\n";
    }
}
