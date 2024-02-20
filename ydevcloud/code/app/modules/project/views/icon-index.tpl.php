<?php
namespace app\project;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$project = $this->get_data('project');
$this->master_view = 'master/project';
$ui = $project->get_setting_value('ui');
$uiVersion = $project->get_setting_value('ui_version');
$type = strtolower(@$_GET['type']);
?>
<div class="d-flex justify-content-center m-5">
    <a href="/project/<?= $project->uuid?>/icon?type=custom" class="btn m-3 <?= $type=='custom' ? 'btn-primary' : 'btn-light'?> btn-lg"><?= __('Custom Icon')?></a>
    <a href="/project/<?= $project->uuid?>/icon?type=ui" class="btn  m-3 <?= !$type || $type=='ui' ? 'btn-primary' : 'btn-light'?> btn-lg"><?= $ui.'@'.$uiVersion?></a>
</div>
<?php if($type=='custom'){?>
    <div class="mb-3">
        <small class="text-muted" style="font-size: 13px">
            <?= sprintf(__('currently just support %s, you can upload iconfont zip and use it.'), '<a href="https://www.iconfont.cn/" target="_blank">iconfont</a>')?>
        </small>
        <button data-upload-mime="application/zip" class="btn btn-primary btn-xs yd-upload" data-complete-callback="upload_complete" data-url="/project/<?= $project->uuid?>/upload?action=icon"><?= __('Upload')?></button>
    </div>
<?php }?>
<iframe frameborder="0" style="width: 100%; height: 100vh" src="/project/<?= $project->uuid?>/icon.frame?type=<?= $type?>"></iframe>
<script>
    function upload_complete(){
        document.location.reload()
    }
</script>
