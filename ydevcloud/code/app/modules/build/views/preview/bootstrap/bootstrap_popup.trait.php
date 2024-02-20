<?php
namespace app\modules\build\views\preview\bootstrap;


use app\build\Build_Model;
use app\project\Page_Model;
use app\project\Project_Setting_Model;
use app\vendor\Env;
use function yangzie\__;

/**
 * 预览下重写bootstrap popup和代码输出逻辑
 */
trait Bootstrap_Popup {

    private function _output_template(&$outputPopupIds){
        $eventModels = $this->build->get_event($this->myid());
        $popupPageIds = [];
        foreach($eventModels as $eventModel){
            $action = $eventModel->get_action();
            if ($action->type != 'popup' || $action->popup_type != 'page') continue;// 只处理页面弹窗类型
            if (in_array($action->popupPageId, $outputPopupIds)) continue;
            if (!$popupPageIds[$action->popupPageId]) $popupPageIds[$action->popupPageId] = [];
            $popupPageIds[$action->popupPageId][] = $eventModel->uuid;
            $outputPopupIds[] = $action->popupPageId;
        }
        if (!$popupPageIds) return;

        foreach ($popupPageIds as $popupPageId=>$eventIds){
            $popupPage = $this->get_page($popupPageId);
            if (!$popupPage) continue;
            $build = $this->build->clone($popupPage);
            $build->set_indent(2);

            $popupPageView = self::create_View($build);
            $page_style = $popupPageView->build_style(false);
            $pageUIConfig = $build->get_ui_config();
            if (strtoupper($pageUIConfig['pageType']) == 'POPUP'){ // 1.模态弹窗页面
                $build->output_code('<template id="'.$popupPageId.'template">', 1);
                $build->output_code('<style>', 2);
                foreach ($page_style as $selector=>$style){
                    $build->output_code("{$selector} {", 3);
                    $build->output_code($style.";", 4);
                    $build->output_code("}", 3);
                }
                $build->output_code('</style>', 2);
                $popupPageView->output();
                $fragment = $popupPageView->build_code();
                $codes = $fragment->get_codes();
                if ($codes){
                    $build->output_code('<script type="module">', 2);
                    $build->output_code($codes, 3);
                    $build->output_code('</script>', 2);
                }
                $build->output_code('</template>', 1);
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
     * @param $outputPopupIds array 不同的组件可能会打开同一个popup，避免popup重复输出，对已经输出的记录在$outputPopupIds中，组件在输出时做个判定，已经存在$outputPopupIds中的就不在输出
     * @return void
     */
    public function build_popup_ui(&$outputPopupIds=[]){
        $this->_output_template($outputPopupIds);
        foreach ($this->childViews as $view){
            $view->build_popup_ui($outputPopupIds);
        }
    }
}
