<?php
namespace app\project;
use app\common\Option_Model;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$function = $this->get_data("function");
$curr_module  = $this->get_data("module");
$project  = $curr_module->get_project();
$modules = $project->get_modules();
$this->layout = '';
?>
<div class="container-fluid">
    <form method="post" id="module-add-form">
        <div class="row mb-3">
            <label class="col-form-label"><?= __('Function Name:')?></label>
            <div class="">
                <input type="hidden" name="uuid" value="<?= $function->uuid?>">
                <input type="text" name="name" class="form-control" value="<?= $function->name?>">
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-form-label"><?= __('Module:')?></label>
            <div class="">
                <select name="module" class="form-control">
                    <?php foreach ($modules as $module){?>
                        <option value="<?= $module->uuid?>" <?= $module->uuid==$curr_module->uuid ? "selected" : ""?>><?= $module->name?></option>
                    <?php }?>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-form-label"><?= __('Desc:')?></label>
            <div class="">
                <textarea class="form-control" name="desc"><?= $function->desc?></textarea>
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary yd-form-submit yd-spin-btn"
                data-redirect="<?= SITE_URI?>project/<?= $project->uuid?>/func?uuid=<?= $curr_module->uuid?>"
                data-url="<?= SITE_URI?>module/<?= $curr_module->uuid?>/addfunction"><?= __("Add")?></button>
        </div>
    </form>
</div>
