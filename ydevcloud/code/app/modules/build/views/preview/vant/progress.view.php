<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;

/**
 * <pre>
 *  <div class="van-progress">
 *      <span class="van-progress__portion></span>
 *      <span class="van-progress__pivot van-bg-danger" style="left: 50%; transform: translate(-50%, -50%);">50%</span>
 *  </div>
 * </pre>
 */
class Progress_View extends Preview_View {
    use Vant_Popup,Html_Code_Helper;
    public function build_ui()
    {
        $space =  $this->indent();
        $value = $this->data['meta']['value']?:50;
        $outputDatas = $this->get_output_datas($outputDataNames);

        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1).'<span';
        echo $this->wrap_output('role', 'progressbar');
        echo $this->wrap_output('class', $this->bar_css());

        if ($outputDatas['VALUE']){
            $outputDataName = $this->get_output_data_name('VALUE', $outputDatas['VALUE'], $outputDataNames['VALUE']);
            echo $this->wrap_output(':aria-valuenow', $outputDataName);
            echo $this->wrap_output(':style', "`width:\${{$outputDataName}}%`");
        }else{
            echo $this->wrap_output('aria-valuenow', $value);
        }

        echo $this->wrap_output('aria-valuemin', $this->data['meta']['custom']['min'] ?? 0);
        echo $this->wrap_output('aria-valuemax', $this->data['meta']['custom']['max'] ?? 100);
        echo "></span>".PHP_EOL;

        if (@$this->data['meta']['custom']['label']){
            echo $this->indent(1).'<span';
            echo $this->wrap_output('class', $this->label_css());
            echo $this->wrap_output($outputDataName ? ':style' : 'style', $this->label_style($outputDataName, $value));

            if ($outputDataName){
                echo $this->wrap_output('x-text', "`\${{$outputDataName}}%`");
            }
            echo ">{$value}%</span>".PHP_EOL;;
        }
        echo "{$space}</div>".PHP_EOL;
    }

    public function build_style($justSelf = true) {
        $style = parent::build_style($justSelf);
        $myid = $this->myid();
        $outputDatas = $this->get_output_datas($outputDataName);

        $value = $this->data['meta']['value']?:50;
        if ($outputDatas['VALUE']){
            $barStyle = [];// build_ui中绑定
        }else{
            $barStyle = ["width: {$value}%"];
        }
        $styleMap = parent::style_map();
        if ($styleMap['color']){
            $barStyle[] = "background-color:".$this->data['meta']['style']['color']." !important";
        }
        $style["[data-uiid={$myid}] .van-progress-bar"] = join(';', $barStyle);
        return $style;
    }
    protected function css_map()
    {
        $cssMap = parent::css_map();
        $styleArray = parent::style_map();
        unset($cssMap['foregroundTheme']);
        if($styleArray['background-color']) unset($cssMap['backgroundTheme']);
        $cssMap['-'] = 'van-progress';
        return $cssMap;
    }
    protected function style_map($meta=null, $state = 'normal')
    {
        $styleArray = parent::style_map($meta, $state);
        unset($styleArray['color']);
        return $styleArray;
    }
    protected function output_as_prop($outputAs, $outputData){
        if (!strcasecmp($outputAs,'value')){
            return ;// value 不再主元素上输出，在progressbar上输出，所以这里返回null
        }
        return parent::output_as_prop($outputAs, $outputData);
    }

    private function bar_css() {
        $css = ['van-progress-bar'];
        $styleMap = parent::style_map();
        if (@$this->data['meta']['css']['foregroundTheme'] && !$styleMap['color']){
            $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['foregroundTheme']];
        }
        if (@$this->data['meta']['custom']['striped']){
            $css[] = "van-progress-bar-striped";
        }
        if (@$this->data['meta']['custom']['animatedStrip']){
            $css[] = "van-progress-bar-animated";
        }
        $css[] = 'van-progress__portion';
        return join(' ', $css);
    }
    private function label_css() {
        $css = ['van-progress__pivot'];
        if (@$this->data['meta']['css']['foregroundTheme']){
            $css[] = $this->cssTranslate['backgroundTheme'][$this->data['meta']['css']['foregroundTheme']];
        }
        return join(' ', $css);
    }
    private function label_style($value, $defaultValue) {
        $styleMap = parent::style_map();
        $style = [];
        if ($styleMap['color']){
            $style[] = "background-color:".$this->data['meta']['style']['color']." !important";
        }
        if (!$value){
            $style[] = "left: {$defaultValue}%;transform: translate(-50%, -50%);";
            return join(';', $style);
        }

        $style = ["left: \${{$value}}%;transform: translate(-50%, -50%);"];
        return "`".join(';', $style)."`";
    }

}
