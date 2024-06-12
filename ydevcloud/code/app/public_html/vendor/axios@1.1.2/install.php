<?php
class axios_install{
    public static function installInVue3() {
        return [
            'devDependencies'=>[],
            'dependencies'=>['axios'=>"^1.1.2"],
            // 这部分的文件会被编译到index.js中到{{globalFiles}}部分
            'globalFiles'=>[]
        ];
    }
    /**
     * 预览时要包含的js
     * @return array ['iife'=>[],'es'=>[],''vendor'=>[]]
     */
    public static function jsForPreview() {
        return [
            'iife'=>['axios.min.js']
        ];
    }
}
