<?php
class petitevue_install{
    public static function getIcons(){
        return [];
    }

    /**
     * 预览时要包含的js
     * @return array ['file'=>'es'|'iife']
     */
    public static function jsForPreview() {
        return [
            'petite-vue.es.js'=>'import {createApp}'
        ];
    }
}
