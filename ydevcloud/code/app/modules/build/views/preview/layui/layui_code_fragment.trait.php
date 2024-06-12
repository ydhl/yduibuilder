<?php
namespace app\modules\build\views\preview\layui;
use app\build\Build_Model;
use app\modules\build\views\code\Base_Code_Fragment;
use app\project\Page_Model;
use phpseclib3\Crypt\EC\BaseCurves\Base;
use function yangzie\__;

/**
 * 该trait封装了layui的code的代码结构，layui的特点是layui.use用法
 */
class Layui_Code_Fragment extends Base_Code_Fragment {
    private $use=[];
    private $codes=[];
    /**
     * 用法举例：add_use('table')
     *
     * @param string $use 导入的模块
     */
    public function add_use(string $use){
        if (!in_array($use, $this->use)){
            $this->use[] = $use;
        }
    }
    public function add_code(string $codes){
        $this->codes[] = $codes;
    }

    /**
     * @return mixed
     */
    public function get_uses(){
        return $this->use;
    }
    public function get_codes(){
        return $this->codes;
    }
    public function merge(Base_Code_Fragment $fragment){
        $this->use = array_unique(array_merge($this->use, $fragment->get_uses()));
        $this->codes = array_merge($this->codes, $fragment->get_codes());
    }
}
