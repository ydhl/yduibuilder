<?php
namespace app\modules\build\views\preview\weui;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Page_View as Bootstrap_Page_View;
use app\modules\build\views\preview\Html_Code_Fragment;

class Page_View extends Bootstrap_Page_View {
    protected function style_map($meta=null, $state = 'normal')
    {
        $map = parent::style_map($meta, $state);
        if (!@$map['height'] && $this->data['pageType']!='popup' && !$this->build->is_subpage()){
            $map['height']= 'height:100vh';
        }

        return $map;
    }
    public function build_code():Base_Code_Fragment{
       $fragment = parent::build_code();
       if (!$this->build->is_subpage()){
           $fragment->add_code(Html_Code_Fragment::SECTION_INIT, 'document.body.setAttribute("data-weui-theme","light");');
       }
       return $fragment;
    }
}
