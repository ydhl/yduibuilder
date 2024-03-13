<?php
namespace app\modules\build\views\preview\weui;


use app\build\Build_Model;
use app\project\Page_Model;
use function yangzie\__;

/**
 * 预览下重载事件绑定的代码
 */
trait Weui_Event_Binding {

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
    private function _popup_event_binding($eventName, $eventBind){
        $action = $eventBind['action'];
        $pageId = $action['popupPageId'];
        $page = $this->get_page($pageId);
        $page_config = json_decode(html_entity_decode($page->config), true);
        $page_type = strtoupper($page->page_type);
        $rootUI = @$page_config['items'][0];

        if ($page_type == 'POPUP'){// 加载modal
?>
    if (!document.getElementById("<?= $pageId?>")){
        var template = document.getElementById("<?= $this->myId(true)?>_<?= $eventBind['id']?>")
        var clon = template.content.cloneNode(true);
        document.body.appendChild(clon);
    }
    var $modal = $('#<?= $pageId?>');
    $modal.fadeIn(200);
    $modal.attr('aria-hidden','false');
    $modal.attr('tabindex','0');
    $modal.trigger('focus');
<?php
        }else{
            if (!$pageId){?>
    alert("<?= __("You not setting popup page, you can open event panel to setting")?>");
<?php       }else{?>
    window.document.location.href = "/preview/page/<?= $pageId ?>"
<?php }
        }
    }
    private function _call_event_binding($eventName, $eventBind){

    }

    /**
     * 预览时通过script 输出弹窗内容，并通过layer打开弹窗
     */
    public function build_event_binding_code(){
        $events = @$this->data['events'];
        if (!$events){
            return ;
        }
        foreach($events as $eventName => $eventBinds){
            $html_event_name = $this->eventMap($eventName);
            ob_start();
            foreach ($eventBinds as $bindId => $eventBind) {
                $action = $eventBind['action'];
                switch (strtoupper($action['type'])){
                    case 'POPUP': $this->_popup_event_binding($eventName, $eventBind);break;
                    case 'CALL': $this->_call_event_binding($eventName, $eventBind);break;
                }
            }
            $code = ob_get_clean();
?>
$('#<?= $this->myId(true)?>').on('<?= $html_event_name?>', function (event) {
<?= $code;?>
})
<?php
        }
    }
}
