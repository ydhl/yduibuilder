<?php

namespace yangzie;

/**
 * graphql typename 内省处理
 */
trait Graphql__Typename{

    /**
     * 通过内省查询支持的类型
     * @param $node
     * @return array
     */
    public function __typename($node){
        return "YangzieQuery";
    }

}

?>
