<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Pagination_View as Preview_Pagination_View;
use app\modules\build\views\code\web\Vue;

class Pagination_View extends Preview_Pagination_View {
    use Vue {
        Vue::build_code as vueBuildCode;
    }

    public function build_ui()
    {
        $space =  $this->indent();
        $total = intval($this->data['meta']['custom']['total']);
        if($total<=0) $total = 100;

        $cssArray = parent::css_map();
        $styleArray = parent::style_map();
        if ($cssArray['backgroundTheme'] && !$styleArray['background-color']) {
            $backgroundTheme = @$this->data['meta']['css']['backgroundTheme'];
            $activeLinkCss = $cssArray['backgroundTheme'] . ' text-light ' . @$this->cssTranslate['borderColorClass'][$backgroundTheme]?:NULL;
        }

        $linkCss = @$cssArray['foregroundTheme'] && !$styleArray['color'] ? $cssArray['foregroundTheme'] : '';
        $activeCss = !$cssArray['backgroundTheme']?'active':'';

        $backgroundColor = $styleArray['background-color']
            ? $styleArray['background-color'].";border-color:{$this->data['meta']['style']['background-color']}!important;"
            : '';
        $color = $styleArray['color'] ?: '';

        echo "{$space}<PaginationComponent";
        $this->output_component_props();
        echo $this->wrap_output(":total", $total);
        echo $this->wrap_output('activeCss', $activeCss);
        echo $this->wrap_output('activeLinkCss', $activeLinkCss);
        echo $this->wrap_output('linkCss', $linkCss);
        echo $this->wrap_output('activeLinkStyle', $backgroundColor);
        echo $this->wrap_output('linkStyle', $color);
        echo PHP_EOL."{$space}";
        $this->output_v_model();
        echo ">".PHP_EOL;
        echo $space."</PaginationComponent>".PHP_EOL;
    }
    public function build_code(): Base_Code_Fragment
    {
        $fragment = $this->vueBuildCode();
        $fragment->add_import('@/components/PaginationComponent.vue', [], 'PaginationComponent');
        return $fragment;
    }
}
