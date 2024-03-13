<?php
namespace app\modules\build\views\preview\weui;


use app\build\Build_Model;
use app\project\Page_Model;
use app\project\Project_Setting_Model;
use app\vendor\Env;
use function yangzie\__;

/**
 * 预览下重写微信popup和代码输出逻辑
 */
trait Weui_Popup {

    private function _output_template(){
        $events = $this->data['events'];
        $uiPackage = $this->build->get_project()->get_setting_value(Env::UI).'@'.$this->build->get_project()->get_setting_value(Env::UI_VERSION);
        $pageIds = [];
        foreach($events as $eventName => $eventBinds){
            foreach ($eventBinds as $bindId => $eventBind) {
                $action = $eventBind['action'];
                if (strtoupper($action['type']) != 'POPUP') continue;
                $pageIds[$bindId] = $action['popupPageId'];
            }
        }
        if (!$pageIds) return;
        foreach ($pageIds as $bindId => $pageId){
            $page = $this->get_page($pageId);
            if (!$page) continue;
            $build = $this->build->clone($page);
            $build->set_indent(2);

            $pageView = self::create_View($build);
            $pageUIConfig = $this->build->get_ui_config();
            $page_style = $pageView->build_style(false);
            if (strtoupper($pageUIConfig['pageType']) == 'POPUP'){
?>
    <template id="<?= $this->myId(true)?>_<?= $bindId?>">
        <style>
<?php
foreach ($page_style as $selector=>$style){
    echo "            {$selector} {\r\n                ".$style.";\r\n            }\r\n";
}
?>
        </style>
<?php $pageView->output();
ob_start();
$pageView->build_code();
$codes = ob_get_clean();
if ($codes){?>
        <script type="module">
<?php $this->build->output_code($codes, 3)?>
        </script>
<?php }
?>
    </template>
<?php
            } else {
?>
    <template id="<?= $this->myId(true)?>_<?= $bindId?>">
        <div class="layer-iframe-dialog shadow" :onclick="'YDECloud.layerTop('+pageId+'})'" id="<?= $pageId?>">
            <div class='modal-dialog overflow-hidden'>
                <div class='modal-content'>
                    <div class='modal-header bg-light align-items-center'>
                        <div class='move-handler text-truncate flex-grow-1'>
                            {{title}}
                        </div>
                        <div class="btn-group win-btn-group">
                            <button type="button" @click="pageMinimize(pageId)" class="btn btn-secondary btn-sm" v-if="state==''"><span class="iconfont icon-minimize"></span></button>
                            <button type="button" @click="pageMaximize(pageId)" class="btn btn-secondary btn-sm" v-if="state==''"><span class="iconfont icon-maximize"></span></button>
                            <button type="button" @click="pageResume(pageId)" class="btn btn-secondary btn-sm" v-if="state!=''"><span class="iconfont icon-resume"></span></button>
                            <button type="button" @click="YDECloud.closeModal(pageId)" class="btn btn-secondary btn-sm"><span class="iconfont icon-close-win"></span></button>
                        </div>
                    </div>
                    <iframe @load="iframeLoaded()" scrolling="auto" allowtransparency="true" class="w-100 h-100" frameborder="0" :src="url"></iframe>
                </div>
            </div>
        </div>
        <script type="module"><?php // 这里做在线预览，所以直接固定用petitevue的版本,并且明确知道用的是zepto?>

            import { createApp } from '<?= $build->get_root_Path()?>vendor/petitevue@0.4.0/petite-vue.es.js'
            createApp(layerPageComponent({ pageId: '<?= $pageId?>', title: '<?= urlencode($page->name)?>', url: '<?= $this->get_popup_page_url($page)?>' })).mount("#<?= $pageId?>");
        </script>
    </template>
<?php
            }
        }
    }

    /**
     * 弹窗页面预览地址
     * @param $eventName
     * @return string
     */
    protected function get_popup_page_url($page) {
        return '/preview/page/'.$page->uuid;
    }
    /**
     * 预览时通过script 输出弹窗内容，并通过layer打开弹窗
     */
    public function build_popup_ui(&$outputPopupIds=[]){
        if (@$this->data['events']){
            $this->_output_template();
        }
        foreach ($this->childViews as $view){
            $view->build_popup_ui($outputPopupIds);
        }
    }
}
