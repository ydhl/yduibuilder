<?php
namespace app\modules\build\views\preview\weui;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Select_View as Bootstrap_Select_View;
use app\modules\build\views\preview\Html_Code_Fragment;
use app\modules\build\views\preview\Preview_View;

class Select_View extends Bootstrap_Select_View {
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

    public function build_ui_begin($is2d = false, $outDataName = '')
    {
        $space =  $this->indent();
        $myid = $this->myid();

        echo "{$space}<div";
        echo $this->build_main_attrs();
        echo $this->wrap_output('@click', "open_{$myid}_menu");
        echo ">".PHP_EOL;
    }
    public function build_ui_end($is2d = false, $outDataName = '')
    {
        $space =  $this->indent();
        echo "{$space}</div>".PHP_EOL;
    }

    public function build_ui_static()
    {
        $this->build_ui_begin();

        echo $this->indent(1);
        echo '<div class="weui-cell__bd"><div'
            .$this->wrap_output('class', $this->front_class())
            .$this->wrap_output('style', $this->front_style())
            .$this->wrap_output('x-text', $this->myid()."_checkedName")
            .'></div></div>'.PHP_EOL;

        $this->build_ui_end();
    }
    protected function build_select($bindOutput, $outDataName, $is2d){
        $this->build_ui_static();
    }

    function build_code(): Base_Code_Fragment
    {
        $fragment = parent::build_code();
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
        $fragment->add_code(Html_Code_Fragment::SECTION_EVENT, $this->build->indent_code(1, $trigger));

        return $fragment;
    }
}
