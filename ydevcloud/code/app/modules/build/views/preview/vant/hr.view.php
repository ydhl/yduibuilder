<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;


class Hr_View extends Preview_View {
    use Vant_Popup,Html_Code_Helper;
    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;
        if ($this->data['meta']['value']){
            echo $this->indent(1);
            echo htmlentities($this->data['meta']['value']);
            echo PHP_EOL;
        }
        echo $this->indent(1);
        echo "</div>".PHP_EOL;
    }
    public function build_style($justSelf = true)
    {
        $styleMap = parent::build_style($justSelf);
        $myid = $this->myid();

        $height = $this->data['meta']['style']['height'] ?: '1px';
        $style = $this->data['meta']['custom']['style'] ?: 'solid';
        if ($style == 'double' && intval($height) < 3){
            $height = '3px';
        }
        $styleMap["[data-uiid={$myid}].van-divider:before, [data-uiid={$myid}].van-divider:after"] = "border-top-width: {$height};";
        return $styleMap;
    }

    protected function css_map()
    {
        $map = parent::css_map();
        unset($map['backgroundTheme']);
        unset($map['foregroundTheme']);

        $meta = $this->data['meta'];

        switch($meta['custom']['style']){
            case 'dotted': $style = 'van-divider--dotted';break;
            case 'dashed': $style = 'van-divider--dashed';break;
            case 'double': $style = 'van-divider--double';break;
            case 'solid':
            default :$style = 'van-divider--hairline';break;
        }
        $css = ["van-divider {$style} van-divider--content-center"];
        if ($meta['css']['backgroundTheme']){
            $css[] = "van-border-".$meta['css']['backgroundTheme'];
        }
        if ($meta['css']['foregroundTheme']){
            $css[] = "van-text-".$meta['css']['foregroundTheme'];
        }
        $map['-'] = join(' ', $css);
        return $map;
    }

    protected function style_map($meta=null, $state = 'normal')
    {
        $meta = $meta??$this->data['meta'];
        $styles = parent::style_map($meta, $state);
        unset($styles['height']);
        unset($styles['background-color']);

        if ($styles['color']) {
            $styles['color'].='!important';
        }
        if (!$meta['width']) {
            $styles['width'] = 'width:100%';
        }
        if ($meta['style']['background-color']) {
            $styles['border-color'] = 'border-color:'. $meta['style']['background-color'].'!important';
        }
        return $styles;
    }
}
