<?php
namespace app\modules\build\views\preview\bootstrap;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\Html_Event_Binding;

class Hr_View extends Preview_View {
    use Html_Event_Binding, Bootstrap_Popup,Html_Code_Helper;
    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo ">\r\n";
        echo $this->indent(1);
        echo "<div ".$this->wrap_output("style", $this->lineStyle()).$this->wrap_output("class", $this->lineCss())."></div>\r\n";
        if ($this->data['meta']['value']){
        echo $this->indent(1);
            echo "<div ".$this->wrap_output("style", $this->textStyle()).$this->wrap_output("class", $this->textCss()).">";
            echo $this->data['meta']['value'];
            echo "</div>";
        }
        echo $this->indent(1);
        echo "<div ".$this->wrap_output("style", $this->lineStyle()).$this->wrap_output("class", $this->lineCss())."></div>\r\n";
        echo "</div>\r\n";
    }
    protected function lineStyle() {
        $styles = parent::style_map();
        $newStyle = [];
        if (!@$styles['height']) {
            $newStyle[] = 'height: 1px';
        }else{
            $newStyle[] = $styles['height'];
        }

        if (!@$styles['background-color'] && !@$this->data['meta']['css']['backgroundTheme']) {
            $newStyle[] = 'background-color:rgba(0,0,0,.1)';
        }else if ($styles['background-color']) {
            $newStyle[] = $styles['background-color'];
        }
        return join(';', $newStyle);
    }
    protected function lineCss() {
        $map = parent::css_map();
        return 'flex-grow-1 '.$map['backgroundTheme'];
    }
    protected function textStyle() {
        $styles = parent::style_map();
        $newStyle = [];
        if (!@$styles['height']) {
            $newStyle[] = 'line-height:1px';
      } else {
            $newStyle[] = 'line-height:'.$this->data['meta']['style']['height'];
      }

      if ($styles['color']) {
          $newStyle[] = $styles['color'];
      }
      return join(";", $newStyle);
    }
    protected function textCss() {
        $map = parent::css_map();
        return 'flex-shrink-0 pl-2 pr-2 '.$map['foregroundTheme'];
    }
    protected function css_map()
    {
        $map = parent::css_map();
        unset($map['backgroundTheme']);
        unset($map['foregroundTheme']);
        $map['-'] = 'd-flex justify-content-center align-items-center';
        return $map;
    }

    protected function style_map()
    {
        $styles = parent::style_map();
        unset($styles['height']);
        unset($styles['background-color']);
        unset($styles['color']);

        if (!$styles['width']) {
            $styles['width'] = 'width:100%';
        }
        return $styles;
    }
}
