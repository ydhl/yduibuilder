<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\bootstrap\Icon_View as Preview_Icon_View;

class Icon_View extends Preview_Icon_View {
    use Vue;
    public function build_code():Base_Code_Fragment
    {
        parent::build_code();
        $path = YZE_UPLOAD_PATH."project/{$this->build->get_project()->uuid}/iconfont";
        if (file_exists($path)){
            $this->get_code_fragment()->add_import('@/assets/iconfont/iconfont.css');
        }
        return $this->get_code_fragment();
    }
}
