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
$this->master_view = 'master/structure';
$page = $project->get_home_page();
$loginUser = YZE_Hook::do_hook(YZE_HOOK_GET_LOGIN_USER);
$member = $project->get_member($loginUser->id);
$token = $this->get_data('token');
?>
<div class="input-group mb-3 w-50">
    <span class="input-group-text"><?= __('Reset home page to')?></span>
    <select class="form-control" id="homePageId">
        <option value="0"><?= __('Not set home page')?></option>
        <?php
        foreach ($project->get_module_function_pages() as $moduleInfo){
            foreach ($moduleInfo['function'] as $functionInfo){
        ?>
            <optgroup label="<?= $moduleInfo['name'].'/'.$functionInfo['name']?>">
        <?php
                foreach ($functionInfo['page'] as $_page){
                    if ($_page->page_type!='page')continue;
        ?>
        <option value="<?= $_page->uuid?>" <?= $project->home_page_id==$_page->id ? 'selected' : ''?>><?= ($project->home_page_id==$_page->id ? '('.__('Home Page').') ' : '').$_page->name?></option>
        <?php
                }
        ?>
            </optgroup>
        <?php
            }
        }?>
    </select>
    <button type="button" class="btn btn-primary yd-spin-btn" onclick="update(this)"><?= __('Update')?></button>
</div>

<?php if($page){?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="text-truncate flex-grow-1"><?= $page->name?></div>
        <div class="btn-group btn-group-sm">
            <a href="/preview/<?=$project->uuid?>?module=<?= $page->get_module()->uuid?>&page=<?= $page->uuid?>" class="btn btn-outline-secondary btn-sm" title="<?= __('Preview')?>"><i class="iconfont icon-run"></i></a>
            <a href="#" data-url="<?= Project_Model::get_ui_builder_url()?>" data-uuid="<?= $page->uuid?>" title="<?= __('Build UI')?>" class="run-ui-builder btn btn-sm btn-outline-secondary"><i class="iconfont icon-uibuilder"></i></a>
            <div class="dropdown btn btn-outline-secondary btn-sm">
                <i class="iconfont icon-more" type="button" data-bs-toggle="dropdown" aria-expanded="false"></i>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a href="/project/<?= $project->uuid?>/pageversion?page=<?= $page->uuid?>" class="dropdown-item"><i class="iconfont icon-versions"></i> <?= __('Version')?> (<?= $page->get_Version_Count()?>)</a></li>
                    <?php if ($member && $member->can_edit()){?>
                        <li><a href="javascript:;" data-dialog-id="<?= $page->uuid?>"
                               data-title="<?= __('Move to trash')?>"
                               data-content="<?= sprintf(__('This action cannot be undone.  are you sure?'), "<code>{$page->name}</code>")?>"
                               data-socket="<?= SOCKET_HOST?>"
                               data-token="<?= $token?>"
                               data-uuid="<?= $page->uuid?>"
                               data-id="<?= $page->id?>"
                               class="delete-page text-danger dropdown-item"><i class="iconfont icon-remove"></i> <?= __('Move to trash')?></a></li>
                    <?php }?>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="bg-light page-preview" style="height: 500px;background-image: url(<?= $page->screen ? $page->screen : ''?>?<?= time()?>)"></div>
    </div>
</div>
<?php }else{?>
    <div class="alert alert-info"><?= __('Not set home page，The home page is the default page when app launched')?></div>
<?php }?>
<script>
    function update(obj){
        var homePageId = $("#homePageId").val();
        $.post('/api/update/homepage', {pageid: homePageId, projectid: "<?= $project->uuid?>"}, function (rst){
            YDJS.spin_clear(obj);
            if (rst?.success){
                window.location.reload();
            }else{
                YDJS.alert(rst?.msg || '<?= __('Update Failed')?>', 'Oops', YDJS.ICON_ERROR);
            }
        }, "json");
    }
</script>
