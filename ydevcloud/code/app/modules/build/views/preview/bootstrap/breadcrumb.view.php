<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Breadcrumb_View extends ValueList_View {
    protected function values() {
        if (@!$this->data['meta']['values']){
            return [["text"=> 'Page A', "value"=> '#1' ], [ "text"=> 'Page B', "value"=> '#2' ]];
        }
        return $this->data['meta']['values'];
    }

    protected function foregroundCss(){
        $css = [];
        $cssMap = parent::css_map();
        if ($cssMap['foregroundTheme']){
            $css[] = $cssMap['foregroundTheme'];
        }
        return join(' ', $css)?:NULL;
    }
    protected function foregroundStyle() {
        $styleMap = parent::style_map();
        $style = [];
        if ($styleMap['color']) {
            $style[] = $styleMap['color'];
        }
        return join(";", $style)?:NULL;
    }
    protected function css_map()
    {
        $cssArray = parent::css_map();
        $cssArray['breadcrumb'] = 'breadcrumb';
        unset($cssArray['foregroundTheme']);
        return $cssArray;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $map = parent::style_map($meta);
        unset($map['color']);
        return $map;
    }

    private function ui_begin($bindOutput, $outDataName, $is2D=false){
        $space =  $this->indent();
        $inputData = $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_temp';

        echo "{$space}<ol";
        echo $this->build_main_attrs();
        if ($inputDataName) {
            if ($is2D){
                echo $this->wrap_output('x-input', $this->is_array($inputData) ? "{$inputDataName}[idxOf{$outDataName}]" : $inputDataName);
            }else{
                echo $this->wrap_output('x-input', $inputDataName);
            }
        }
        echo ">".PHP_EOL;

    }
    private function ui_end(){
        $space =  $this->indent();
        echo $space;
        echo "</ol>".PHP_EOL;
    }

    private function ui_item($bindOutput, $outDataName, $is2D){
        $itemName = $bindOutput['name'];
        if ($is2D) {
            $itemName .= '2';
        }

        echo $this->indent(1).'<template x-for="(itemOf'.$itemName.', idxOf'.$itemName.') in '
            .($is2D?"itemOf":"").$outDataName.'" :key="idxOf'.$itemName.'">'.PHP_EOL;

        if ($is2D){
            $this->ui_li($bindOutput['item'], $bindOutput['name'], "itemOf{$outDataName}", true);
        }else{
            $this->ui_li($bindOutput, $bindOutput['name'], $outDataName, false);
        }

        echo $this->indent(1).'</template>'.PHP_EOL;
    }
    private function ui_li($bindOutput, $itemName, $outDataName, $is2d){
        $dataName = $itemName;
        if ($is2d) {
            $dataName .= '2';
        }
        list('name'=>$xText, 'value'=>$xValue) = $this->get_bind_name_value($bindOutput, $dataName);
        $inputData = $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_temp';

        if ($inputDataName) {
            $inputDataName = $this->is_array($inputData) ? "{$inputDataName}[idxOf{$itemName}]" : $inputDataName;

            echo $this->indent(1) . '<li :class="{\'breadcrumb-item\': true, \'active\':' . $inputDataName . '==' . $xValue . '}"';
            echo '>' . PHP_EOL;
            echo $this->indent(2) . '<template x-if="' . $inputDataName . '!=' . $xValue . '">' . PHP_EOL;
            echo $this->indent(2) . "<a href='javascript:;'";
            echo $this->wrap_output('class', $this->foregroundCss());
            echo $this->wrap_output('style', $this->foregroundStyle());
            $this->build_data_output_bind();
            echo $this->wrap_output(':data-value', $xValue);
            echo $this->wrap_output(':data-bound', "'{$outDataName}[\''+idxOf{$dataName}+'\']'");
            echo $this->wrap_output('x-text', $xText);
            echo "></a>" . PHP_EOL;
            echo $this->indent(2) . "</template>" . PHP_EOL;

            echo $this->indent(2) . '<template x-if="' . $inputDataName . '==' . $xValue . '">' . PHP_EOL;
            echo $this->indent(2) . "<span";
            echo $this->wrap_output(':data-value', $xValue);
            echo $this->wrap_output(':data-bound', "'{$outDataName}[\''+idxOf{$dataName}+'\']'");
            echo $this->wrap_output('x-text', $xText);
            echo "></span>" . PHP_EOL;
            echo $this->indent(2) . "</template>" . PHP_EOL;
        }else{
            echo $this->indent(1).'<li class="breadcrumb-item">'.PHP_EOL;
            echo $this->indent(2)."<a href='javascript:;'";
            echo $this->wrap_output('class', $this->foregroundCss());
            echo $this->wrap_output('style', $this->foregroundStyle());
            $this->build_data_output_bind();
            echo $this->wrap_output('x-text', $xText);
            echo $this->wrap_output(':data-value', $xValue);
            echo $this->wrap_output(':data-bound', "'{$outDataName}[\''+idxOf{$dataName}+'\']'");
            echo "></a>".PHP_EOL;
        }

        echo $this->indent(1)."</li>".PHP_EOL;
    }

    protected function build_ui_static()
    {
        $this->ui_begin(null, null, false);
        $inputData = $this->get_input_data($inputDataName);
        if (!$inputDataName) $inputDataName = $this->myid() . '_temp';
        foreach ($this->values() as $item){
            echo $this->indent(1);

            $css = ["'breadcrumb-item': true"];
            if ($inputDataName){
                $css[] = "'active': {$inputDataName}=='{$item['value']}'";
            }else if (@$item['checked']){
                $css[] = "'active': true";
            }

            echo '<li :class="{' . join(', ', $css) . '}"';
            echo '>'.PHP_EOL;
            if ($inputDataName){
                echo $this->indent(2) . '<template x-if="' . $inputDataName . '!=\'' . $item['value'] . '\'">' . PHP_EOL;
                echo $this->indent(2);
                echo "<a href='javascript:;'";
                echo $this->wrap_output('class', $this->foregroundCss());
                echo $this->wrap_output('style', $this->foregroundStyle());
                echo $this->wrap_output('data-value', trim($item['value']?:$item['text']));
                $this->build_data_output_bind();
                echo ">{$item['text']}</a>".PHP_EOL;
                echo $this->indent(2)."</template>".PHP_EOL;


                echo $this->indent(2) . '<template x-if="' . $inputDataName . '==\'' . $item['value'] . '\'">' . PHP_EOL;
                echo $this->indent(2) . "<span";
                echo $this->wrap_output('data-value', trim($item['value']?:$item['text']));
                echo ">{$item['text']}</span>".PHP_EOL;
                echo $this->indent(2)."</template>".PHP_EOL;
            }else {
                if (!@$item['checked']) {
                    echo $this->indent(2);
                    echo "<a href='javascript:;'";
                    echo $this->wrap_output('class', $this->foregroundCss());
                    echo $this->wrap_output('style', $this->foregroundStyle());
                    echo $this->wrap_output('data-value', trim($item['value']?:$item['text']));
                    $this->build_data_output_bind();
                    echo ">{$item['text']}</a>".PHP_EOL;
                }else{
                    echo $this->indent(2) . "<span";
                    echo $this->wrap_output('data-value', trim($item['value']?:$item['text']));
                    echo ">{$item['text']}</span>".PHP_EOL;
                }
            }
            echo $this->indent(1);
            echo "</li>".PHP_EOL;
        }
        $this->ui_end();
    }

    protected function build_ui_2d_array($bindOutput, $outDataName)
    {
        $this->ui_begin($bindOutput, $outDataName, true);
        $this->ui_item($bindOutput, $outDataName, true);
        $this->ui_end();
    }

    protected function build_ui_array($bindOutput, $outDataName)
    {
        $this->ui_begin($bindOutput, $outDataName, false);
        $this->ui_item($bindOutput, $outDataName, false);
        $this->ui_end();
    }

    function build_code(): Base_Code_Fragment
    {
        parent::build_code();
        $codeFragment = $this->get_code_Fragment();
        $inputData = $this->get_input_data($dataName);
        // 如果没有数据绑定的话，定义一个临时数据
        if (!$dataName) {
            $codeFragment->add_code(Html_Code_Fragment::SECTION_DATA_DEFINE, $this->myid() . '_temp: "",');
        }
        return $codeFragment;
    }
}
