<?php
class ydecloud_install{
    public static function install_vue3() {
        return [
            'devDependencies'=>[],
            'dependencies'=>[],
            // 这部分的文件会被编译到index.js中到{{globalFiles}}部分
            'globalFiles'=>[
                'js'=>[
                    'ydecloud-0.0.1.js',
                ]
            ]
        ];
    }
    /**
     * 预览时要包含的js
     * @return array ['file'=>'es'|'iife']
     */
    public static function jsForPreview() {
        return [
            'ydecloud-0.0.1.js'=>'iife'
        ];
    }
}
