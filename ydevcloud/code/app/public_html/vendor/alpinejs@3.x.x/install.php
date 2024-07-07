<?php
class alpinejs_install{
    public static function getIcons(){
        return [];
    }

    /**
     * 预览时要包含的js
     * @return array ['iife'=>[],'es'=>[],''vendor'=>[]]
     */
    public static function jsForPreview() {
        return [
            'es'=>['alpinejs.es.js'=>'import Alpine'],
            'iife'=>['alpinejs-util.js']
        ];
    }
}
