<?php
namespace app\project;
use app\vendor\Env;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$project = $this->get_data('project');
$menu = $this->get_data('menu');
$this->master_view = 'master/project';
$installedPackages = $project->get_front_project_packages();
$allPackages = Env::package();
$colors =[
        'lib'=>'primary',
        'framework'=>'success',
        'ui'=>'warning',
        'language'=>'info',
]
?>
<h3 class="mb-3"><i class="iconfont icon-library fs-2"></i> <?= __('Libraries')?></h3>

<div class="card ms-3">
        <div class="card-header"><?= __("Installed Libraries")?></div>
        <ul class="list-group list-group-flush">
            <?php foreach ($installedPackages['system'] as $package){
                list($packageName) = explode('@', trim($package));
            ?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
                <div class="text-truncate">
                    <?= $package?>&nbsp;<span class="badge bg-<?= $colors[$allPackages[$packageName]['type']]?:'secondary'?>"><?= $allPackages[$packageName]['type']?></span>
                    <div class="text-muted" style="font-size: 10px"><?= $allPackages[$packageName]['desc']?></div>
                </div>
            </li>
            <?php }?>
        </ul>
    </div>

