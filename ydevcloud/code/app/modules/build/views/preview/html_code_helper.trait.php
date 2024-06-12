<?php
namespace app\modules\build\views\preview;

use app\modules\build\views\code\Base_Code_Fragment;

/**
 * 获取html框架下的代码fragment对象, 目前采用alpine，如果需要用其他的，修改这里的代码即可，ui组件中不用修改
 */
trait Html_Code_Helper {
    use Alpine;
    /**
     * @var Html_Code_Fragment
     */
    private $codeFragment;

    /**
     * @return Html_Code_Fragment
     */
    public function get_code_Fragment():Base_Code_Fragment{
        if (!$this->codeFragment) $this->codeFragment = new Alpine_Code_Fragment();
        return $this->codeFragment;
    }
}
