<?php
class vue_install{
    public static function getIcons(){
        return [];
    }


    public static function installInVue3() {
        return [
            'devDependencies'=>[],
            'dependencies'=>['lodash'=>"^4.17.21"]
        ];
    }
    /**
     * 在预览时，通过petitevue来实现逻辑，实际vue项目在编译成目标代码时不会使用petitevue，这里这时在线预览使用
     * @return ['iife'=>[],'es'=>[],''vendor'=>[]]
     */
    public static function jsForPreview() {
        return [
            'vendor'=>['jquery@3.5.1', 'layui@2.9.6', 'alpinejs@3.x.x']
        ];
    }
}
