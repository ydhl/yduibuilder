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
$token = $this->get_data('token');
$this->master_view = 'master/project';


$project_structure = [];
foreach ($project->get_modules() as $module){
    $project_structure[] = [
        "id"=>$module->uuid,
        "parent"=>"#",
        "text"=>$module->name.'('.$module->page_count().')',
        "icon"=> 'iconfont icon-folder',
        "url"=> yze_merge_query_string('', ['module_uuid' => $module->uuid, 'function_uuid'=>'', 'type'=>'']),
        "state"=>[
            "opened"=>$module->id == $curr_module->id,
            "disabled"=>false,
            "selected"=>$module->id == $curr_module->id && !$curr_function
        ]
    ];
    foreach ($module->get_functions() as $function){
        $project_structure[] = [
            "id"=>$function->uuid,
            "parent"=>$module->uuid,
            "text"=>$function->name.'('.$function->page_count().')',
            "icon"=> 'iconfont icon-folder',
            "url"=> yze_merge_query_string('', ['function_uuid' => $function->uuid, 'module_uuid'=>'', 'type'=>'']),
            "state"=>[
                "opened"=>$function->id == $curr_function->id,
                "disabled"=>false,
                "selected"=>$function->id == $curr_function->id
            ]
        ];
    }
}

$project_structure[] = [
    "id"=>'popup',
    "parent"=>"#",
    "text"=>__('Popup').'('.$project->popup_count().')',
    "icon"=> 'iconfont icon-popup',
    "url"=> yze_merge_query_string('', ['type' => 'popup','function_uuid'=>'','module_uuid'=>'']),
    "state"=>[
        "opened"=>$_GET['type'] == 'popup',
        "disabled"=>false,
        "selected"=>$_GET['type'] == 'popup'
    ]
];

$project_structure[] = [
    "id"=>'component',
    "parent"=>"#",
    "text"=>__('UI Component').'('.$project->component_count().')',
    "icon"=> 'iconfont icon-component',
    "url"=> yze_merge_query_string('', ['type' => 'component','function_uuid'=>'','module_uuid'=>'']),
    "state"=>[
        "opened"=>$_GET['type'] == 'component',
        "disabled"=>false,
        "selected"=>$_GET['type'] == 'component'
    ]
];
$project_structure[] = [
    "id"=>'subpage',
    "parent"=>"#",
    "text"=>__('Sub Page').'('.$project->subpage_count().')',
    "icon"=> 'iconfont icon-subpage',
    "url"=> yze_merge_query_string('', ['type' => 'subpage','function_uuid'=>'','module_uuid'=>'']),
    "state"=>[
        "opened"=>$_GET['type'] == 'subpage',
        "disabled"=>false,
        "selected"=>$_GET['type'] == 'subpage'
    ]
];
$project_structure[] = [
    "id"=>'recycle',
    "parent"=>"#",
    "text"=>__('Recycle').'('.$project->recycle_count().')',
    "icon"=> 'iconfont icon-recycle',
    "url"=> yze_merge_query_string('', ['type' => 'recycle','function_uuid'=>'','module_uuid'=>'']),
    "state"=>[
        "opened"=>$_GET['type'] == 'recycle',
        "disabled"=>false,
        "selected"=>$_GET['type'] == 'recycle'
    ]
];
?>
<div class="d-flex justify-content-between align-items-start">
    <div id="project-tree" class="flex-shrink-0 border-light border-1 bg-light me-3 p-1" style="width: 250px;height: 100vh;overflow-x: hidden"></div>
    <div class="flex-grow-1">
        <?php
        if ($_GET['type']=='recycle'){?>
            <button type="button" class="btn btn-danger yd-confirm-post mb-3"
                    data-title="<?= __('Empty Trash')?>"
                    data-redirect="reload"
                    data-url="/project/<?= $project->uuid?>/emptytrash"
                    data-content="<?= __('This action cannot be undone, all content in page will be lost.  are you sure?')?>"><?= __('Empty Trash')?></button>
            <?php
        }
        ?>
        <div class=" d-flex flex-wrap ">
        <?php
        if (!$pages){
            if (!$curr_module && !$curr_function){?>
                <div class="d-flex align-items-center flex-column justify-content-center" style="height: 500px;width: 100%">
                    <div class="text-muted"><?= __("please select module or function")?></div>
                    <button class="btn btn-primary mt-3 yd-dialog" data-url="/project/<?= $project->uuid?>/addmodule"><?= __('Add module')?></button>
                </div>
            <?php }else{?>
            <div class="d-flex align-items-center flex-column justify-content-center" style="height: 500px;width: 100%" >
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
            <?php
        }
    }

    foreach ($pages as $page){ ?>
        <div class="card me-3 mb-3" style="width: 15rem">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="text-truncate flex-grow-1" title="<?= $page->name?>"><?= $page->name?></div>
                <div class="d-flex align-items-center">
                    <a href="/preview/<?= $project->uuid?>?module=<?= $page->get_module()->uuid?>&page=<?= $page->uuid?>" class="text-decoration-none p-1" title="<?= __('Preview')?>"><i class="iconfont icon-run"></i></a>
                    <a href="#" data-url="<?= Project_Model::get_ui_builder_url()?>" data-uuid="<?= $page->uuid?>" title="<?= __('Build UI')?>" class="run-ui-builder p-1 text-decoration-none"><i class="iconfont icon-edit"></i></a>
                    <div class="dropdown text-decoration-none p-1">
                        <i class="iconfont icon-more text-primary" data-bs-toggle="dropdown" aria-expanded="false"></i>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a href="/project/<?= $project->uuid?>/pageversion?page=<?= $page->uuid?>" class="dropdown-item"><i class="iconfont icon-versions"></i> <?= __('Version')?> (<?= $page->get_Version_Count()?>)</a></li>
                            <?php if ($member && $member->can_edit()){?>
                                <?php if($_GET['type']=='recycle'){
                                   ?>
                                        <li><a href="javascript:;"
                                           data-title="<?= __('Delete Page')?>"
                                           data-content="<?= __('This action cannot be undone, all content in page will be lost.  are you sure?')?>"
                                           data-url="/project/<?=$project->uuid?>/recycle?page=<?= $page->uuid?>"
                                           data-redirect="reload"
                                           class="dropdown-item yd-confirm-post"><i class="iconfont icon-remove"></i> <?= __('Delete Page')?></a></li>
                                        <li><a href="javascript:;"
                                           data-url="/project/<?=$project->uuid?>/recovery?page=<?= $page->uuid?>"
                                           data-title="<?= __('Recovery')?>"
                                           class="dropdown-item yd-dialog"><i class="iconfont icon-moveto"></i> <?= __('Recovery')?></a></li>
                                    <?php
                                    }else{?>
                                    <li><a javascript=":;"
                                           data-url="/project/<?= $project->uuid?>/recovery?page=<?= $page->uuid?>"
                                           data-title="<?= __('Move page to')?>"
                                           class="dropdown-item yd-dialog"><i class="iconfont icon-moveto"></i> <?= __('Move to')?></a></li>
                                    <li><a href="javascript:;" data-dialog-id="<?= $page->uuid?>"
                                           data-title="<?= __('Move to trash')?>"
                                           data-content="<?= __('are you sure?')?>"
                                           data-socket="<?= SOCKET_HOST?>"
                                           data-token="<?= $token?>"
                                           data-uuid="<?= $page->uuid?>"
                                           data-id="<?= $page->id?>"
                                           class="delete-page text-danger dropdown-item"><i class="iconfont icon-recycle"></i> <?= __('Move to trash')?></a></li>
                            <?php }}?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-0 bgtransparent">
                <div class="page-preview" style="background-image: url(<?= $page->screen ? SITE_URI.'image?file='.urlencode($page->screen).'&time='.time() : '/img/design.svg'?>)"></div>
            </div>
        </div>
    <?php }?>
        </div>
    </div>
</div>

<script>
    $(function () {
        $('#project-tree').on('select_node.jstree', function (e, data) {
            window.location.href = data.instance.get_node(data.selected[0]).original.url
        }).jstree({
            "core": {
                "data": <?= json_encode($project_structure)?>

            },
            "plugins" : [ "wholerow" ]
        });
    });
</script>
