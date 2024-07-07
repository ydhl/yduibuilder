<?php
namespace app\modules\build\views\preview;
use app\build\Build_Model;
use app\modules\build\views\code\Base_Code_Fragment;
use app\project\Page_Model;
use phpseclib3\Crypt\EC\BaseCurves\Base;
use function yangzie\__;

/**
 * 该trait封装了使用alpine实现html的代码结构
 */
class Alpinejs_Code_Fragment extends Html_Code_Fragment {
    private $codes=[];
    private $subPage_modules=[];
    public function add_code($section, $codes){
        if (!$this->codes[$section]){
            $this->codes[$section] = [];
        }
        $this->codes[$section] = array_merge($this->codes[$section], (array)$codes);
    }

    /**
     * @param $pageName string 子页面名称
     * @param $pagePath string 页面加载路径
     * @param $inputData array [子页面参数名=>主页面参数名]
     * @return void
     */
    public function add_subpage_module($pageName, $pagePath, $inputData=[]){
        $this->subPage_modules[$pageName] = [
            'path'=>$pagePath,
            'input'=>$inputData
        ];
    }

    public function get_codes(){
        return $this->codes;
    }
    public function get_section_codes($section){
        return $this->codes[$section];
    }
    public function get_subpage_modules(){
        return $this->subPage_modules;
    }
    public function merge(Base_Code_Fragment $fragment){
        $this->codes = array_merge_recursive($this->codes, $fragment->get_codes());
        $this->subPage_modules = array_merge($this->subPage_modules, $fragment->get_subpage_modules());
    }
}
