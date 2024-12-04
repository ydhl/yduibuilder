<?php

/**
 * 封装前端的基础处理
 */
class lodash_install{
    public static function installInVue3() {
        return [
            'devDependencies'=>[],
            'dependencies'=>['lodash'=>"^4.17.21"]
        ];
    }
    /**
     * 生成html5代码需要包含的前端文件
     * @return array ['iife'=>[],'es'=>[],''vendor'=>[]]
     */
    public static function installInHtml5() {
        return [
            'iife'=>['lodash.js']
        ];
    }
    /**
     * 预览时要包含的js
     * @return array ['iife'=>[],'es'=>[],''vendor'=>[]]
     */
    public static function jsForPreview() {
        return [
            'iife'=>['lodash.js']
        ];
    }
}
