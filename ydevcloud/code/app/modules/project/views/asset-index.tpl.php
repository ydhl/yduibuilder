<?php
namespace app\project;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use yangzie\YZE_Simple_View;
use function yangzie\__;


$this->master_view = 'master/project';
//$pagination = new YZE_Simple_View()
$files = $this->get_data("files");
$project = $this->get_data("project");
$type = $this->get_data("type");
$currpage = $this->get_data("currpage");
$total = $this->get_data("total");

$pagination = new YZE_Simple_View(YZE_APP_VIEWS_INC."pagination", ['total'=>$total, 'pagesize'=>20, 'currpage'=>$currpage], $this->controller)
?>
<div class="d-flex justify-content-center m-5">
    <a href="/project/<?= $project->uuid?>/asset?type=image" class="btn m-3 <?= $type=='image' ? 'btn-primary' : 'btn-light'?> btn-lg"><?= __('Image')?></a>
    <a href="/project/<?= $project->uuid?>/asset?type=asset" class="btn  m-3 <?= $type=='asset' ? 'btn-primary' : 'btn-light'?> btn-lg"><?= __('Asset')?></a>
</div>
<div class="d-flex flex-wrap">
<?php
foreach ($files as $file){
    if ($file->type == 'image'){
        $preview = $file->url ? UPLOAD_SITE_URI.$file->url : '/logo2.svg';
    }else{
        $preview = '/logo2.svg';
    }
    ?>
    <div class='card me-3 mb-3' style="width: 16rem;">
        <div class='card-body'>
            <div class="d-flex">
                <div class='border rounded flex-grow-0  flex-shrink-0 yd-dialog' data-size="large" data-content="<img src='<?= $preview?>' style='width:100%'/>" data-title="Preview"
                     style="width: 50px;height: 50px; background-size:cover; background-position:center;background-image: url(<?= $preview?>)" ></div>
                <div class='ps-3'>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-truncate" style="width: 8rem;"><?= $file->file_name?></div>
                        <div class="dropdown flex-grow-0  flex-shrink-0">
                            <i class="iconfont icon-more" type="button" data-bs-toggle="dropdown" aria-expanded="false"></i>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a href="#" data-url="/project/<?= $project->uuid?>/asset/remove?uuid=<?= $file->uuid?>"
                                       data-redirect="reload"
                                       data-content="<?= __('Are you sure?<br/>Deletion is safe and will not affect existing content ')?>" class="dropdown-item yd-confirm-post text-danger">
                                        <i class="iconfont icon-remove"></i> <?= __('Remove')?></a></li>
                        </div>
                    </div>
                    <div>
                        <small class="text-muted"><?= $file->upload_date?></small>
                        <small class="text-success"><?= round($file->file_size / 1000, 0)?>Kb</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php }?>
</div>
<?php
$pagination->output();
?>
