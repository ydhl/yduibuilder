<?php
namespace app\modules\build\views\code\wxmp;


use app\build\Build_Model;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\code\web\bootstrap_vue\Vue_Code_Fragment;

/**
 * wxmp 生成的代码和在线预览有类似的地方，比如css的处理，js代码等，所以wxmp代码生成Class都继承之Preview
 * 这里通过trait都方式统一重载相关的实现
 * @package app\api\views
 */
trait Wxmp {
    use Wxmp_Event_Binding, Wxmp_Popup;
    protected function get_Img_Src($imgSrc){
        return rtrim($this->build->get_img_Asset_Path(), '/').'/'.basename(urldecode($imgSrc), PATHINFO_BASENAME);
    }

    protected function background_style($metaStyle){
        $styles = parent::background_style($metaStyle);
        // 微信小程序背景图片不能通过wxss引用，通过wxml中的变量方式引用
        unset($styles['background-image']);
        return $styles;
    }

    private function background_image_style(){
        $formatedImages = [];
        foreach((array)@$this->data['meta']['style']['background-image'] as $index => $img){
            if (!$img) continue;
            $img = $this->get_Img_Src($img);
            $formatedImages[] = "url(".$this->get_Img_Src($img).")";
        }
        if ($formatedImages){
            return 'background-image:'.join(',', $formatedImages)." !important";
        }
        return '';
    }

    protected function build_main_attrs() {
        $this->add_attr('style', $this->background_image_style(), ';');
        parent::build_main_attrs();
        // 小程序的事件绑定
        $events = @$this->data['events'];
        foreach((array)$events as $eventName => $eventBinds){
            $sortEventName = $this->eventMap($eventName);
            echo ' bind'.$sortEventName.'="'.$this->myId().ucfirst($sortEventName).'"';
        }
    }
    protected function build_form_attrs ($noid=false) {
        if (!$noid) {
            echo ' id="'.$this->myId().'Control"';
        }
        if (@$this->data['meta']['form']['inputName']){
            echo ' name="'.$this->data['meta']['form']['inputName'].'"';
        }
        if (@$this->data['meta']['form']['state']=='disabled'){
            echo ' disabled';
        }
        if (@$this->data['meta']['form']['state']=='readonly'){
            echo ' readonly';
        }
        if (@$this->data['meta']['form']['placeholder']) {
            echo ' placeholder="' . @$this->data['meta']['form']['placeholder'] . '"';
        }
    }
    public static function get_View_Class(array $uiconfig, Build_Model $build){
        $ui = $build->get_ui();
        $frontendFramework = $build->get_front_Framework();
        $type = strtolower($uiconfig['type']);
        return "app\\modules\\build\\views\\code\\wxmp\\{$ui}_{$frontendFramework}\\{$type}_View";
    }

    public function get_code_fragment():Base_Code_Fragment{
        if (!$this->fragment) $this->fragment = new Wxmp_Code_Fragment();
        return $this->fragment;
    }

    /**
     * 输出成WXMP格式的json字符串，单引号，如果是合法的单词，则不需要引号，比如
     * { name: 'value', 'name-1': 'value2' }
     * @param $array
     * @return string
     */
    public function formatWXMPJSON($array){
        $lines = [];
        foreach (explode(PHP_EOL, json_encode($array, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) as $line) {
            $segments = explode('":', $line);
            if (count($segments)==1){
                $lines[] = $line;
                continue;
            }
            if (preg_match("/^\s*\"[a-zA-Z0-9_]+$/", $segments[0], $matches)){
                $lines[] = preg_replace("/^(\s*)\"/", "$1",$segments[0]).": ".$segments[1];
            }else{
                $lines[] = $line;
            }
        }
//        print_r($lines);
        return join(PHP_EOL, $lines);
    }
}
