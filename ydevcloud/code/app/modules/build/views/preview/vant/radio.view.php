<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use phpseclib3\Math\BigInteger\Engines\PHP;


class Radio_View extends Preview_View {
    use Vant_Popup,Html_Code_Helper;
    protected $type = 'radio';
    protected function disable_style()
    {
        if (in_array($this->data['meta']['form']['state'], ['readonly','disabled'])) {
           return 'border-color:var(--van-gray-6);background-color:var(--van-gray-6)';
        }
        return null;
    }

    protected function css_map() {
        $map = parent::css_map();
        $map['-'] = 'van-h-auto';
        if (@$this->data['meta']['form']['state'] == 'hidden'){
            $map['-'] .= ' van-d-none';
        }
        return $map;
    }
    private function icon_class($item){
        $_ = ['van-'.$this->type.'__icon '.($this->type=='radio' ? 'van-radio__icon--round' : 'van-checkbox__icon--square')];
        if (@$item['checked']){
            $_[] = 'van-'.$this->type.'__icon--checked';
        }
        return join(' ', $_);
    }

    public function build_ui()
    {
        $space =  $this->indent(2);
        $values = @$this->data['meta']['values']?:[[ "name"=> 'sample', "value"=> '1' ]];
        echo "{$space}<div";
        $this->build_main_attrs();
        echo ">".PHP_EOL;
        foreach ((array)@$values as $index => $item){
            echo $this->indent(3)."<div";
            echo $this->wrap_output('class', 'van-'.$this->type.'-group van-'.$this->type.'-group--horizontal');
            echo ">".PHP_EOL;
            echo $this->indent(4);
            echo '<div class="van-'.$this->type.' van-'.$this->type.'--horizontal">'.PHP_EOL;

            echo $this->indent(5);
            echo '<div';
            echo $this->wrap_output('class', $this->icon_class($item));
            echo '>'.PHP_EOL;

            echo '<i';
            echo $this->wrap_output('class', 'van-badge__wrapper van-icon van-icon-success');
            echo $this->wrap_output('style', $item['checked'] ? $this->disable_style() : null);
            echo '></i>' . PHP_EOL;

            echo $this->indent(5);
            echo '</div>';

            echo $this->indent(5);
            echo "<label class='van-{$this->type}__label'>";
            echo $item['text'];
            echo "</label>".PHP_EOL;

            echo $this->indent(4);
            echo "</div>".PHP_EOL;

            echo $this->indent(3);
            echo "</div>".PHP_EOL;
        }
        echo "{$space}</div>".PHP_EOL;
    }
}
