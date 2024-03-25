<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Dropdown_View as Preview_Dropdown_View;

class Dropdown_View extends Preview_Dropdown_View{
    use Vue;
    public function build_code():Base_Code_Fragment
    {
        parent::build_code();
        $this->get_code_fragment()->add_import('bootstrap');
        return $this->get_code_fragment();
    }
}
