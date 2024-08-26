<?php
namespace app\modules\build\views\preview\vant;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Alpinejs_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\ValueList_View;
use phpseclib3\Math\BigInteger\Engines\PHP;

/**
 * <pre>
 *  <div role="radiogroup">
 *      <div role="radio" tabindex="0" aria-checked="true" class="van-radio">
 *          <div class="van-radio__icon van-radio__icon--round van-radio__icon--checked">
 *              <i class="van-icon van-icon-success"><!----></i>
 *          </div>
 *          <span class="van-radio__label">单选框 1</span>
 *      </div>
 *  </div>
 * </pre>
 */
class Radio_View extends ValueList_View {
    use Vant_Popup,Html_Code_Helper, Alpine{
        Alpine::build_code as alpineBuildCode;
    }
    protected $type = 'radio';

    protected function build_ui_begin($iteratorName = null)
    {
        $space =  $this->indent();
        echo "{$space}<div";
        echo $this->wrap_output('role', $this->type.'group');
        $this->build_main_attrs();
        echo ">".PHP_EOL;
    }
    protected function build_ui_end()
    {
        $space =  $this->indent();
        echo "{$space}</div>".PHP_EOL;
    }
    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex=null, $iteratorName='')
    {
        list('name'=>$name, 'value'=>$value, 'checked'=>$checked, 'data'=>$boundData) = $this->get_bind_name_value($outputData, $itemName);
        $staticValue = strlen($staticData['value'])?$staticData['value']:$staticData['name'];
        $myid = $this->myid();
        $inputDataName = $this->get_input_data_name($inputIsArr);

        echo $this->indent(1);
        echo '<div';
        echo $this->wrap_output('role', $this->type);
        echo $this->wrap_output('class', "van-{$this->type} van-{$this->type}--horizontal");
        echo $this->wrap_output('data-root', $myid);
        echo $this->wrap_output('data-uiid', $this->myId().$this->type);
        echo $this->wrap_output('@click', $this->myId().'_update_checked($el)');
        if ($value){
            echo $this->wrap_output(':data-value', $value);
            echo $this->wrap_output('data-bound', $boundData);
            echo $this->wrap_output(':data-checked', "alpinejs_in_array(\$el, '{$inputDataName}', {$value})");
            echo $this->wrap_output(':data-default', $checked ? "{$checked} ? {$value} : ''" : null);
        }else{
            echo $this->wrap_output('data-value',$staticValue );
            echo $this->wrap_output(':data-checked', "alpinejs_in_array(\$el, '{$inputDataName}', '{$staticValue}')");
            echo $this->wrap_output('data-default', $staticData['checked'] ? $staticValue : null);
        }
        echo '>'.PHP_EOL;

        echo $this->indent(2);
        echo '<div';
        echo $this->wrap_output(':class', $this->icon_class($inputDataName, $value, $staticValue));
        echo '>'.PHP_EOL;

        echo $this->indent(2);
        echo '<i';
        echo $this->wrap_output(':class', $this->i_class($inputDataName, $value, $staticValue));
        echo $this->wrap_output(':style', $this->i_style($inputDataName, $value, $staticValue));
        echo '></i>' . PHP_EOL;

        echo $this->indent(2);
        echo '</div>' . PHP_EOL;

        echo $this->indent(2);
        echo '<span class="van-'.$this->type.'__label"';
        echo $this->wrap_output('x-text', $name ?: NULL);
        echo '>'.$staticData['name'].'</span>'.PHP_EOL;

        echo $this->indent(1);
        echo "</div>".PHP_EOL;
    }
    public function build_code(): Base_Code_Fragment
    {
        $fragment = $this->alpineBuildCode();
        $myid = $this->myid();
        $checkUpdate = <<<CHECK_UPDATE
{$myid}_update_checked(el){
    if (el.dataset.checked){
        el.removeAttribute('data-checked')
    }else{
        el.dataset.checked = 'true'
    }
},
CHECK_UPDATE;
        $radioUpdate = <<<RADIO_UPDATE
{$myid}_update_checked(el){
    el.dataset.checked = 'true'
},
RADIO_UPDATE;
        $fragment->add_code(Alpinejs_Code_Fragment::SECTION_EVENT, $this->build->indent_code(0, $this->type == 'radio' ? $radioUpdate : $checkUpdate));
        return $fragment;
    }

    protected function css_map() {
        $map = parent::css_map();
        $style = parent::style_map();
        $map['-'] = 'van-h-auto';
        if (@$this->data['meta']['form']['state'] == 'hidden'){
            $map['-'] .= ' van-d-none';
        }
        if ($style['color']) unset($map['foregroundTheme']);
        if ($style['background-color']) unset($map['backgroundTheme']);
        $map['-'] .= 'van-'.$this->type.'-group van-'.$this->type.'-group--horizontal';
        return $map;
    }

    private function icon_class($inputDataName, $value, $staticValue){
        $checkValue = $value?:"'{$staticValue}'";
        $icon = 'van-'.$this->type.'__icon '.($this->type=='radio' ? 'van-radio__icon--round' : 'van-checkbox__icon--square');
        $_ = ["'{$icon}': true"];
        $_[] = "'van-{$this->type}__icon--checked':alpinejs_in_array(\$el, '{$inputDataName}', {$checkValue})";

        return "{".join(',',$_)."}";
    }
    private function i_style($inputDataName, $value, $staticValue)
    {
        $disabled = in_array($this->data['meta']['form']['state'], ['readonly','disabled']);
        $checkValue = $value?:"'{$staticValue}'";
        if ($disabled){
            return "alpinejs_in_array(\$el, '{$inputDataName}', {$checkValue}) ? 'border-color:var(--van-gray-6);background-color:var(--van-gray-6)' : 'border-color:var(--van-gray-6);'";
        }
        $color = $this->data['meta']['style']['color'];
        if (!$color) return null;
        return "alpinejs_in_array(\$el, '{$inputDataName}', {$checkValue}) ? 'border-color:{$color};background-color:{$color}' : 'border-color:{$color}'";
    }
    private function i_class($inputDataName, $value, $staticValue)
    {
        $css = ["'van-badge__wrapper van-icon van-icon-success': true"];
        $checkValue = $value?:"'{$staticValue}'";
        $color = $this->data['meta']['style']['color'];
        $foregroundTheme = $this->data['meta']['css']['foregroundTheme'];

        if (!$color) {
            $borderColorCss = $this->cssTranslate['borderColorClass'][$foregroundTheme];
            $backgroundTheme = $this->cssTranslate['backgroundTheme'][$foregroundTheme];
            $css[] = "'{$borderColorCss}':!alpinejs_in_array(\$el, '{$inputDataName}', {$checkValue})";
            $css[] = "'{$borderColorCss} $backgroundTheme':alpinejs_in_array(\$el, '{$inputDataName}', {$checkValue})";
        }
        return "{".join(',', $css)."}";
    }

}
