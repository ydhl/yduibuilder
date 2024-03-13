<?php
namespace app\modules\build\views\code\wxmp\weui_wxmp;
use app\modules\build\views\code\wxmp\Wxmp;
use app\modules\build\views\preview\weui\Hr_View as Preview_Hr_View;

class Hr_View extends Preview_Hr_View {
    use Wxmp;

    public function build_ui()
    {
        $space =  $this->indent();
        echo "{$space}<view";
        echo $this->build_main_attrs();
        echo ">\r\n";
        echo $this->indent(1);
        echo "<view ".$this->wrap_output("style", $this->lineStyle()).$this->wrap_output("class", $this->lineCss())."></view>\r\n";
        if ($this->data['meta']['value']){
            echo $this->indent(1);
            echo "<view ".$this->wrap_output("style", $this->textStyle()).$this->wrap_output("class", $this->textCss()).">";
            echo $this->data['meta']['value'];
            echo "</view>\r\n";
        }
        echo $this->indent(1);
        echo "<view ".$this->wrap_output("style", $this->lineStyle()).$this->wrap_output("class", $this->lineCss())."></view>\r\n";

        echo $this->indent();
        echo "</view>\r\n";
    }
}
