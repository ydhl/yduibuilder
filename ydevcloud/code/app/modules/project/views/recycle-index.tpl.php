<?php
namespace app\project;
use yangzie\YZE_Hook;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;
use function yangzie\yze_merge_query_string;

$project = $this->get_data('project');
$pages = $this->get_data('pages');
$loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
$member = $project->get_member($loginUser->id);
$this->master_view = 'master/structure';

if (!$pages){?>
    <div class="p-5 text-muted text-center fs-1"><i class="iconfont icon-recycle fs-1"></i> <?= __('empty')?></div>
<?php
return;
}?>
<table class="table table-bordered table-sm table-hover table-striped align-middle">
    <thead>
    <tr>
        <th></th>
        <th><?= __('Function')?></th>
        <th>
            <?= __('Page')?>
        </th>
        <th>
            <?= __('Page Type')?>
        </th>
        <th>
            <?= __('Version')?>
        </th>
    </tr>
    </thead>
<?php foreach ($pages as $page){ ?>
        <tr>
            <td><?php if ($page->screen){?>
                <div class="bg-light" style="height: 50px;width:50px;background-size:cover;background-image: url(<?= $page->screen ? UPLOAD_SITE_URI.$page->screen : '/img/transparent.svg'?>)"></div>
                <?php }?>
            </td>
            <td><?= $page->get_module()->name."/".$page->get_function()->name?></td>
            <td><?= $page->name?></td>
            <td><?= $page->page_type?></td>
            <td><?= $page->get_Version_Count()?></td>
            <td>
                <div class="btn-group btn-group-sm">
                    <a href="/preview/<?= $project->uuid?>?module=<?= $page->get_module()->uuid?>&page=<?= $page->uuid?>" class="btn btn-outline-primary btn-sm"><?= __('Preview')?></a>
                    <?php if ($member && $member->can_edit()){?>
                    <a href="javascript:;"
                           data-title="<?= __('Delete Page')?>"
                           data-content="<?= __('This action cannot be undone, all content in page will be lost.  are you sure?')?>"
                           data-url="/project/<?=$project->uuid?>/recycle?page=<?= $page->uuid?>"
                           data-redirect="reload"
                           class="btn btn-outline-primary yd-confirm-post btn-sm"><?= __('Delete Page')?></a>
                    <a href="javascript:;"
                       data-url="/project/<?=$project->uuid?>/recovery?page=<?= $page->uuid?>"
                       data-title="<?= __('Recovery')?>"
                       class="btn btn-outline-primary yd-dialog btn-sm"><?= __('Recovery')?></a>
                    <?php }?>
                </div>
            </td>
        </tr>
<?php }?>
</table>
