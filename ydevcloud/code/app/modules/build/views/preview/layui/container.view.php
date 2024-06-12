<?php
namespace app\modules\build\views\preview\layui;



use app\modules\build\views\preview\Preview_View;

class Container_View extends Preview_View {
    use Layui_Popup,Layui_Code_Helper;
    protected function css_map()
    {
        $cssMap = parent::css_map();
        $hasForm = false;
        foreach ((array)$this->data['items'] as $item) {
            if ($item['meta']['form']) {
                $hasForm = true;
                break;
            }
        }
        if ($hasForm) {
            $cssMap['form'] = 'layui-form';
        }
        return $cssMap;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ((array)@$this->childViews as $view){
            $view->output();
        }

        echo "{$space}</div>\r\n";
    }
}
