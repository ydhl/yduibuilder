<?php
class jquery_install{
    public static function install_vue3() {
        return [
            'devDependencies'=>["@types/jquery"=>"^3.5.4"],
            'dependencies'=>['jquery'=>"^3.5.1"],
            // 这部分的文件会被编译到index.js中到{{globalFiles}}部分
            'globalFiles'=>[
                'js'=>[
                    'jquery-3.5.1.min.js',
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
            'jquery-3.5.1.min.js'=>'iife'
        ];
    }
}
