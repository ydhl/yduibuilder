<?php
namespace app\modules\build\views\preview\layui;

use app\modules\build\views\code\Base_Code_Fragment;
use app\modules\build\views\preview\Alpine;

/**
 * 该trait封装了layui的code的代码结构，layui的特点是layui.use用法
 */
trait Layui_Code_Helper {
    use Alpine;
    /**
     * @var Layui_Code_Fragment
     */
    private $codeFragment;

    /**
     * @return Layui_Code_Fragment
     */
    public function get_code_Fragment():Base_Code_Fragment{
        if (!$this->codeFragment) $this->codeFragment = new Layui_Code_Fragment();
        return $this->codeFragment;
    }
}
