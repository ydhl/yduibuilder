<?php
namespace app\modules\build\views\preview\bootstrap;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Modal_View extends Preview_View {
    use Bootstrap_Popup,Html_Code_Helper;
    protected function css_map()
    {
        $css_map = parent::css_map();
        unset($css_map['backgroundTheme'], $css_map['foregroundTheme']);
        if (!$css_map['']) $css_map[''] = '';
        $css_map[''] .= ' modal fade';
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
    protected function style_map($meta=null, $state = 'normal')
    {
        $map = parent::style_map($meta, $state);
        $map['width'] = 'width:100%';

        foreach ($map as $name => $value){
            if (preg_match("/^border|^outline/", $name, $matches)){
                unset($map[$name]);
            }
        }
        return $map;
    }
    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);

        $position = $this->data['meta']['custom']['position'];
        $items = [ "top"=> 'flex-start', "center"=> 'center', "bottom"=> 'flex-end' ];
        $justify = [ "left"=> 'flex-start', "center"=> 'center', "right"=> 'flex-end' ];
        $style['.model-position'] = 'pointer-events:none;width: 100%;height: 100%;display:flex;justify-content:'.($justify[$position[0]?:'center']).'; align-items:'.($items[$position[1]?:'center']);

        return $style;
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

        echo $this->indent().'<div';
        echo $this->wrap_output('id', $pageid);
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1);
        echo "<div class='model-position'>".PHP_EOL;

        echo $this->indent(2);
        echo "<div class='modal-dialog'>".PHP_EOL;
        echo $this->indent(3);
        echo "<div".$this->wrap_output('class', $this->body_Class()).$this->wrap_output('style', $this->body_style()).">".PHP_EOL;

        if (!@$this->data['meta']['custom']['headless']){
            echo $this->indent(4);
            echo "<div class='modal-header align-items-center'>".PHP_EOL;
            echo $this->indent(5);
            echo "<div class='d-flex  move-handler'>".PHP_EOL;
            foreach ($myItems['head'] as $view){
                $view->increase_indent(6);
                $view->output();
            }
            echo $this->indent(5);
            echo "</div>".PHP_EOL;
            echo $this->indent(5);
            echo '<button type="button" onclick="YDECloud.closeSelf(this)" class="close" ><span>×</span></button>'."".PHP_EOL;

            echo $this->indent(4);
            echo "</div>".PHP_EOL;
        }


        echo $this->indent(4);
        echo "<div class='modal-body'>".PHP_EOL;
        foreach ($myItems['body'] as $view){
            $view->increase_indent(4);
            $view->output();
        }
        echo $this->indent(4);
        echo "</div>".PHP_EOL;


        if (!@$this->data['meta']['custom']['footless']){
            echo $this->indent(4);
            echo "<div class='modal-footer'>".PHP_EOL;
            foreach ($myItems['foot'] as $view){
                $view->increase_indent(4);
                $view->output();
            }
            echo $this->indent(4);
            echo "</div>".PHP_EOL;
        }


        echo $this->indent(3);
        echo "</div>".PHP_EOL;
        echo $this->indent(2);
        echo "</div>".PHP_EOL;
        echo $this->indent(1);
        echo $this->indent()."</div>".PHP_EOL;

        echo $this->indent()."</div>".PHP_EOL;
    }
}
