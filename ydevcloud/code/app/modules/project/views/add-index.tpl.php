<?php
namespace app\project;
use app\common\Option_Model;
use app\vendor\Env;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$project = $this->get_data("project");
$logo = $project ? $project->get_setting_value('logo') : '';
$this->layout = '';
?>
<div class="container-fluid overflow-hidden">
    <form method="post">
        <div class="row mb-3">
            <label class="col-sm-4 col-form-label"><span class="text-danger">*</span><?= __('Project Name:')?></label>
            <div class="col-sm-8">
                <input type="hidden" name="p-uuid" value="<?= $project->uuid?>">
                <input type="text" name="p-name" class="form-control" value="<?= $project->name?>">
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-sm-4 col-form-label"><?= __('Project Logo:')?></label>
            <div class="col-sm-8">
                <img src="<?= $logo ? UPLOAD_SITE_URI.$logo : "#"?>" class="rounded-circle project-logo <?= $logo ? '' : "d-none"?>" style="width: 50px;height: 50px;object-fit: cover"/>
                <input type="hidden" name="s-logo" id="project-logo" value="<?= $logo?>">
                <button type="button" class="btn btn-secondary btn-sm yd-upload" data-uploaded-callback="logo_callback" data-upload-mime="image/*" data-url="/common/upload"><?= __('Upload')?></button>
            </div>
        </div>
        <?php if (!$project){?>
        <div class="row mb-3">
            <label class="col-sm-4 col-form-label"><?= __('Type:')?></label>
            <div class="col-sm-8">
                <select name="p-end_kind" class="form-select end_kind">
                    <option value="pc"><?= __('PC')?></option>
                    <option value="mobile"><?= __('Mobile')?></option>
                </select>
            </div>
            <small class="offset-sm-4 text-danger"><?= __('Cannot be modified after setting')?></small>
        </div>
        <?php }?>
        <div class="row mb-3">
            <label class="col-sm-4 col-form-label"><?= __('Brief:')?></label>
            <div class="col-sm-8">
                <textarea type="text" name="p-desc" class="form-control"><?= $project->desc?></textarea>
            </div>
        </div>
        <?php if (!$project){
            include 'tech.inc.php';
        }?>
        <div class="row mb-3">
            <div class="col-sm-8 offset-4">
                <button type="submit" class="btn btn-primary yd-form-submit yd-spin-btn" data-redirect="<?= $project ? "reload" : "/project"?>" data-url="/project/add"><?= $project ? __("Save") : __("Add")?></button>
            </div>
        </div>
    </form>
</div>
<script>
    function logo_callback(up, file, response) {
        if (response && response.success) {
            $(".project-logo").attr('src', response.data.url)
            $(".project-logo").removeClass('d-none')
            $("#project-logo").val(response.data.file)
        }
    }
    $(function () {
        // 加载环境
        $(".end_kind").on("change", function () {
            var endkind = $(this).val();
            var options = '';
            $('.front').empty();

            for (var name in envInfo) {
                if (envInfo[name]['env'] != 'front') continue;
                if (envInfo[name]['endKind'].indexOf(endkind)==-1) continue;

                options += `<option value="${name}">${envInfo[name]['name']}</option>`;
            }
            $('.front').append(options);
            $('.front').trigger('change');
        })
        setTimeout(function (){
            $(".end_kind").trigger('change');
        },500)
        yd_upload_render();
    })
</script>
