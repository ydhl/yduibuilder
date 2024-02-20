<?php
namespace app\modules\build\views\preview;


use app\build\Build_Model;

use app\project\Page_Model;
use yangzie\YZE_JSON_View;
use function yangzie\__;

/**
 * 预览下重载事件绑定的代码
 */
trait Html_Event_Binding {

    /**
     * 返回ydecloud对应事件名在html5上对应的事件名
     * @param $eventName
     * @return string
     */
    protected function eventMap($eventName){
        return[
            'onload' => 'load',
            'onready' => '',
            'onshow' =>'',
            'onhide'=>'',
            'onbeforeunload'=>'',
            'onunload'=>'',
            'onresize'=>'',
            'onscroll'=>'',
            'onclick'=>'click',
            'ondblclick'=>'dblclick',
            'onpulldown'=>'',
            'onreachbottom'=>''
        ][strtolower($eventName)];
    }


    /**
     * 事件绑定代码
     */
    public function build_event_binding_code(){

    }
}
