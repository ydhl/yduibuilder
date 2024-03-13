<?php
namespace app\modules\build\views\code\wxmp;


use app\build\Build_Model;
use app\project\Page_Model;
use function yangzie\__;

/**
 * 事件绑定的代码
 */
trait Wxmp_Event_Binding {

    /**
     * 返回ydecloud对应事件名在微信小程序上对应的事件名
     * @param $eventName
     * @return string
     */
    protected function eventMap($eventName){
        return[
            'onload' => '',
            'onready' => '',
            'onshow' =>'',
            'onhide'=>'',
            'onbeforeunload'=>'',
            'onunload'=>'',
            'onresize'=>'',
            'onscroll'=>'',
            'onclick'=>'tap',
            'ondblclick'=>'',
            'onpulldown'=>'',
            'onreachbottom'=>''
        ][strtolower($eventName)];
    }
    private function _popup_event_binding($eventName, $eventBind){
        $action = $eventBind['action'];
        $pageId = $action['popupPageId'];
        $page = $this->get_page($pageId);
        $page_type = strtoupper($page->page_type);

        if ($page_type == 'POPUP'){// 加载modal
?>
this.setData({
    <?= $this->myId()."_".$eventBind['id']?>Visible: true
});
<?php
        }else{
            if (!$pageId){?>
wx.showToast({title: "<?= __("You not setting popup page, you can open event panel to setting")?>"});
<?php       }else{?>
wx.navigateTo({
    url: "/<?= $page->get_save_path('wxmp') ?>"
});
<?php }
        }
    }
    private function _call_event_binding($eventName, $eventBind){

    }

    /**
     * 小程序事件绑定代码
     */
    public function build_event_binding_code(){
        $events = @$this->data['events'];
        if (!$events){
            return ;
        }
        foreach($events as $eventName => $eventBinds){
            $sortEventName = $this->eventMap($eventName);
            ob_start();
            foreach ($eventBinds as $bindId => $eventBind) {
                $action = $eventBind['action'];
                switch (strtoupper($action['type'])){
                    case 'POPUP': $this->_popup_event_binding($eventName, $eventBind);break;
                    case 'CALL': $this->_call_event_binding($eventName, $eventBind);break;
                }
            }
            $this->get_code_fragment()->add_function($this->myId().ucfirst($sortEventName).'(e)', ob_get_clean());
        }
    }
}
