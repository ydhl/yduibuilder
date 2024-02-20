<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;

class Nav_View extends Preview_View {
    use Html_Event_Binding,Bootstrap_Popup,Html_Code_Helper;
    private function theme_css($item) {
        $css = ["nav-link"];
        if ($item['checked']){
            $css[] = "active";
        }
        $theme = $this->data['meta']['css']['foregroundTheme'];
        $theme = $theme && $theme !== 'default' ? $theme : 'primary';
        if ($item['checked']){
            $css[] = $this->cssTranslate['backgroundTheme'][$theme].' text-white';
        }else{
            $css[] = $this->cssTranslate['foregroundTheme'][$theme];
        }
        return join(' ', $css);
    }

    private function theme_style($item) {
        $style = [];
        if ($item['checked']){
            $style[] = "background-color:".$this->data['meta']['style']['color']." !important;color:#fff;";
        }else{
            $style[] = "color:".$this->data['meta']['style']['color']." !important;";
        }
        return join(' ', $style);
    }
    protected function css_map()
    {
        $cssMap = parent::css_map();
        unset($cssMap['foregroundTheme']);
        $arr = [];
        $arr[] = 'nav';
        if (@$this->data['meta']['custom']['type'] == 'tab'){
            $arr[] =  'nav-tabs';
        }
        if (@$this->data['meta']['custom']['type'] == 'pill'){
            $arr[] =  'nav-pills';
        }
        if (@$this->data['meta']['custom']['justified']){
            $arr[] =  'nav-justified';
        }
        if (@$this->data['meta']['custom']['filled']){
            $arr[] =  'nav-fill';
        }

        $parentUI = $this->get_parent_UI();
        $parentIsCard = in_array(strtolower($parentUI['type']), ['card']);

        if ($parentIsCard && @$this->data['meta']['custom']['type'] == 'tab'){
            $arr[] = 'card-header-tabs';
        }
        if ($parentIsCard && @$this->data['meta']['custom']['type'] == 'pill'){
            $arr[] = 'card-header-pills';
        }

        $cssMap['-'] = join(' ', $arr);
        return $cssMap;
    }

    public function build_ui()
    {
        $values = @$this->data['meta']['values']?:[[ "text"=> 'Sample 1', "value"=> '#' ], [ "text"=> 'Sample 2', "value"=> '#', 'checked'=> true ]];
        $space =  $this->indent();
        echo "{$space}<div ";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ((array)@$values as $item){
            echo $this->indent(1) . "<div class='nav-item'>\r\n";
            echo $this->indent(2) . "<a"
                .$this->wrap_output('class', $this->theme_css($item))
                .$this->wrap_output('style', $this->theme_style($item))
                ." href='{$item['value']}'>{$item['text']}</a>\r\n";
            echo $this->indent(1) . "</div>\r\n";
        }
        foreach ((array)@$this->childViews as $view){
            $view->output();
        }
        echo "{$space}</div>\r\n";
    }
}
