<?php
namespace app\modules\build\views\preview\weui;


use app\build\Build_Model;
use app\project\Page_Model;
use app\project\Project_Setting_Model;
use app\vendor\Env;
use function yangzie\__;

/**
 * 预览下重写bootstrap popup和代码输出逻辑； 对于html5框架，输出的代码和预览一致
 */
trait Weui_Popup {
    public function build_popup_ui(&$outputPopupIds=[]){
        // 通过ajax加载弹窗，不输出弹窗模版
    }
    /**
     * 弹窗页面预览地址
     * @param $page
     * @return string
     */
    protected function get_popup_page_url($page) {
        if ($page->page_type=='popup'){
            // 弹窗加载时显示上完整的html结构
            return '/preview/popup/'.$page->uuid;
        }else{
            return '/preview/page/'.$page->uuid;
        }
    }

}
