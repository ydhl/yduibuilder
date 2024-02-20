<?php
class axios_install{
    public static function install_vue3() {
        return [
            'devDependencies'=>[],
            'dependencies'=>['axios'=>"^1.1.2"],
            // 这部分的文件会被编译到index.js中到{{globalFiles}}部分
            'globalFiles'=>[]
        ];
    }
    /**
     * 预览时要包含的js
     * @return array ['file'=>'es'|'iife']
     */
    public static function jsForPreview() {
        return [
            'axios.min.js'=>'iife'
        ];
    }
}
