<?php
class zepto_install{
    public static function getIcons(){
        return [
        ];
    }
    public static function installInVue3() {
        /**
         * 定于基于node，bootstrap的包名和对应的css如何包含
         */
        return [
        ];
    }

    /**
     * wxss中的内容是在app.wxss中通过@import导入
     * @return ['wxss'=>[需要导入的样式表及顺序], 'export'=>[要导出的文件列表]]
     */
    public static function installInWxmp2(){
        return [
            'wxss'=>[],
            'export'=>['./'=>['zepto.min.js']]
        ];
    }

    /**
     * 预览时要包含的js
     * @return ['iife'=>[],'es'=>[],''vendor'=>[]]
     */
    public static function jsForPreview() {
        return [
            'iife'=>['zepto.min.js']
        ];
    }
}
