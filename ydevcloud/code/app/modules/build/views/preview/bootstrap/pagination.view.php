<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;

class Pagination_View extends Preview_View {
    use Html_Event_Binding,Bootstrap_Popup,Html_Code_Helper;

    protected function style_map()
    {
        $styleArray = parent::style_map();
        unset($styleArray['background-color'],$styleArray['color']);
        return $styleArray;
    }
    protected function activeLinkStyle()
    {
        $styleArray = parent::style_map();
        return $styleArray['background-color']
            ? $styleArray['background-color'].";border-color:{$this->data['meta']['style']['background-color']}!important;"
            : '';
    }
    protected function linkStyle()
    {
        $styleArray = parent::style_map();
        return $styleArray['color'] ?: '';
    }
    protected function css_map()
    {
        $cssArray = parent::css_map();
        unset($cssArray['backgroundTheme'],$cssArray['foregroundTheme']);
        return $cssArray;
    }
    public function linkCss() {
        $cssArray = parent::css_map();
        return @$cssArray['foregroundTheme'];
    }
    public function activeLinkCss() {
        $cssArray = parent::css_map();
        if (!@$cssArray['backgroundTheme']) return '';
        $backgroundTheme = @$this->data['meta']['css']['backgroundTheme'];
        return $cssArray['backgroundTheme'] . ' text-light ' . @$this->cssTranslate['borderColorClass'][$backgroundTheme];
    }
    public function activeItemCss() {
        $cssArray = parent::css_map();
        if (!@$cssArray['backgroundTheme']) return 'active';
        return '';
    }
    public function build_ui()
    {

        $space =  $this->indent();
        echo "{$space}<nav";
        echo $this->build_main_attrs();
        echo ">\r\n";
        echo $this->indent(1);
        echo "<ul class='pagination ";
        echo "'>\r\n";
        echo $this->indent(2) . "<li class='page-item disabled'>\r\n";
        echo $this->indent(3) . "<a class='page-link' href='#' tabindex='-1' aria-disabled='true'>Previous</a>\r\n";
        echo $this->indent(2) . "</li>\r\n";
        echo $this->indent(2) . "<li class='page-item'><a class='page-link ".$this->linkCss()."' style='".$this->linkStyle()."' href='#'>1</a></li>\r\n";
        echo $this->indent(2) . "<li class='page-item ".$this->activeItemCss()."' aria-current='page'>\r\n";
        echo $this->indent(3) . "<a class='page-link ".$this->activeLinkCss()."' style='".$this->activeLinkStyle()."' href='#'>2 <span class='sr-only'>(current)</span></a>\r\n";
        echo $this->indent(2) . "</li>\r\n";
        echo $this->indent(2) . "<li class='page-item'><a class='page-link ".$this->linkCss()."' style='".$this->linkStyle()."' href='#'>3</a></li>\r\n";
        echo $this->indent(2) . "<li class='page-item'>\r\n";
        echo $this->indent(3) . "<a class='page-link ".$this->linkCss()."' style='".$this->linkStyle()."' href='#'>Next</a>\r\n";
        echo $this->indent(2) . "</li>\r\n";
        echo $this->indent(1) . "</ul>\r\n";

        echo "{$space}</nav>\r\n";
    }
}
