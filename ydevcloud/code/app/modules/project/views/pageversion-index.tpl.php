<?php
namespace app\project;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;
use function yangzie\_e;

$this->master_view = 'master/structure';
$page = $this->get_Data('page');
$pageVersions = $page->get_versions();
$token = $this->get_data('token');
?>
<h3><?= __('Version')?></h3>
<div class="d-flex flex-wrap align-items-start justify-content-start">
    <?php foreach ($pageVersions as $pageVersion){?>
    <div class="card me-3 mb-3" style="width: 20rem">
        <div class="card-body p-0 bgtransparent">
            <div class="page-preview" style="background-image: url(<?= $pageVersion->screen ? UPLOAD_SITE_URI.$pageVersion->screen : '/img/design.svg'?>)"></div>
        </div>
        <table class="table m-0 table-striped table-hover table-bordered">
            <tr>
                <td class="text-muted"><?= __('Version Index')?></td>
                <td><?= $pageVersion->index?></td>
            </tr>
            <tr>
                <td class="text-muted"><?= __('Saved Time')?></td>
                <td><?= $pageVersion->created_on?></td>
            </tr>
            <tr>
                <td class="text-muted"><?= __('Saved By')?></td>
                <td><?= $pageVersion->get_project_member()->get_user()->nickname?></td>
            </tr>
            <tr>
                <td class="text-muted"><?= __('Message')?></td>
                <td><?= $pageVersion->message?></td>
            </tr>
            <?php if ($page->last_version_id!=$pageVersion->id){?>
            <tr>
                <td colspan="2"><button class="btn btn-primary btn-sm reverse2version" type="button"
                data-token="<?= $token?>"
                data-socket="<?= SOCKET_HOST?>"
                data-content="<?= __('Are you sure you want to reverse the selected version?')?>"
                data-title="<?= __('Reverse to this')?>"
                data-pageid="<?= $page->uuid ?>"
                data-uuid="<?= $pageVersion->uuid?>"><?= __('Reverse to this')?></button></td>
            </tr>
            <?php }else{?>
                <tr>
                    <td colspan="2"><button disabled class="btn btn-light btn-sm" type="button"><?= __('Current Version')?></button></td>
                </tr>
            <?php }?>
        </table>
    </div>
    <?php }?>
</div>
