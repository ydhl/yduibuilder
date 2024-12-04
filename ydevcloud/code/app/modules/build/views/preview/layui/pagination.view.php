<?php
namespace app\modules\build\views\preview\layui;

use app\modules\build\views\code\Base_Code_Fragment;


use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Valuable_View;

class Pagination_View extends Preview_View implements Valuable_View {
    use Layui_Popup,Layui_Code_Helper;

    protected function output_as_prop($outputAs, $outputData)
    {
        if (!strcasecmp($outputAs, 'value')) return ':data-value';
        return parent::output_as_prop($outputAs, $outputData);
    }

    public function build_ui()
    {

        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->output_main_attrs(true, false);
        echo "></div>".PHP_EOL;
    }
    protected function css_map()
    {
        $cssMap = parent::css_map();
        unset($cssMap['backgroundTheme']);
        unset($cssMap['foregroundTheme']);
        unset($cssMap['paginationSizing']);
        return $cssMap;
    }

    protected function style_map($meta = null, $state = 'normal')
    {
        $styleMap = parent::style_map($meta, $state);
        unset($styleMap['background-color']);
        return $styleMap;
    }

    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $myid = $this->myid();

        if ($this->data['meta']['css']['backgroundTheme'] && !$this->data['meta']['style']['background-color']){
            $bgcolor = $this->cssTranslate['themeColor'][$this->data['meta']['css']['backgroundTheme']]?:'';
            $background = "background-color:{$bgcolor} !important";
        }else if ($this->data['meta']['style']['background-color']){
            $background = "background-color:{$this->data['meta']['style']['background-color']} !important";
        }
        if ($this->data['meta']['css']['foregroundTheme'] && !$this->data['meta']['style']['color']){
            $color = $this->cssTranslate['themeColor'][$this->data['meta']['css']['foregroundTheme']]?:'';
            $force = "color:{$color} !important";
        }else if ($this->data['meta']['style']['color']){
            $force = "color:{$this->data['meta']['style']['color']} !important";
        }
        if($background) $style["[data-uiid={$myid}] .layui-laypage-em"] = $background;
        if($force) {
            $style["[data-uiid={$myid}] em:nth-child(2)"] = $force;
            $style["[data-uiid={$myid}] a:hover"] = $force;
        }
        return $style;
    }

    public function build_code():Base_Code_Fragment
    {
        parent::build_code();
        $myid = $this->myId();
        $total = intval($this->data['meta']['custom']['total']) ?: 100;
        $size = intval($this->data['meta']['custom']['pageSize']) ?: 10;
        $theme = $this->cssTranslate['paginationSizing'][$this->data['meta']['css']['paginationSizing']]?:'';
        $limits = [10,20,30,50,100];
        if (!in_array($size, $limits)) {
            $limits[] = $size;
        }
        sort($limits);
        $limits = json_encode($limits);
        $inputDataName = $this->get_input_data_name($inputIsArr, $inputDataConfig);

        $fragment = $this->get_code_Fragment();

        $code = <<<LAYPAGE
this.\$nextTick(() => {
    layui_pagination_init("{$myid}",{$total},{$size},$limits,'{$theme}','{$inputDataName}', Alpine);
})
LAYPAGE;
        $fragment->add_code(Html_Code_Fragment::SECTION_INIT, $code);
        return $fragment;
    }
}
