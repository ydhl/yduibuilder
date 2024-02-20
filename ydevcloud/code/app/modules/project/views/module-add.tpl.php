<?php
namespace app\project;
use app\common\Option_Model;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

/**
 * 该功能会在管理后台和uibuilder中被调用，uibuilder调用是通过header token传入认证信息
 */
$project = $this->get_data("project");
$module  = $this->get_data("module");
$request = YZE_Request::get_instance();

$this->layout = '';
?>
<div class="container-fluid">
    <form method="post" id="module-add-form">
        <div class="row mb-3">
            <label class="col-form-label"><?= __('Module Name:')?></label>
            <div class="">
                <input type="hidden" name="uuid" value="<?= $module->uuid?>">
                <?php ?>
                <input type="text" name="name" class="form-control" value="<?= $module->name?>">
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-form-label"><?= __('Desc:')?></label>
            <div class="">
                <textarea class="form-control" name="desc"><?= $module->desc?></textarea>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-form-label"><?= __('Folder:')?></label>
            <div class="">
                <input type="text" name="folder" class="form-control" value="<?= $module->folder?>">
                <div class="form-text"><?= __('Folder is the module saved folder name, when build all module content will saved in this folder')?></div>
            </div>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary yd-form-submit yd-spin-btn"
                    data-redirect="/project/<?= $project->uuid?>/structure"
                    data-url="<?= $module ? "/module/".$module->uuid."/edit" : SITE_URI."project/".$project->uuid."/addmodule"?>"><?= __("Add")?></button>
        </div>
    </form>
</div>
