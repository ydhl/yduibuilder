<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\bootstrap\Nav_View as Bootstrap_Nav_View;
use app\modules\build\views\preview\Preview_View;

class Nav_View extends Bootstrap_Nav_View {
    protected function item_class($inputDataName, $value) {
        $css = ["'weui-navbar__item':true","'weui-bar__item_on':{$inputDataName} === {$value}"];
        $styleMap =  Preview_View::style_map();
        if (!$styleMap['color']) {
            $theme = $this->data['meta']['css']['foregroundTheme'];
            if (!$theme || $theme === 'default') {
                $css[] = "'text-dark':{$inputDataName} !== {$value}";
                $css[] = "'bg-light text-dark':{$inputDataName} === {$value}";
            }else{
                $css[] = "'{$this->cssTranslate['foregroundTheme'][$theme]}':{$inputDataName} !== {$value}";
                $css[] = "'{$this->cssTranslate['backgroundTheme'][$theme]} text-white':{$inputDataName} === {$value}";
            }
        }

        return "{".join(',', $css)."}";
    }

    protected function item_style($inputDataName, $value) {
        $styleMap =  Preview_View::style_map();
        $style = [];
        if (@$styleMap['color']) {
            $style[] = "{$inputDataName} != {$value}?'{$styleMap['color']}':'color:#fff;background-color:{$this->data['meta']['style']['color']}'";
        }
        return join('', $style);
    }
    protected function css_map()
    {
        $cssMap = Preview_View::css_map();
        unset($cssMap['foregroundTheme']);
        $cssMap['-'] = 'weui-navbar';

        return $cssMap;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = Preview_View::style_map($meta);
        unset($styleArray['color']);
        return $styleArray;
    }

    public function build_ui_static()
    {
        $values = @$this->data['meta']['values']?:$this->demo_values();
        $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_value';
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        if ($inputDataName) echo $this->wrap_output('x-input', $inputDataName);
        echo ">".PHP_EOL;

        foreach ((array)@$values as $item){
            $value = addslashes($item['value']?:$item['text']);
            echo $this->indent(1) . "<div"
                .$this->wrap_output(':class', $this->item_class($inputDataName,"'{$value}'"))
                .$this->wrap_output(':style', $this->item_style($inputDataName,"'{$value}'"))
                .$this->wrap_output('data-value', $value)
                .">{$item['text']}</div>".PHP_EOL;
        }
        foreach ((array)@$this->childViews as $view){
            $view->output();
        }
        echo "{$space}</div>".PHP_EOL;
    }
    private function nav_item($bindOutput, $inputDataName, $itemName, $is2D){
        list('name'=>$name, 'value'=>$value) = $this->get_bind_name_value($bindOutput, $itemName.($is2D?2:''));
        echo $this->indent(1) . '<template x-for="(itemOf'.$itemName.($is2D?2:'').', idxOf'.$itemName.($is2D?2:'').') in itemOf'.$itemName.'" :key="idxOf'.$itemName.($is2D?2:'').'">'.PHP_EOL;
        echo $this->indent(1) . "<div"
            .$this->wrap_output(':class', $this->item_class($inputDataName,$value))
            .$this->wrap_output(':style', $this->item_style($inputDataName,$value))
            .$this->wrap_output(':data-value', $value)
            .$this->wrap_output('x-text', $name)
            ."></div>".PHP_EOL;
        echo $this->indent(1) . "</template>".PHP_EOL;

    }
    protected function build_ui_2d_array($bindOutput, $outDataName){
        $itemName = $bindOutput['name'];
        $space =  $this->indent();
        $inputData = $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_value';
        $inputDataName = $this->is_array($inputData) ? "{$inputDataName}[idxOf{$itemName}]" : $inputDataName;

        echo "{$space}<div";
        $this->build_main_attrs();
        echo $this->wrap_output('x-input', $inputDataName);
        echo ">".PHP_EOL;

        $this->nav_item($bindOutput, $inputDataName, $itemName, true);

        echo "{$space}</div>".PHP_EOL;
    }
    protected function build_ui_array($bindOutput, $outDataName){
        $itemName = $bindOutput['name'];
        $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_value';
        $space =  $this->indent();

        echo "{$space}<div";
        $this->build_main_attrs();
        echo $this->wrap_output('x-input', $inputDataName);
        echo ">".PHP_EOL;

        $this->nav_item($bindOutput, $inputDataName, $itemName, false);

        echo "{$space}</div>".PHP_EOL;
    }

}
