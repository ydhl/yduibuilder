<?php
namespace app\project;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$project = $this->get_data('project');
$token = $this->get_data('token');
$this->master_view = 'master/project';
?>
<h3 class="mb-3"><i class="iconfont icon-download fs-2"></i> <?= __('Build')?></h3>

<h6 class="pb-2 d-flex align-items-center justify-content-center">
    <button type="button" class="btn btn-primary ms-3" data-uuid="<?= $project->uuid?>" data-token="<?= $token?>"
            data-content="<?= __('please dont refresh or close page when building')?>"
            data-url="<?= SOCKET_HOST?>"
            id="build-project"><?= __('Build Now')?></button>
</h6>
<pre id="build-log" class="overflow-auto" style="height:calc( 100vh - 400px );"></pre>
