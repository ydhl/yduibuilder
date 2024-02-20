<?php
namespace app\modules\build\views\preview;
use app\build\Build_Model;
use app\modules\build\views\code\Base_Code_Fragment;
use app\project\Page_Model;
use phpseclib3\Crypt\EC\BaseCurves\Base;
use function yangzie\__;

/**
 * 该trait封装了html的code的代码结构
 */
class Html_Code_Fragment extends Base_Code_Fragment {
    private $codes=[];
    public function add_code($codes){
        $this->codes = array_merge($this->codes, (array)$codes);
    }

    public function get_codes(){
        return $this->codes;
    }
    public function merge(Base_Code_Fragment $fragment){
        $this->codes = array_merge($this->codes, $fragment->get_codes());
    }
}
