<?php
namespace app\project;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use yangzie\YZE_Simple_View;
use function yangzie\__;
$uicomponents = $this->get_data("uicomponents");
$types = $this->get_data("types");
$total = $this->get_data("total");
$currpage = $this->get_data("currpage");
$members = $this->get_data("members");
$this->master_view = 'master/project';
$pagination = new YZE_Simple_View(YZE_APP_VIEWS_INC."pagination", ['total'=>$total, 'pagesize'=>30, 'currpage'=>$currpage], $this->controller)

?>
<h3><i class="iconfont icon-ui fs-2"></i> <?= __('Project UI Component')?></h3>
<form method="get">
    <div class="d-flex align-items-center mb-3 mt-4">
        <select name="type" class="form-control form-control-sm w-auto me-3">
            <option value=""><?= __('All Type')?></option>
            <?php foreach ($types as $type){?>
                <option value="<?= $type?>" <?= $_GET['type']==$type ? "selected" : ""?>><?= $type?></option>
            <?php }?>
        </select>
        <select name="kind" class="form-control form-control-sm w-auto me-3">
            <option value=""><?= __('All Kind')?></option>
            <option value="pc" <?= $_GET['kind']=='pc' ? "selected" : ""?>><?= __('PC')?></option>
            <option value="mobile" <?= $_GET['kind']=='mobile' ? "selected" : ""?>><?= __('Mobile')?></option>
        </select>
        <select name="user_id" class="form-control form-control-sm w-auto me-3">
            <option value=""><?= __('All Member')?></option>
            <?php foreach ($members as $member_id => $member_name){?>
                <option value="<?= $member_id?>" <?= $_GET['user_id']==$member_id ? "selected" : ""?>><?= $member_name?></option>
            <?php }?>
        </select>
        <input class="form-control form-control-sm w-auto me-3" name="query" value="<?= $_GET['query']?>" placeholder="<?= __('Search')?>" type="text">
        <button class="btn btn-primary btn-sm" type="submit"><?= __('Search')?></button>
    </div>
</form>
<?php if(!$uicomponents){ ?>
    <div class="d-flex justify-content-center align-items-center h-50">
        <?= __("Has no UI Component ")?>
    </div>
<?php
return;
}?>

<div class="d-flex flex-wrap align-items-start mb-3">
    <?php foreach ($uicomponents as $uicomponent){?>
    <div class="card me-3 mb-3" style="width: 300px; height: 300px">
        <div class="card-header"><?= $uicomponent->name?>
        </div>
        <div class="card-body bgtransparent">
            <div class="w-100 h-100" style="background-repeat:no-repeat;background-position:center center;background-image:url(<?= SITE_URI?>image?pageid=<?= $uicomponent->uuid?>);background-size:100% auto">
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <div class="d-flex align-items-center">
                <small class="badge bg-info"><?= $uicomponent->component_end_kind?></small>
            </div>
            <button type="button"
                    data-content="<?= __('Page will move to Trash, you can put it back anytime.')?>"
                    data-button="<?= __('Delete')?>"
                    class="btn btn-danger btn-xs delete-uicomponent" data-uuid="<?= $uicomponent->uuid?>"><?= __('Delete')?></button>
        </div>
    </div>
    <?php }?>
</div>
<?php
$pagination->output();
?>
