<?php
class vue_install{
    public static function getIcons(){
        return [];
    }


    public static function install_vue3() {
        return [];
    }
    /**
     * 在预览时，通过petitevue来实现逻辑，实际vue项目在编译成目标代码时不会使用petitevue，这里这时在线预览使用
     */
    public static function jsForPreview() {
        return [
            '../petitevue@0.4.0/petite-vue.es.js'=>'import {createApp}'
        ];
    }
}
