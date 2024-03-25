<?php
namespace app\modules\build\views\code\web\bootstrap_vue;
use app\build\Build_Model;
use app\project\Page_Model;
use function yangzie\__;

/**
 * 由于bootstrap前端生成的代码和在线预览一样，所以web前端不同框架代码生成Class都继承之Preview
 * 这里通过trait都方式统一重载相关的实现，vue3的组件使用
 * @package app\api\views
 */
trait Vue {
    use Vue_Code_Helper;
    protected function get_Img_Src($imgSrc){
        return '~'.rtrim($this->build->get_img_Asset_Path(), '/').'/'.basename(urldecode($imgSrc), PATHINFO_BASENAME);
    }

    /**
     * 输出成vue格式的json字符串，单引号，如果是合法的单词，则不需要引号，比如
     * { name: 'value', 'name-1': 'value2' }
     * @param $array
     */
    public function formatVue3JSON($array){
        $string = [];
        foreach ($array as $name => $value) {
            $string[] = (preg_match("/-/", $name) ? escapeshellarg($name) : $name).": ".escapeshellarg($value);
        }
        return "{ ".join(', ', $string)." }";
    }

    public static function get_View_Class(array $uiconfig, Build_Model $build){
        $ui = $build->get_ui();
        $frontend = $build->get_frontend();
        $frontendFramework = $build->get_front_Framework();
        $type = strtolower($uiconfig['type']);
        return "app\\modules\\build\\views\\code\\{$frontend}\\{$ui}_{$frontendFramework}\\{$type}_View";
    }

    private function get_page_url($page) {
        return $page->id == $this->build->get_project()->home_page_id ? '/' : ($page->url?:"/page{$page->id}");
    }

    ///
    /// 弹窗处理
    ///
    ///

    public function build_main_attrs(){
        parent::build_main_attrs();
        $events = @$this->data['events'];
        foreach((array)$events as $eventName => $eventBinds){
            $sortEventName = $this->eventMap($eventName);
            echo ' @'.$sortEventName.'="'.$this->myId().ucfirst($sortEventName).'"';
        }
    }
    /**
     * 返回ydecloud对应事件名在vue3上对应的事件名
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

    public function build_popup_ui(&$outputPopupIds=[]){
        // 把弹窗作为telport的组件输出
        if (@$this->data['events']){
            foreach($this->data['events'] as $eventName => $eventBinds){
                foreach ($eventBinds as $bindId => $eventBind) {
                    $action = $eventBind['action'];
                    $popupName = $this->myId().$bindId;
                    if (strtoupper($action['type']) == 'POPUP'){
                        $page = $this->get_page($action['popupPageId']);
                        if (strtolower($page->page_type) == 'popup'){
                            echo "        <{$popupName} v-if='{$popupName}Visible'></{$popupName}>\r\n";
                        }else if($page){
                            $pageId = $action['popupPageId'];
                            $this->get_code_fragment()->add_ref("popup".$page->id."State", '', true,'max 最大化 min 最小化 "" 默认');
                            $this->get_code_fragment()->add_ref("popup".$page->id."Title", "{$page->name}", true);
                            $this->get_code_fragment()->add_declare("const",'closeModal', 'any');
                            $this->get_code_fragment()->add_declare("const",'layer', 'any');
                            $this->get_code_fragment()->add_declare("const",'layerTop', 'any');
                            $this->get_code_fragment()->add_declare("const",'$', 'any');
?>
        <div v-if='<?= $popupName?>Visible' class="layer-iframe-dialog shadow" onclick="YDECloud.layerTop('<?=$pageId?>')" id="<?=$pageId?>">
            <div class='modal-dialog overflow-hidden'>
                <div class='modal-content'>
                    <div class='modal-header bg-light align-items-center'>
                        <div class='move-handler text-truncate flex-grow-1'>{{popup<?= $page->id?>Title}}</div>
                        <div class="btn-group win-btn-group">
                            <button type="button" @click="popup<?= $page->id?>Minimize()" class="btn btn-secondary btn-sm" v-if="popup<?= $page->id?>State==''"><span class="iconfont icon-minimize"></span></button>
                            <button type="button" @click="popup<?= $page->id?>Maximize()" class="btn btn-secondary btn-sm" v-if="popup<?= $page->id?>State==''"><span class="iconfont icon-maximize"></span></button>
                            <button type="button" @click="popup<?= $page->id?>Resume()" class="btn btn-secondary btn-sm" v-if="popup<?= $page->id?>State!=''"><span class="iconfont icon-resume"></span></button>
                            <button type="button" onclick="YDECloud.closeModal('<?= $pageId?>')" class="btn btn-secondary btn-sm"><span class="iconfont icon-close-win"></span></button>
                        </div>
                    </div>
                    <iframe @load="popup<?= $page->id?>Loaded()" scrolling="auto" allowtransparency="true" class="w-100 h-100" frameborder="0" src="<?= $this->get_page_url($page)?>"></iframe>
                </div>
            </div>
        </div>
<?php
                            ob_start();
?>
() => {
  const index = $('#<?= $pageId?>').attr('data-layer-index')
  popup<?= $page->id?>State.value = 'min'
  layer.min(index)
  $('#<?= $pageId?>').attr('data-layer-state', 'minimize')
  var length = $("[data-layer-state='minimize']").length
  $('#layui-layer' + index).css({ height: '68px', width: '300px', bottom: '0', left: ((length - 1) * 300 + 16) + 'px', top: 'unset' })
}
<?php
                            $this->get_code_fragment()->add_function("popup{$page->id}Minimize",  ob_get_clean(),true);
                            ob_start();
?>
() => {
  const index = $('#<?= $pageId?>').attr('data-layer-index')
  popup<?= $page->id?>State.value = 'max'
  layer.full(index)
}
<?php
                            $this->get_code_fragment()->add_function("popup{$page->id}Maximize",  ob_get_clean(),true);
                            ob_start();
?>
() => {
  var index = $('#<?= $pageId?>').attr('data-layer-index')
  popup<?= $page->id?>State.value = ''
  layer.restore(index)
}
<?php
                            $this->get_code_fragment()->add_function("popup{$page->id}Resume",  ob_get_clean(),true);
                            ob_start();
?>
() => {
  popup<?= $page->id?>Title.value = ($('#<?= $pageId?> iframe').get(0) as any).contentDocument.title
}
<?php
                            $this->get_code_fragment()->add_function("popup{$page->id}Loaded",  ob_get_clean(),true);
                        }
                    }
                }
            }
        }
        foreach ($this->childViews as $view){
            $view->build_popup_ui($outputPopupIds);
        }
    }
    public function build_event_binding_code(){
        $events = @$this->data['events'];
        if (!$events){
            return;
        }
        foreach($events as $eventName => $eventBinds){
            $functionName = $this->myId().ucfirst($this->eventMap($eventName));
            ob_start();
?>
() => {
<?php
            foreach ($eventBinds as $bindId => $eventBind) {
                $action = $eventBind['action'];
                $page = find_by_uuid(Page_Model::CLASS_NAME, $action['popupPageId']);
                switch (strtoupper($action['type'])){
                    case 'POPUP': $this->_popup_event_binding($page, $eventName, $eventBind);break;
                    case 'CALL':  $this->_call_event_binding($page, $eventName, $eventBind); break;
                }
            }
?>
}
<?php
            $this->get_code_fragment()->add_function($functionName, ob_get_clean(), true);
        }
    }

    /**
     * 代码直接output，其他的代码组成部分通过return返回
     * @param Page_Model|null $page
     * @param $eventName
     * @param $eventBind
     * @return array
     */
    private function _popup_event_binding(Page_Model $page=null, $eventName, $eventBind){

        $this->get_code_fragment()->add_import('vue', ['ref']);

        $page_config = json_decode(html_entity_decode($page->config), true);
        $page_type = strtoupper($page->page_type);
        $rootUI = @$page_config['items'][0];
        $popupName = $this->myId().$eventBind['id'];

        if ($page_type == 'POPUP'){
            $page_file = $page->get_save_path('vue');
            $this->get_code_fragment()->add_component($popupName, $page_file);
            $this->get_code_fragment()->add_declare('const', 'openModal', 'any');
            $this->get_code_fragment()->add_ref("{$popupName}Visible", false, true);
            $this->get_code_fragment()->add_import("vue", ['nextTick']);
?>
  <?= $popupName ?>Visible.value = true
  nextTick(() => {
    openModal({
      pageId: '<?= $page->uuid?>',
      esc: <?= "'".(@$rootUI['meta']['custom']['esc'] ?:'yes')."'"?>,
      backdrop: <?= "'".(@$rootUI['meta']['custom']['backdrop'] ?:'yes')."'"?>,
      position: <?= @$rootUI['meta']['custom']['position'] ? "['".join("', '",@$rootUI['meta']['custom']['position'])."']" : 'undefined'?>
    })
  })
<?php }else{
    if (!$page){
        $this->get_code_fragment()->add_declare('const', 'layer', 'any');
?>
  layer.alert('<?= __("You not setting popup page, you can open event panel to setting")?>')
<?php
    return;
    }else{
        $this->get_code_fragment()->add_declare('const', 'openPage', 'any');
        $this->get_code_fragment()->add_ref("{$popupName}Visible", false, true);
        $this->get_code_fragment()->add_import("vue", ['nextTick']);
?>
  <?= $popupName ?>Visible.value = true
  nextTick(() => {
    YDECloud.openPage({
      pageId: '<?= $page->uuid ?>'
    })
  })
<?php }
}

    }
    private function _call_event_binding(Page_Model $page, $eventName, $eventBind){

    }
}
