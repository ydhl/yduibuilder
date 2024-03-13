<?php
namespace app\modules\build\views\code\wxmp;


/**
 * 弹窗处理
 */
trait Wxmp_Popup {

    public function build_popup_ui(&$outputPopupIds=[]){
        if (@$this->data['events']){
            foreach($this->data['events'] as $eventName => $eventBinds){
                foreach ($eventBinds as $bindId => $eventBind) {
                    $action = $eventBind['action'];
                    $popupName = $this->myId()."_".$bindId;
                    if (strtoupper($action['type']) == 'POPUP'){
                        $pageId = $action['popupPageId'];
                        $popupPage = $this->get_page($pageId);
                        if ($popupPage && $popupPage->page_type=='popup'){
                            $this->get_code_fragment()->add_data("{$popupName}Visible", false);
                            $this->get_code_fragment()->add_component($popupName, '/'.$popupPage->get_save_path('wxmp'));
                            echo "<{$popupName} wx:if='{{{$popupName}Visible}}'></{$popupName}>\r\n";
                        }
                    }
                }
            }
        }
        foreach ($this->childViews as $view){
            $view->build_popup_ui($outputPopupIds);
        }
    }
}
