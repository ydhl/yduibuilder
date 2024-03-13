<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Breadcrumb_View as Preview_Breadcrumb_View;

class Breadcrumb_View extends Preview_Breadcrumb_View {
    use Wxmp;

    public function build_style($justSelf = true)
    {
        $style = parent::build_style($justSelf);
        $foregroundStyle = $this->foregroundStyle();
        if ($foregroundStyle) $style['.' . $this->myId(). '-foreground'] = $foregroundStyle;
        return $style;
    }
    protected function foregroundCss(){
        $css = [];
        $cssMap = parent::css_map();
        $foreTheme = $this->data['meta']['css']['foregroundTheme'];
        if ($foreTheme){
            $css[] = $this->cssTranslate['foregroundTheme'][$foreTheme];
        }
        $css[] = $this->myId()."-foreground";
        return join(' ', $css);
    }
    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<view";
        echo $this->build_main_attrs();
        echo ">\r\n";

        foreach ($this->values() as $item){
            echo $this->indent(1);
            echo "<view class='weui-breadcrumb-item";
            echo @$item['checked'] ? ' active' : '';
            echo "'>\r\n";
            if (!@$item['checked']){
                echo $this->indent(2);
                echo "<text".$this->wrap_output('class', $this->foregroundCss())
                    .">{$item['text']}</text>\r\n";
            }else{
                echo $this->indent(2);
                echo "<text>{$item['text']}</text>\r\n";
            }
            echo $this->indent(1);
            echo "</view>\r\n";
        }

        echo $space;
        echo "</view>\r\n";
    }
}
