<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\preview\bootstrap\Radio_View as Bootstrap_Radio_View;
use app\modules\build\views\preview\Preview_View;

class Radio_View extends Bootstrap_Radio_View {
    protected $type = 'radio';
    protected function css_map()
    {
        $css = parent::css_map();
        $styleMap = Preview_View::style_map();
        if ($styleMap['color']){
            unset($css['foregroundTheme']);
        }
        if ($styleMap['background-color']){
            unset($css['backgroundTheme']);
        }
        $css['-'] = 'w-100 weui-cells_'.$this->type;
        return $css;
    }
    private function icon_class(){
        $map = Preview_View::style_map();
        $css = ['weui-icon-checked'];
        $foreground = $this->data['meta']['css']['foregroundTheme'];
        if (!$map['color'] && $foreground && $foreground!='default'){
            $css[] = $this->cssTranslate['foregroundTheme'][$foreground];
        }
        return join(' ', $css);
    }
    private function icon_style($inputDataName, $value){
        $style = [];
        $map = Preview_View::style_map();

        if ($map['color']){
            $style[] = $map['color'];
        }
        if ($this->type=='radio'){
            return "{$inputDataName}=={$value}?'".join(';', $style)."':'display:none'";
        }else{
            return "{$inputDataName}.indexOf({$value})!==-1?'".join(';', $style)."':''";
        }
    }
    public function build_ui_static()
    {
        $space =  $this->indent();
        $values = $this->data['meta']['values']?:$this->demo_values();
        $dataInput = $this->get_input_data($inputDataName);
        if( ! $inputDataName){
            $inputDataName = $this->myid() . '_value';
        }

        echo "{$space}<div";
        $this->build_main_attrs();
        echo ">".PHP_EOL;
        foreach ((array)@$values as $index => $item){
            $value = addslashes($item['value'] ?: $item['text']);
            echo $this->indent(1)."<label";
            echo " class='weui-cell weui-cell_active weui-check__label'";
            echo ">".PHP_EOL;

            echo $this->indent(2);
            echo "<div class='weui-cell__bd'>".PHP_EOL;
            echo $this->indent(3);
            echo "<p>".$item['text']."</p>".PHP_EOL;
            echo $this->indent(2);
            echo "</div>".PHP_EOL;

            echo $this->indent(2);
            echo "<div class='weui-cell__ft'>".PHP_EOL;
            echo $this->indent(3);
            echo "<input type='{$this->type}'";

            if (@$item['checked']){
                echo ' checked';
            }
            echo ' class="weui-check" id="'.$this->myId(true).$index.'"';
            echo $this->build_form_attrs();
            if (!$dataInput) {
                echo $this->wrap_output('x-model.fill', $inputDataName);
            }
            echo ' value="'.$value.'"';
            echo ">".PHP_EOL;
            echo $this->indent(3);
            echo '<span';
            echo $this->wrap_output('class', $this->icon_class());
            echo $this->wrap_output(':style', $this->icon_style($inputDataName, "'{$value}'"));
            echo "></span>".PHP_EOL;
            echo $this->indent(2);
            echo "</div>".PHP_EOL;


            echo $this->indent(1);
            echo "</label>".PHP_EOL;
        }
        echo "{$space}</div>".PHP_EOL;
    }
}
