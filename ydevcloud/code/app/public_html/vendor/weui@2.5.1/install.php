<?php
class weui_install{
    public static function getIcons(){
        return [
            'weui-icon-success',
            'weui-icon-info',
            'weui-icon-warn',
            'weui-icon-waiting'
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
     * @return ['wxss'=>[需要导入的样式表及顺序], 'export'=>[目标目录=>[导出文件]]]
     */
    public static function installInWxmp2(){
        return [
            'wxss'=>[
                "weui.wxss",
                "ext-button.wxss",
                "ext-breadcrumb.wxss",
                "ext-progress.wxss",
                "ext-navbar.wxss",
                "ext-layout.wxss",
                "ext-padding-margin.wxss",
                "ext-checkbox.wxss",
                "ext-cell.wxss",
                "ext-text.wxss",
                "ext-misc.wxss",
                "ext-border.wxss",
                "ext-responsive.wxss"
            ],
            'export'=>[
                './'=>[
                    'export/ext-breadcrumb.wxss',
                    'export/ext-button.wxss',
                    'export/ext-cell.wxss',
                    'export/ext-checkbox.wxss',
                    'export/ext-layout.wxss',
                    'export/ext-misc.wxss',
                    'export/ext-navbar.wxss',
                    'export/ext-padding-margin.wxss',
                    'export/ext-progress.wxss',
                    'export/ext-responsive.wxss',
                    'export/ext-select.wxss',
                    'export/ext-text.wxss',
                    'export/ext-border.wxss',
                    'export/weui.wxss'
                ]
            ]
        ];
    }

    /**
     * 预览时要包含的js
     * @return ['iife'=>[],'es'=>[],''vendor'=>[]]
     */
    public static function jsForPreview() {
        return [
            'iife'=>['plugin/jquery-1.9.1.min.js','plugin/util.js','plugin/carousel.js','weui.min.js'],
            'vendor'=>['alpinejs@3.x.x']
        ];
    }
}
