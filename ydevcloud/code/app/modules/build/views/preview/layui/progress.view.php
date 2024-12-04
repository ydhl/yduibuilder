<?php
namespace app\modules\build\views\preview\layui;

use app\modules\build\views\preview\bootstrap\Progress_View as Bootstrap_Progress_View;
use app\modules\build\views\preview\Preview_View;

class Progress_View extends Bootstrap_Progress_View {
    use Layui_Popup,Layui_Code_Helper;

    private function bar_class() {
        $class = ['layui-progress-bar layui-d-flex layui-justify-content-end layui-align-items-center'];
        $cssMap = parent::css_map();
        // 前景样式会转变成style设置
        unset($cssMap['foregroundTheme']);
        if ($this->data['meta']['custom']['striped']) {
            $class[] = 'layui-progress-bar-striped';
        }
        if ($this->data['meta']['custom']['animatedStrip']) {
            $class[] = 'layui-progress-bar-animated';
        }
        return join(' ', $class);
    }
    protected function css_map()
    {
        $cssMap = Preview_View::css_map();
        //前景色作为进度条颜色
        unset($cssMap['foregroundTheme']);
        $cssMap['-'] = 'layui-progress';
        return $cssMap;
    }

    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $myid = $this->myid();
        $style["[data-uiid={$myid}] [role=progressbar]"] .= ';height: 100%';
        return $style;
    }

    public function build_ui()
    {
        $outputDatas = $this->get_output_datas($outputDataNames);

        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->output_main_attrs();
        echo ">".PHP_EOL;

        echo $this->indent(1).'<div';
        echo $this->wrap_output('role', 'progressbar');
        echo $this->wrap_output('class', $this->bar_class());

        $value = $this->data['meta']['value']?:50;
        if ($outputDatas['VALUE']){
            $valueDataName = $this->get_output_data_name('VALUE', $outputDatas['VALUE'], $outputDataNames['VALUE']);
            echo $this->wrap_output(':lay-percent', "`\${{$valueDataName}}%`");
            echo $this->wrap_output(':style', "`width:\${{$valueDataName}}%`");
        }else{
            echo $this->wrap_output('lay-percent', "{$value}%");
        }
        echo ">".PHP_EOL;

        if (@$this->data['meta']['custom']['label']){
            echo $this->indent(2)."<div ";
            $textDataName = $this->get_output_data_name('TEXT', $outputDatas['TEXT'], $outputDataNames['TEXT']);
            if ($textDataName && $valueDataName){
                echo $this->wrap_output('x-text', "`\${{$textDataName}}:\${{$valueDataName}}%`");
            }else if ($textDataName){
                echo $this->wrap_output('x-text', $textDataName);
            }else if ($valueDataName){
                echo $this->wrap_output('x-text', "`\${{$valueDataName}}%`");
            }
            echo ">{$value}%</div>".PHP_EOL;
        }
        echo $this->indent(1)."</div>".PHP_EOL;
        echo "{$space}</div>".PHP_EOL;
    }
}
