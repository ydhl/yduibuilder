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
     * @param $dataConfig array
     * @param $hidePrefix boolean
     * @return string|null
     */
    public function get_expression_code($hidePrefix=false){
        switch (strtolower($this->type)){
            case 'literal': return $this->literal;
            case 'code': return $this->code;
            case 'connect':return $this->remove_prefix($this->data->path, $hidePrefix);
            case 'operator':return $this->operator;
            case 'expression':return $this->get_condition_expression_code($hidePrefix);
            case 'expression_group':{
                $code = [];
                if ($this->modifier) $code[] = preg_replace("/@/", '', $this->modifier);
                if ($this->subexpression) $code[] = '(';

                foreach ($this->subexpression as $sub){
                    $code[] = $sub->get_expression_code($hidePrefix);
                }

                if ($this->subexpression) $code[] = ')';
                return join(' ', $code);
            }
            case 'ternary':
            {
                $code = [];
                $code[] = $this->expression ? $this->expression->get_expression_code($hidePrefix) : $this->get_condition_expression_code($hidePrefix);
                $code[] = '?';
                $code[] = $this->trueExpression->get_expression_code($hidePrefix);
                $code[] = ':';
                $code[] = $this->falseExpression->get_expression_code($hidePrefix);
                return join(' ', $code);
            }
        }
        return null;
    }
    private function remove_prefix($data, $hidePrefix, $modifier=''){
        $rst = '';
        if (!$hidePrefix) {
            $rst = $data;
        }else{
            $rst = preg_replace('/^page\./','', $data);
        }
        if ($modifier && preg_match("/@/", $modifier)){
            return preg_replace("/@/", $rst, $modifier);
        }else{
            return $rst;
        }
    }
    private function get_condition_expression_code($hidePrefix) {
        $code = [];
        if ($this->expression) {
            $code[] = $this->expression->get_expression_code();
        }else if ($this->data){
            $code[] = $this->remove_prefix($this->data->path, $hidePrefix, $this->data->modifier) ?: $this->data->literal;
        }

        if ($this->operator)$code[] = $this->operator;

        if ($this->rightExpression) {
            $code[] = $this->rightExpression->get_expression_code();
        }else if ($this->rightData){
            $code[] = $this->remove_prefix($this->rightData->path, $hidePrefix, $this->rightData->modifier) ?: $this->rightData->literal;
        }

        return join(' ', $code);
    }
}?>
