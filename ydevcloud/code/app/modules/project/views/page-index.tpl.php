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
$curr_module = $this->get_data('curr_module');
$curr_function = $this->get_data('curr_function');
$loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
$member = $project->get_member($loginUser->id);
$this->master_view = 'master/structure';
$token = $this->get_data('token');

if (!$pages){?>
    <div class="p-5 m-5 text-center text-muted">
        <?php if ($curr_function){?>
            <p><?= __('you have no page, please use uibuilder add page and design it')?></p>
            <a href="javascript:;" data-url="<?= Project_Model::get_ui_builder_url()?>"
               data-functionid="<?= $curr_function->uuid?>"
               class="run-ui-builder btn btn-primary"><i class="iconfont icon-uibuilder"></i> <?= __('Open UIBuild Add Page')?></a>
        <?php } else if ($curr_module && !$curr_module->get_functions()){
            ?>
            <p><?= __('please add function first')?></p>
            <a href="#" data-url="/module/<?= $curr_module->uuid?>/addfunction"
               data-title="<?= __('Add Function')?>" class="btn btn-primary yd-dialog"><i class="iconfont icon-function"></i> <?= __('Add Function')?></a>
            <?php
        }?>
    </div>
<?php }?>
<div class=" d-flex flex-wrap ">
<?php foreach ($pages as $page){ ?>
    <div class="card me-3 mb-3" style="width: 20rem">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="text-truncate flex-grow-1"><?= $page->name?></div>
            <div class="btn-group btn-group-sm">
                <a href="/preview/<?= $project->uuid?>?module=<?= $page->get_module()->uuid?>&page=<?= $page->uuid?>" class="btn btn-outline-secondary btn-sm" title="<?= __('Preview')?>"><i class="iconfont icon-run"></i></a>
                <a href="#" data-url="<?= Project_Model::get_ui_builder_url()?>" data-uuid="<?= $page->uuid?>" title="<?= __('Build UI')?>" class="run-ui-builder btn btn-sm btn-outline-secondary"><i class="iconfont icon-ui"></i></a>
                <div class="dropdown btn btn-outline-secondary btn-sm">
                    <i class="iconfont icon-more" type="button" data-bs-toggle="dropdown" aria-expanded="false"></i>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a href="/project/<?= $project->uuid?>/pageversion?page=<?= $page->uuid?>" class="dropdown-item"><i class="iconfont icon-versions"></i> <?= __('Version')?> (<?= $page->get_Version_Count()?>)</a></li>
                        <?php if ($member && $member->can_edit()){?>
                            <li><a javascript=":;"
                                   data-url="/project/<?= $project->uuid?>/recovery?page=<?= $page->uuid?>"
                                   data-title="<?= __('Move page to')?>"
                                   class="dropdown-item yd-dialog"><i class="iconfont icon-moveto"></i> <?= __('Move to')?></a></li>
                            <li><a href="javascript:;" data-dialog-id="<?= $page->uuid?>"
                                   data-title="<?= __('Move to trash')?>"
                                   data-content="<?= __('This action cannot be undone.  are you sure?')?>"
                                   data-socket="<?= SOCKET_HOST?>"
                                   data-token="<?= $token?>"
                                   data-uuid="<?= $page->uuid?>"
                                   data-id="<?= $page->id?>"
                                   class="delete-page text-danger dropdown-item"><i class="iconfont icon-recycle"></i> <?= __('Move to trash')?></a></li>
                        <?php }?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0 bgtransparent">
            <div class="page-preview" style="background-image: url(<?= $page->screen ? UPLOAD_SITE_URI.$page->screen : '/img/design.svg'?>)"></div>
        </div>
    </div>
<?php }?>
</div>
