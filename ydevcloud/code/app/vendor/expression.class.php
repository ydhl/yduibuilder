<?php
namespace app\vendor;
use \yangzie\YZE_Model;
use yangzie\YZE_Object;
use \yangzie\YZE_SQL;
use \yangzie\YZE_DBAException;
use \yangzie\YZE_DBAImpl;
use \app\project\Project_Model;

/**
 *
 *
 * @version $Id$
 * @package project
 */
class Expression extends YZE_Object {
    const TYPE_LITERAL = 'literal';
    const TYPE_CONNECT = 'connect';
    const TYPE_EXPRESSION = 'expression';
    const TYPE_EXPRESSION_GROUP = 'expression_group';
    const TYPE_OPERATOR = 'operator';
    const TYPE_TERNARY = 'ternary';
    public $type;
    /**
     * @var  Expression_Data;
     */
    public $data;
    /**
     * @var Expression_Data;
     */
    public $rightData;
    public $operator;
    public $modifier;
    public $literal;
    public $desc;
    /**
     * @var array<Expression>
     */
    public $subexpression;
    /**
     * @var Expression
     */
    public $expression;
    /**
     * @var Expression
     */
    public $rightExpression;
    /**
     * @var Expression
     */
    public $trueExpression;
    /**
     * @var Expression
     */
    public $falseExpression;

    public function __construct(array $expression) {
        foreach ($expression as $name => $value) {
            if (in_array($name, ['data', 'rightData'])){
                if ($value) $this->$name = new Expression_Data($value);
            }else if (in_array($name, ['expression','rightExpression','trueExpression','falseExpression'])){
                if ($value) $this->$name = new Expression($value);
            }else if ($name == 'subexpression'){
                $this->subexpression = [];
                foreach ($value as $exp){
                    if($exp) $this->subexpression[] = new Expression($exp);
                }
            }else{
                $this->$name = $value;
            }
        }
    }

    /**
     * 表达式代码
     *
     * @param $translate_expression_call boolean 是否把代码中的表达式调用加上调用符号()
     * @return string|null
     */
    public function get_expression_code($translate_expression_call=true){
        switch (strtolower($this->type)){
            case 'literal': return $this->literal;
            case 'code': return $this->code;
            case 'connect':{
                $dataPath = $this->data->path;
                if ($this->data->isExpression && $translate_expression_call){
                    $dataPath = preg_replace("/page\.(\w+)/", 'page.\\1()', $dataPath);
                }
                return $dataPath;
            }
            case 'operator':return $this->operator;
            case 'expression':return $this->get_condition_expression_code();
            case 'expression_group':{
                $code = [];
                if ($this->modifier) $code[] = preg_replace("/@/", '', $this->modifier);
                if ($this->subexpression) $code[] = '(';

                foreach ($this->subexpression as $sub){
                    $code[] = $sub->get_expression_code($translate_expression_call);
                }

                if ($this->subexpression) $code[] = ')';
                return join(' ', $code);
            }
            case 'ternary':
            {
                $code = [];
                $code[] = $this->expression ? $this->expression->get_expression_code($translate_expression_call) : $this->get_condition_expression_code($translate_expression_call);
                $code[] = '?';
                $code[] = $this->trueExpression->get_expression_code($translate_expression_call);
                $code[] = ':';
                $code[] = $this->falseExpression->get_expression_code($translate_expression_call);
                return join(' ', $code);
            }
        }
        return null;
    }
    private function replace_modifer($data, $modifier=''){
        $rst = $data;
        if ($modifier && preg_match("/@/", $modifier)){
            return preg_replace("/@/", $rst, $modifier);
        }else{
            return $rst;
        }
    }
    private function get_condition_expression_code($translate_expression_call=false) {
        $code = [];
        if ($this->expression) {
            $code[] = $this->expression->get_expression_code($translate_expression_call);
        }else if ($this->data){
            $code[] = $this->replace_modifer($this->data->path, $this->data->modifier) ?: $this->data->literal;
        }

        if ($this->operator)$code[] = $this->operator;

        if ($this->rightExpression) {
            $code[] = $this->rightExpression->get_expression_code($translate_expression_call);
        }else if ($this->rightData){
            $code[] = $this->replace_modifer($this->rightData->path, $this->rightData->modifier) ?: $this->rightData->literal;
        }

        return join(' ', $code);
    }
}?>
