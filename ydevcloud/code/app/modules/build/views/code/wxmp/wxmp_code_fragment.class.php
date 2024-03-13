<?php
namespace app\modules\build\views\code\wxmp;


use app\modules\build\views\code\Base_Code_Fragment;

/**
 * vue 的代码编译处理，所有vue组件的代码只需往Vue_Code_Trait_View中放，
 * 然后通过Vue_Code_Trait_View的相关get方法返回对应的代码;
 *
 * vue SFC 代码组成可以分成如下几个部分：
 * <pre>
 * require
 * global
 * Page({
 *  data:
 *  function,
 *  lifecycle
 * })
 * </pre>
 * 以上所有方法，都将通过对应的接口进行设置
 */
class Wxmp_Code_Fragment extends Base_Code_Fragment {
    private $require = [];
    private $data = [];
    private $global = [];
    private $function = [];
    private $lifecycle = [];
    private $components = [];

    private function add_comment($comment){
        if (!$comment) return '';
        return "// {$comment}".PHP_EOL;
    }
    /**
     * <ul>
     * <li>用法举例：add_require('foo','../path/to/bar') = const foo = require('../path/to/bar') </li>
     * </ul>
     *
     * @param string $varName require名
     * @param array $path 路径
     */
    public function add_require(string $varName, string $path){
        $this->require[$varName] = $path;
    }

    /**
     * add_data('key', 'value') = data{ key: value }
     * add_data('key', [1,2,3]) = data{ key: [1,2,3] }
     * add_data('key', {a:[1,2,3]}) = data{ key: {a:[1,2,3]} }
     * @param string $key
     * @param $value
     */
    public function add_data(string $key, $value){
        $this->data[$key] = $value;
    }

    /**
     * add_global("const app = getApp();", "test") : <br/>
     * // test<br/>
     * const app = getApp();
     *
     * @param string $code  ref变量名
     * @param string $comment 代码注释
     */
    public function add_global(string $code, string $comment=""){
        $this->global[] = $this->add_comment($comment).$code;
    }

    public function add_component(string $componentName, string $componentPath){
        $this->components[$componentName] = $componentPath;
    }


    /**
     * @param string $functionName 自定义的函数名称,完整的签名格式，比如foo(a,b,c)
     * @param string $functionCodes 自定义函数代码体
     * @param string $comment 代码注释
     */
    public function add_function(string $functionName, string $functionCodes, string $comment=""){
        $this->function[$functionName] = $this->add_comment($comment).$functionCodes;

    }

    /**
     * @param string $lifecycleName 生命周期名称，比如onLoad
     * @param string $lifecycleCodes 生命周期代码体
     * @param string $comment 代码注释
     */
    public function add_lifecycle(string $lifecycleName, string $lifecycleCodes, string $comment=""){
        $this->lifecycle[$lifecycleName][] = $this->add_comment($comment)."{$lifecycleCodes}";
    }

    public function merge(Base_Code_Fragment $fragment){
        foreach (['require','global','data','function', 'components'] as $item){
            $methond = "get_".$item;
            $this->$item = array_merge($this->$item, $fragment->$methond());
        }
        foreach (['lifecycle'] as $item){
            $methond = "get_".$item;
            $this->$item = array_merge_recursive($this->$item, $fragment->$methond());
        }
    }
    /**
     * @return array [require名=>文件路径]
     */
    public function get_require(){
        return $this->require;
    }
    /**
     * @return array [事件名=>[完整的语句]]
     */
    public function get_lifecycle(){
        return $this->lifecycle;
    }
    /**
     * @return array [函数名=>完整的function语句]
     */
    public function get_function(){
        return $this->function;
    }

    /**
     * @return array [key=>value]
     */
    public function get_data(){
        return $this->data;
    }

    /**
     * @return array [完整的代码]
     */
    public function get_global(){
        return $this->global;
    }
    public function get_components(){
        return $this->components;
    }
}
