<?php

/**
 * 封装前端的基础处理
 */
class ydecloud_install{
    public static function installInVue3() {
        return [
            'devDependencies'=>[],
            'dependencies'=>[],
            // 这部分的文件会被编译到index.js中到{{globalFiles}}部分
            'globalFiles'=>[
                'js'=>[
                    'ydecloud-vue-0.0.1.js',
                ]
            ]
        ];
    }
    /**
     * 生成html5代码需要包含的前端文件
     * @return array ['iife'=>[],'es'=>[],''vendor'=>[]]
     */
    public static function installInHtml5() {
        return [
            'iife'=>['ydecloud-0.0.1.js']
        ];
    }
    /**
     * 预览时要包含的js
     * @return array ['iife'=>[],'es'=>[],''vendor'=>[]]
     */
    public static function jsForPreview() {
        return [
            'iife'=>['ydecloud-0.0.1.js']
        ];
    }
}
