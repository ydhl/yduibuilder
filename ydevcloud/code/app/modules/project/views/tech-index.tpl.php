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
$this->layout = '';
?>
<div class="container-fluid">
    <form method="post">
        <?php include 'tech.inc.php'?>
        <div class="row mb-3">
            <div class="col-sm-8 offset-4">
                <button type="submit" class="btn btn-primary yd-form-submit yd-spin-btn" data-redirect="/project/<?= $project->uuid?>/setting" data-url="/project/<?= $project->uuid?>/tech"><?= __("Update")?></button>
            </div>
        </div>
    </form>
</div>
