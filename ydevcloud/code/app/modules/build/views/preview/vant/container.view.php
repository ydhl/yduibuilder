<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

class Container_View extends Preview_View {
    use  Vant_Popup,Html_Code_Helper;
    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        foreach ((array)@$this->childViews as $view){
            $view->output();
        }

        echo "{$space}</div>".PHP_EOL;
    }
    protected function css_map()
    {
        $css = parent::css_map();
        if ($this->isRow()){
            $css['row'] = 'van-row';
            if (@$this->data["meta"]['custom']['justify']){
                $map = ['flex-start'=> 'van-row--justify-start', 'center'=> 'van-row--justify-center', 'flex-end'=> 'van-row--justify-end', 'space-between'=> 'van-row--justify-space-between', 'space-around'=> 'van-row--justify-space-around'];
                $css['row-justify'] = $map[$this->data["meta"]['custom']['justify']];
            }
            if (@$this->data["meta"]['custom']['align']){
                $map = ['flex-start'=> 'van-row--align-start', 'center'=> 'van-row--align-center', 'flex-end'=> 'van-row--align-bottom', 'baseline'=> 'van-row--align-baseline', 'stretch'=> 'van-row--align-stretch'];
                $css['row-align'] = $map[$this->data["meta"]['custom']['align']];
            }
            if (!@$this->data["meta"]['custom']['wrap']){
                $css['nowrap'] = "van-row--nowrap";
            }
        }else if ($this->isCol()){
            $css['col'] = 'van-col';
        }
        return $css;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $style = parent::style_map($meta, $state);
        $parent = $this->get_parent_UI($index);
        $gutter = intval($parent["meta"]['custom']['gutter']);
        if ($this->isCol() && $gutter){
            $total = count($parent['items']);
            if ($total > 1){
                if ($index == 0){
                    $style['padding-right'] = "padding-right:".($gutter/2)."px !important";
                }else if ($index == $total - 1){
                    $style['padding-left'] = "padding-left:".($gutter/2)."px !important";
                    $style['padding-right'] = "padding-right:".($gutter/2)."px !important";
                }else{
                    $style['padding-left'] = "padding-left:".($gutter/2)."px !important";
                }
            }
        }
        if (!$this->data['items']){
            $style['min-height'] = "min-height:100px !important;";
            $style['min-width'] = "min-width:100px !important;";
        }
        return $style;
    }
    private function isRow(){
        $parentConfig = $this->get_parent_UI();
        if (!$parentConfig) return false;
        if ($this->get_endKind() == "mobile") {
            if ($parentConfig['type'] != "Container") return true;
            return false;
        }
        if ($parentConfig['type'] == "Container") return true;
        return false;
    }
    private function isCol(){
        $parentConfig = $this->get_parent_UI();
        if (!$parentConfig) return false;
        $parentOfParent = $this->find_parent($parentConfig['meta']['id']);
        if ($this->get_endKind() == "mobile") {
            if ($parentConfig['type'] == "Container" && $parentOfParent['type'] != "Container") return true;
            return false;
        }
        if ($parentConfig['type'] != "Container") return false;
        if ($parentOfParent['type'] == "Container") return true;
    }
}
