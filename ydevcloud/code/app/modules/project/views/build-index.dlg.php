<?php
namespace app\project;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;
use function yangzie\yze_module_js_bundle;

$project = $this->get_data('project');
$token = $this->get_data('token');
$this->layout="";

yze_module_js_bundle();
?>
<h6 class="pb-2 border-bottom d-flex align-items-center justify-content-start">
    <button type="button" class="btn btn-primary ms-3" data-uuid="<?= $project->uuid?>" data-token="<?= $token?>"
            data-content="<?= __('please dont refresh or close page when building')?>"
            data-url="<?= SOCKET_HOST?>"
            id="build-project"><?= __('Build Now')?></button>
</h6>
<pre id="build-log" class="overflow-auto" style="height:calc( 100vh - 400px );"></pre>
