<?php
namespace app\build;

use yangzie\YZE_Request;
use function yangzie\__;
use function yangzie\yze_merge_query_string;

/**
 * 预览的框架页面，输出左边菜单和右边的实际预览页面
 */
$module = $this->get_data('module');
$project = $this->get_data('project');
$curr_page = $this->get_data('curr_page');
$pages = $module ? $module->get_pages() : [];
$this->set_data('type', 'ui');
$curr_page = $curr_page ?: reset($pages);
if (!$_GET['hidemaster']){
    $this->master_view = 'master/preview';
}
$simulateDevice = $project->end_kind == 'mobile' ? 'mobile' : @$_GET['device'];

$height = "";
if ($simulateDevice && $simulateDevice!='pc') {
    if ($simulateDevice === 'tablet') {
        $width = 768;
        $height = "height:1024px";
    }else{
        $width = 576;
        $height = "height:768px";
    }
?>
        <div class="d-flex justify-content-center">
<div class="simulate-border shadow-sm mt-4" style="width: <?=$width?>px;<?= $height?>">
<?php }
?>
    <iframe style="border:1px solid #efefef" class="preview-page" id="preview-page" src="<?= yze_merge_query_string('/preview/page/'.$curr_page->uuid,$_GET)?>"></iframe>
<?php
if ($simulateDevice && $simulateDevice!='pc') {
?>
</div>
    </div>
<?php }?>
