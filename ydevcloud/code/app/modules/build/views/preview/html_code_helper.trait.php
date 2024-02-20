<?php
namespace app\modules\build\views\preview;

use app\modules\build\views\code\Base_Code_Fragment;

/**
 * 该trait处理编译预览代码逻辑
 */
trait Html_Code_Helper {
    /**
     * @var Html_Code_Fragment
     */
    private $codeFragment;

    /**
     * @return Html_Code_Fragment
     */
    public function get_code_Fragment():Base_Code_Fragment{
        if (!$this->codeFragment) $this->codeFragment = new Html_Code_Fragment();
        return $this->codeFragment;
    }
}
