<?php
namespace app\project;
use app\common\Option_Model;
use app\vendor\Env;
use TencentCloud\Msp\V20180319\Models\Project;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$project = $this->get_data("project");
$this->layout = '';
?>
<div class="container-fluid">
    <form method="post" id="transfer-form">
        <div class="row">
            <label class="col-sm-4 col-form-label"><span class="text-danger">*</span><?= __('New Name:')?></label>
            <div class="col-sm-8">
                <input type="text" name="p-name" class="form-control" value="">
            </div>
        </div>
        <?php include 'tech.inc.php'?>
        <div class="row mb-3">
            <div class="col-sm-8 offset-4">
                <button type="submit" class="btn btn-primary yd-form-submit yd-spin-btn" id="copy-button" data-submit-cb="copyDone" data-confirm-msg="<?= __('Are you sure？')?>" data-url="/project/<?= $project->uuid?>/copy"><?= __("Copy")?></button>
            </div>
        </div>
    </form>
</div>
<script>
    function copyDone(rst) {
        YDJS.spin_clear('#copy-button');
        if (rst.success){
            window.location.href = "/project/" + rst.data.uuid
        }else{
            YDJS.alert(rst.msg)
        }
    }
</script>
