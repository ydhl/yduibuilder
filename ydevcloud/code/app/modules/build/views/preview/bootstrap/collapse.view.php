<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;
use function yangzie\__;

class Collapse_View extends Preview_View {
    use Html_Event_Binding, Bootstrap_Popup,Html_Code_Helper;
    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";

        $this->build_item();

        echo $this->indent();
        echo "</div>\r\n";
    }
    protected function css_map()
    {
        $map = parent::css_map();
        $map['-'] = 'accordion';
        return $map;
    }
    private function emptyContent(){
        echo $this->indent(1);
        echo '<div class="card">'."\r\n";
        echo $this->indent(2);
        echo '<div class="card-header" id="'.$this->myid(true).'heading0">'."\r\n";
        echo $this->indent(3);
        echo '<h2 class="mb-0">'."\r\n";
        echo $this->indent(4);
        echo '<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse"
                    data-target="#'.$this->myid(true).'collapse0" aria-expanded="true" :aria-controls="'.$this->myid(true).'collapse0">';
        echo "According Header";
        echo "</button>\r\n";
        echo $this->indent(3);
        echo "</h2>\r\n";
        echo $this->indent(2);
        echo "</div>\r\n";
        echo $this->indent(2);
        echo '<div id="'.$this->myid(true).'collapse0" class="collapse show" aria-labelledby="'.$this->myid(true).'heading0" data-parent="#'.$this->myid(true).'">'."\r\n";
        echo $this->indent(3);
        echo '<div class="card-body">'."\r\n";
        echo "According body, you can add item from Style Panel\r\n";
        echo "</div>\r\n";
        echo $this->indent(2);
        echo "</div>\r\n";
        echo $this->indent(1);
        echo "</div>\r\n";
    }
    private function build_item(){
        if (!$this->data['items']){
            $this->emptyContent();
            return;
        }
        foreach ($this->childViews as $index => $view){
            echo $this->indent(1);
            echo '<div class="card">'."\r\n";
            echo $this->indent(2);
            echo '<div class="card-header" id="'.$this->myid(true).'heading'.$index.'">'."\r\n";
            echo $this->indent(3);
            echo '<h2 class="mb-0">'."\r\n";
            echo $this->indent(4);
            echo '<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse"';
            echo ' data-target="#'.$this->myid(true).'collapse'.$index.'" aria-expanded="true" :aria-controls="'.$this->myid(true).'collapse'.$index.'">';
            echo $view->data['meta']['title'];
            echo "</button>\r\n";
            echo $this->indent(3);
            echo "</h2>\r\n";
            echo $this->indent(2);
            echo "</div>\r\n";
            echo $this->indent(2);

            echo '<div id="'.$this->myid(true).'collapse'.$index.'" class="collapse';
            echo !isset($this->data['meta']['custom']['activeItem']) && !$index || $this->data['meta']['custom']['activeItem'] == $index ? 'show' : '';
            echo '" aria-labelledby="'.$this->myid(true).'heading'.$index.'" data-parent="#'.$this->myid(true).'">'."\r\n";
            echo $this->indent(3);
            echo '<div class="card-body">'."\r\n";

            $view->increase_indent(3);
            $view->output();

            echo $this->indent(3);
            echo "</div>\r\n";
            echo $this->indent(2);
            echo "</div>\r\n";
            echo $this->indent(1);
            echo "</div>\r\n";
        }
    }
}
