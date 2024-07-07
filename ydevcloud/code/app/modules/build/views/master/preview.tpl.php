<?php
namespace app\build;

use app\project\Action_Model;
use app\project\Page_Bind_Event_Model;
use app\project\Page_Model;
use app\project\Project_Model;
use app\project\Project_Setting_Model;
use yangzie\YZE_Request;
use function yangzie\__;
use function yangzie\yze_merge_query_string;
$request = YZE_Request::get_instance();
$curr_module = $this->get_data('module');
$curr_page = $this->get_data('curr_page');
$project = $this->get_data('project');
$pages = $curr_module ? $curr_module->get_pages() : [];
$type = $this->get_data('type');
$curr_api_env = trim($request->get_from_get("api_env"));
$curr_page = $curr_page ?: reset($pages);
$has_api = Action_Model::from('a')
    ->left_join(Page_Model::CLASS_NAME,'p', 'p.id = a.page_id')
    ->where('p.project_id=:pid and a.bind_api_id!=0 and a.is_deleted=0 and a.page_id=:id')
    ->count('id', [":pid"=>$project->id,":id"=>$curr_page->id],'a');

$api_envs = Project_Setting_Model::get_setting_value($project->id, 'api_env');

$popups = [];
$components = [];
$subpages = [];
foreach (Page_Model::from()->where("page_type in ('popup', 'component', 'subpage') and project_id=:pid and is_deleted=0")
             ->select([':pid'=>$project->id]) as $page){
    if ($page->page_type == 'popup'){
        $popups[] = $page;
    }elseif ($page->page_type == 'subpage'){
        $subpages[] = $page;
    }else{
        $components[] = $page;
    }
}
$arg = $_GET;
$arg['page'] = $curr_page->uuid;
$has_data_bound = true;

$page_data = [];
foreach ($project->get_modules() as $module){
    $page_data[] = [
        "id"=>$module->uuid,
        "parent"=>"#",
        "text"=>$module->name,
        "icon"=> 'iconfont icon-folder',
        "state"=>[
            "opened"=>$module->id == $curr_module->id,
            "disabled"=>true,
            "selected"=>false
        ]
    ];
    foreach ($module->get_functions() as $function){
        $page_data[] = [
            "id"=>$function->uuid,
            "parent"=>$module->uuid,
            "text"=>$function->name,
            "icon"=> 'iconfont icon-folder',
            "state"=>[
                "opened"=>false,
                "disabled"=>true,
                "selected"=>false
            ]
        ];

        foreach ($function->get_pages() as $page){
            $page_data[] = [
                "id"=>$page->uuid,
                "parent"=>$function->uuid,
                "text"=>$page->name,
                "icon"=> 'iconfont icon-page',
                "url"=>yze_merge_query_string('',['page'=>$page->uuid,'module'=>$module->uuid,'type'=>'page']),
                "state"=>[
                    "opened"=>true,
                    "disabled"=>false,
                    "selected"=>$curr_page->id == $page->id
                ]
            ];
        }
    }
}

$page_data[] = [
    "id"=>'popup',
    "parent"=>"#",
    "text"=>__('Popup'),
    "icon"=> 'iconfont icon-popup',
    "state"=>[
        "opened"=>$_GET['type'] == 'popup',
        "disabled"=>true,
        "selected"=>false
    ]
];
foreach ($popups as $page){
    $page_data[] = [
        "id"=>$page->uuid,
        "parent"=>'popup',
        "text"=>$page->name,
        "icon"=> 'iconfont icon-page',
        "url"=> yze_merge_query_string('', ['page' => $page->uuid, 'module' => '', 'type' => 'popup']),
        "state"=>[
            "opened"=>true,
            "disabled"=>false,
            "selected"=>$curr_page->id == $page->id
        ]
    ];
}

$page_data[] = [
    "id"=>'component',
    "parent"=>"#",
    "text"=>__('UI Component'),
    "icon"=> 'iconfont icon-component',
    "state"=>[
        "opened"=>$_GET['type'] == 'component',
        "disabled"=>true,
        "selected"=>false
    ]
];
foreach ($components as $page){
    $page_data[] = [
        "id"=>$page->uuid,
        "parent"=>'component',
        "icon"=> 'iconfont icon-page',
        "url"=> yze_merge_query_string('',['page'=>$page->uuid,'module'=>'','type'=>'component']),
        "text"=>$page->name,
        "state"=>[
            "opened"=>true,
            "disabled"=>false,
            "selected"=>$curr_page->id == $page->id
        ]
    ];
}

$page_data[] = [
    "id"=>'subpage',
    "parent"=>"#",
    "text"=>__('Sub Page'),
    "icon"=> 'iconfont icon-subpage',
    "state"=>[
        "opened"=>$_GET['type'] == 'subpage',
        "disabled"=>true,
        "selected"=>false
    ]
];
foreach ($subpages as $page){
    $page_data[] = [
        "id"=>$page->uuid,
        "parent"=>'subpage',
        "icon"=> 'iconfont icon-subpage',
        "url"=> yze_merge_query_string('',['page'=>$page->uuid,'module'=>'','type'=>'subpage']),
        "text"=>$page->name,
        "state"=>[
            "opened"=>true,
            "disabled"=>false,
            "selected"=>$curr_page->id == $page->id
        ]
    ];
}
?>
<div class="preview">
    <div class="preview-menu">
        <div class="d-flex justify-content-between align-items-center">
            <a href="/project/<?= $project->uuid?>" class="text-decoration-none flex-shrink-0 me-4"><i class="iconfont icon-arrowleft"></i><?= __('Back')?></a>
            <h5 class="text-center text-truncate"><?= $project->name?></h5>
        </div>

        <div class="card">
            <div class="card-header p-1">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="btn-group-sm btn-group">
                        <?php if ($project->end_kind == 'mobile'){
                            $myarg = $arg;
                            $myarg['device']='mobile';
                            $myarg['module']=$curr_module->uuid;
                            ?>
                            <a href="<?= yze_merge_query_string('/preview/'.$project->uuid,$myarg)?>" class="btn btn-outline-primary btn-sm <?= $type=='ui' ? 'active' : ''?>">
                                <?= __('Preview')." <i class='iconfont icon-mobile'></i>"?>
                            </a>
                        <?php }else{?>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle  <?= $type=='ui' ? 'active' : ''?>" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?= __('Preview')." <i class='iconfont icon-".(@$_GET['device']?:'pc')."'></i>"?>
                                </button>
                                <ul class="dropdown-menu">
                                    <?php foreach (['pc'=>__('PC'),'tablet'=>__('Tablet'),'mobile'=>__('Portrait')] as $end_type=>$name){
                                        $myarg = $arg;
                                        $myarg['device']=$end_type;
                                        $myarg['module']=$curr_module->uuid;
                                        ?>
                                        <li><a class="dropdown-item" href="<?= yze_merge_query_string('/preview/'.$project->uuid,$myarg)?>"><i class="iconfont icon-<?= $end_type?>"></i> <?= $name?></a></li>
                                    <?php }?>
                                </ul>
                            </div>
                        <?php }?>
                        <a href="/code/<?= $project->uuid?>?module=<?= $curr_module->uuid?>&page=<?= $curr_page->uuid?>" class="btn btn-outline-primary <?= $type=='code' ? 'active' : ''?>"><?= __('Code')?></a>
                    </div>
                    <?php if ($has_api && $api_envs && $type!='code'){?>
                        <div class="btn-group ms-2">
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?= $curr_api_env?:__('not specified')?>
                                </button>
                                <ul class="dropdown-menu">
                                    <?php foreach ((array)$api_envs as $name=>$path){
                                        $myarg = $arg;
                                        $myarg['api_env'] = $name;
                                        $myarg['module']=$curr_module->uuid;
                                        ?>
                                        <li><a class="dropdown-item"
                                               href="<?= yze_merge_query_string(($type=='code'?'/code':'/preview').'/'.$project->uuid,$myarg)?>"><?= $name?></a></li>
                                    <?php }?>
                                </ul>
                            </div>
                        </div>
                    <?php }?>
                </div>
                <div class="d-flex align-items-center mt-2 gap-2">
                    <button type="button"  data-url="<?= Project_Model::get_ui_builder_url()?>"
                            data-uuid="<?= $curr_page->uuid?>" class="btn btn-outline-primary btn-sm run-ui-builder">
                        <?= __('Edit')?>
                    </button>
                    <button data-title="<?= __('Build Project')?>" data-size="large" data-url="/project/<?= $project->uuid?>/build.dlg" class="btn btn-sm btn-outline-primary yd-dialog"><?= __('Build')?></button>

                    <?php if ($has_data_bound && $type!='code'){
                        $myarg = $arg;
                        $myarg['mock']=@$_GET['mock'] ? 0 : 1;
                        ?>
                        <label class="d-flex align-items-center ms-2">
                            <a class="text-decoration-none" href="<?= yze_merge_query_string('/preview/'.$project->uuid,$myarg)?>">
                                <input type="checkbox" <?= $_GET['mock'] ? "checked" : ""?>>&nbsp;<?= __('Mock data')?>
                            </a>
                        </label>
                    <?php }?>
                </div>
            </div>
            <div id="page-tree" style="width: 100%;height:calc(100vh - 150px); overflow-x: hidden;"></div>
        </div>
    </div>
    <div class="preview-body pt-4">
        <?= $this->content_of_view()?>
    </div>
</div>

<script>
    $(function () {
        $('#page-tree').on('select_node.jstree', function (e, data) {
            window.location.href = data.instance.get_node(data.selected[0]).original.url
        }).jstree({
            "core": {
                "data": <?= json_encode($page_data)?>

            },
            "plugins" : [ "wholerow" ]
        });
    });
</script>
