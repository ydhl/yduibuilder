<?php
namespace app\modules\build\views\code;


abstract class Base_Code_Fragment{
    public abstract function merge(Base_Code_Fragment $fragment);

    /**
     * 在section中添加代码，同一个section按调用顺序添加
     * @param $section string
     * @param $codes array | string
     * @return mixed
     */
    public abstract function add_code($section, $codes);
    public abstract function get_section_codes($section);
    public abstract function get_codes();
}
