<?php
namespace app\modules\build\views\preview\layui;




use app\modules\build\views\preview\Preview_View;

class Text_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    protected function css_map()
    {
        $map = parent::css_map();
        $css = [];
        if (@$this->data['meta']['custom']['align']){
            $css[] = 'layui-text-' . $this->data['meta']['custom']['align'];
        }
        if (@$this->data['meta']['custom']['italic']){
            $css[] = 'layui-font-italic';
        }
        if (@$this->data['meta']['custom']['bold']){
            $css[] = 'layui-font-weight-'.strtolower($this->data['meta']['custom']['bold']);
        }
        $map['-'] = join(' ', $css);
        return $map;
    }

    public function build_ui()
    {
        $type = strtolower(@$this->data['meta']['custom']['type'] ?: 'div');

        $space =  $this->indent();
        echo "{$space}<{$type}";
        echo $this->build_main_attrs();
        echo ">";
        echo htmlentities(@$this->data['meta']['value'] ?: @$this->data['meta']['title']);
        echo "</{$type}>\r\n";
    }
}
