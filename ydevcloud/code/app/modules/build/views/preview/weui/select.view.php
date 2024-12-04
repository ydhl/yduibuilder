<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Html_Code_Helper;
use app\modules\build\views\preview\Preview_View;
use app\modules\build\views\preview\ValueList_View;

/**
 * <pre>
 * <div class="weui-cell weui-cell_active weui-cell_select">
 *  <div class="weui-cell__bd"><div class="weui-select">日期</div></div>
 * </div>
 * </pre>
 */
class Select_View extends ValueList_View {
    use Weui_Popup,Html_Code_Helper, Alpine{
        Alpine::build_code as alpineBuildCode;
    }
    protected function build_valuelist($outputData, $itemName, $staticData = null, $staticDataIndex = null, $iteratorName='')
    {
        // 列表通过弹窗弹出
    }

    public function build_ui_begin($iteratorName=null)
    {
        $space =  $this->indent();
        $myid = $this->myid();
        $inputDataName = $this->get_input_data_name();

        echo "{$space}<div";
        $this->output_main_attrs();
        echo $this->wrap_output('@click', "open_{$myid}_menu");
        echo ">".PHP_EOL;

        echo $this->indent(1);
        echo '<div class="weui-cell__bd"><div'
            .$this->wrap_output('class', $this->front_class())
            .$this->wrap_output('style', $this->front_style());
        $text = $this->default_value();

        if ($iteratorName) {
            echo $this->wrap_output('x-text', "alpinejs_checked_name(\$el, {$iteratorName}, '{$inputDataName}') || '{$text}'");
        }else{
            echo $this->wrap_output('x-text', "alpinejs_checked_name(\$el, {$myid}_values(), '{$inputDataName}') || '{$text}'");
        }

        echo '></div></div>'.PHP_EOL;
    }
    public function build_ui_end()
    {
        $space =  $this->indent();
        echo "{$space}</div>".PHP_EOL;
    }

    function build_code(): Base_Code_Fragment
    {
        $fragment = $this->alpineBuildCode();
        $myid = $this->myid();
        $inputDataName = $this->get_input_data_name();

        $trigger = <<<TRIGGER
open_{$myid}_menu(){
    const page = this
    const menus = [];
    for(const item of this.{$myid}_values()){
        const menu = { label: "", value:"", disabled: false }
        if (typeof item === "object"){
            menu.label = item.hasOwnProperty("name") ? item.name : JSON.stringify(item)
            menu.value = item.hasOwnProperty("value") ? item.value : JSON.stringify(item)
        }else{
            menu.label = item
            menu.value = item
        }
        menus.push(menu)
    }
    weui.picker(menus, {
        defualtValue: [page.{$inputDataName}],
        onConfirm: function (rst) {
            page.{$inputDataName} = rst[0].value
        },
        id:"{$myid}"
    });
},
TRIGGER;
        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $this->build->indent_code(0, $trigger));

        return $fragment;
    }

    protected function css_map()
    {
        $map = Preview_View::css_map();
        $css = ['weui-cell weui-cell_active weui-cell_select d-overflow-hidden'];
        $styleInfo = Preview_View::style_map();
        if ($this->data['meta']['custom']['borderless']){
            $css[] = 'border-0';
        }
        if ($this->data['meta']['form']['state'] == 'disabled'){
            $css[] = 'disabled';
        }
        if ($this->data['meta']['form']['state'] == 'readonly'){
            $css[] = 'readonly';
        }
        if ($styleInfo['background-color']){
            unset($map['backgroundTheme']);
        }
        $map['-'] = join(' ', $css);
        return $map;
    }
    private function front_class(){
      $css = ['weui-select'];
      $cssInfo = Preview_View::css_map();
      $styleInfo = Preview_View::style_map();
      if ($cssInfo['foregroundTheme'] && !$styleInfo['color']) $css[] = $cssInfo['foregroundTheme'];
      return join(' ', $css);

    }
    private function front_style(){
      $style = [];
      $styleInfo = Preview_View::style_map();
      if ($styleInfo['color']) $style[] = $styleInfo['color'];
      return join(';', $style);
    }

}
