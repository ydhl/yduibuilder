<?php
namespace app\modules\build\views\code\web;
use app\modules\build\views\code\Base_Code_Fragment;

/**
 * vue 的代码编译处理，所有vue组件的代码只需往Vue_Code_Trait_View中放，
 * 然后通过Vue_Code_Trait_View的相关get方法返回对应的代码;
 *
 * </pre>
 * 以上所有方法，都将通过对应的接口进行设置
 */
class Vue_Code_Fragment extends Base_Code_Fragment {
    private $import = [];
    private $const = [];
    private $let = [];
    private $declare = [];
    private $components = [];
    private $ref = [];
    private $computed = [];
    private $lifecycle = [];
    private $emit = [];
    private $prop = [];
    private $codes=[];
    const SECTION_FUNCTION = 'function';

    private function add_comment($comment){
        if (!$comment) return '';
        return "{$comment}".PHP_EOL;
    }
    /**
     * <ul>
     * <li>用法举例：add_import('bootstrap') = import 'bootstrap' </li>
     * <li>用法举例：add_import('vue',['ref','computed']) = import {ref, comptuted} from 'vue' </li>
     * <li>用法举例：add_import('vue',['ref','computed'], 'Vue') = import {ref, comptuted}, Vue from 'vue' </li>
     * </ul>
     *
     * @param string $fromPackage 导入的模块
     * @param array $imports 其他导入内容，包含在{}中的，比如import {foo1, foo2} from bar
     * @param string $defaultImport 默认导入的内容，也就是没有{} 包含的，比如import foo from bar
     */
    public function add_import(string $fromPackage, array $imports=[], string $defaultImport=null){
        if (!$this->import[$fromPackage]) $this->import[$fromPackage] = [];
        if ($imports) {
            if (!$this->import[$fromPackage]['import']) $this->import[$fromPackage]['import'] = [];
            $this->import[$fromPackage]['import'] = array_unique(array_merge($this->import[$fromPackage]['import'], $imports));
        }
        if ($defaultImport) $this->import[$fromPackage]['default'] = $defaultImport;
    }
    public function add_const(string $constName, string $rightValue, $type='')
    {
        $this->const[$constName] = ['value'=>$rightValue,'type'=>$type];
    }
    public function add_let(string $constName, string $rightValue, $type='')
    {
        $this->let[$constName] = ['value'=>$rightValue,'type'=>$type];
    }
    public function add_emit(string $emit){
        $this->emit[] = $emit;
    }
    public function add_prop(string $name, string|array $type, $defaultValue=null){
        $this->prop[$name] = ['type'=>$type];
        if (isset($defaultValue)){
            $this->prop[$name]['default'] = $defaultValue;
        }
    }

    /**
     * addDeclare('const', '$', 'any')
     * @param string $type
     * @param string $keyword
     * @param string $keyType
     */
    public function add_declare(string $type, string $keyword, string $keyType){
        $this->declare[$keyword] = "declare {$type} {$keyword}: {$keyType}";
    }

    /**
     * @param string $compName 组件名
     * @param string $compPath 组件的地址
     */
    public function add_component(string $compName, string $compPath){
        if (in_array($compName, $this->components)) return;

        $this->components[] =  $compName;
        $this->add_import('@/'.$compPath, [], $compName);
    }

    /**
     * add_ref("foo", 1, true, "test") : <br/>
     * // test<br/>
     * const foo = ref(1)
     *
     * @param string $refName  ref变量名
     * @param string $refValue  ref变量值，也就是ref(变量值)
     * @param boolean $needReturn 是否需要通过return返回
     * @param string $comment 代码注释
     */
    public function add_ref(string $refName, $refValue, $refType='', string $comment=""){
        $this->add_import('vue', ['ref']);
        $this->ref[$refName] = $this->add_comment($comment)."const {$refName} = ref{$refType}("
            .(is_array($refValue) ? json_encode($refValue, JSON_UNESCAPED_UNICODE) : $refValue).")";
    }

    /**
     * add_computed("foo", "()=>store.foo.bar", true, "test") : <br/>
     * // test<br/>
     * const foo = computed(()=>store.foo.bar)
     * <br/><br/>
     * add_computed("foo", "{<br/>
     *  get:{ return store.foo.bar },<br/>
     *  set(v) : { store.foo.bar=v }<br/>
     * }", true, "test") : <br/>
     * // test<br/>
     * const foo = computed({<br/>
     *  get:{ return store.foo.bar },<br/>
     *  set(v) : { store.foo.bar=v }<br/>
     * })
     *
     * @param string $computedName 计算表达式的名称
     * @param string $computedCodes 计算表达式的代码体
     * @param boolean $needReturn 是否需要通过return返回
     * @param string $type 类型
     * @param string $comment 代码注释
     */
    public function add_computed(string $computedName, string $computedCodes, string $type=null, string $comment=""){
        $this->computed[$computedName] = $this->add_comment($comment)."const {$computedName} = computed".($type?:"")."({$computedCodes})";
    }

    /**
     * @param string $lifecycleName 生命周期名称，比如onMounted
     * @param string $lifecycleCodes 生命周期代码体
     * @param string $comment 代码注释
     */
    public function add_lifecycle(string $lifecycleName, string $lifecycleCodes, string $comment=""){
        $this->lifecycle[$lifecycleName][] = $this->add_comment($comment) . $lifecycleCodes;
    }

    public function merge(Base_Code_Fragment $fragment){
        foreach (['declare','components','ref','computed', 'emit', 'const', 'let'] as $item){
            $methond = "get_".$item;
            $this->$item = array_merge($this->$item, $fragment->$methond());
        }
        foreach ($fragment->get_import() as $package=>$imports){
            $this->import[$package]['default'] = $imports['default'];
            $this->import[$package]['import'] = $this->import[$package]['import'] ? array_merge($this->import[$package]['import'], $imports['import']) : $imports['import'];
        }
        foreach (['lifecycle'] as $item){
            $methond = "get_".$item;
            $this->$item = array_merge_recursive($this->$item, $fragment->$methond());
        }

        $this->codes = array_merge_recursive($this->codes, $fragment->get_codes());
    }
    /**
     * @return array [表达式名=>完整的ref语句]
     */
    public function get_ref(){
        return $this->ref;
    }
    /**
     * @return array [事件名=>[完整的语句]]
     */
    public function get_lifecycle(){
        return $this->lifecycle;
    }


    /**
     * @return array [表达式名=>完整的computed语句]
     */
    public function get_computed(){
        return $this->computed;
    }


    /**
     * @return array [组件名]
     */
    public function get_components(){
        return $this->components;
    }

    /**
     * @return array [关键字=>完整的declare语句]
     */
    public function get_declare(){
        return $this->declare;
    }

    /**
     * @return array [package=>['default'=>'默认导出','import'=>[导出的内容]]]
     */
    public function get_import(){
        return $this->import;
    }
    public function get_prop(){
        return $this->prop;
    }
    /**
     * @return array [constname=>rightvalue]
     */
    public function get_const(){
        return $this->const;
    }
    public function get_let(){
        return $this->let;
    }
    public function get_emit(){
        return array_unique($this->emit);
    }


    /**
     * 在section中添加代码，同一个section按调用顺序添加
     * @param $section string
     * @param $codes array | string
     * @return mixed
     */
    public function add_code($section, $codes){
        if (!$this->codes[$section]){
            $this->codes[$section] = [];
        }
        $this->codes[$section] = array_merge($this->codes[$section], (array)$codes);
    }

    public function get_codes(){
        return $this->codes;
    }
    public function get_section_codes($section){
        return $this->codes[$section];
    }
}
