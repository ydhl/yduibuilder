<?php
namespace app\build;

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

$api_envs = Project_Setting_Model::get_setting_value($project->id, 'api_env');
$popups = [];
$components = [];
foreach (Page_Model::from()->where("page_type in ('popup','component') and project_id=:pid and is_deleted=0")
             ->select([':pid'=>$project->id]) as $page){
    if ($page->page_type == 'popup'){
        $popups[] = $page;
    }else{
        $components[] = $page;
    }
}
$arg = $_GET;
$arg['page'] = $curr_page->uuid;
?>
<div class="fixed-top d-flex align-items-center justify-content-center mt-1" style="margin-left: 215px">
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

    <button type="button"  data-url="<?= Project_Model::get_ui_builder_url()?>"
            data-uuid="<?= $curr_page->uuid?>" class="btn btn-outline-primary btn-sm ms-2 run-ui-builder">
        <i class='iconfont icon-ui'></i> <?= __('Edit with UIBuilder')?>
    </button>
</div>
<div class="preview">
    <div class="preview-menu">
        <div class="card">
            <div class="card-header p-1 d-flex justify-content-between">
                <a href="/project/<?= $project->uuid?>" class="btn btn-sm btn-light"><i class="iconfont icon-arrowleft"></i><?= __('Back')?></a>
                <button data-title="<?= __('Build Project')?>" data-size="large" data-url="/project/<?= $project->uuid?>/build.dlg" class="btn btn-sm btn-outline-primary yd-dialog"><?= __('Build Project')?></button>
            </div>
            <div class="card-body">
                <div class="text-center"><?= $project->name?></div>
            </div>
            <div class="accordion accordion-flush" id="module_list">
                <?php foreach ($project->get_modules() as $module){?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="header-<?= $module->uuid?>">
                        <button class="accordion-button <?= $module->id == $curr_module->id ? '' : 'collapsed'?>" type="button" data-bs-toggle="collapse" data-bs-target="#body-<?= $module->uuid?>" aria-expanded="true" aria-controls="body-<?= $module->uuid?>">
                            <?= $module->name?>
                        </button>
                    </h2>
                    <div id="body-<?= $module->uuid?>" class="accordion-collapse collapse <?= $module->id == $curr_module->id ? 'show' : ''?>" aria-labelledby="header-<?= $module->uuid?>">
                        <div class="accordion-body p-0">
                            <div class="list-group list-group-flush">
                                <?php foreach ($module->get_pages() as $page) {?>
                                    <a href="<?= yze_merge_query_string('',['module'=>$module->uuid,'type'=>'','page'=>$page->uuid])?>" class="list-group-item list-group-item-action <?= $curr_page->id == $page->id ? "active" : ""?>">
                                        <?= $page->name;?>
                                    </a>
                                <?php }?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php }?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="header-popup">
                        <button class="accordion-button <?= $_GET['type'] == 'popup' ? '' : 'collapsed'?>"
                                type="button" data-bs-toggle="collapse" data-bs-target="#body-popup" aria-expanded="true"
                                aria-controls="body-popup">
                            <?= __('Popup')?>
                        </button>
                    </h2>
                    <div id="body-popup" class="accordion-collapse collapse <?= $_GET['type'] == 'popup' ? 'show' : ''?>" aria-labelledby="header-popup">
                        <div class="accordion-body p-0">
                            <div class="list-group list-group-flush">
                                <?php foreach ($popups as $page) {?>
                                    <a href="<?= yze_merge_query_string('',['page'=>$page->uuid,'module'=>'','type'=>'popup'])?>" class="list-group-item list-group-item-action <?= $curr_page->id == $page->id ? "active" : ""?>">
                                        <?= $page->name;?>
                                    </a>
                                <?php }?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="header-component">
                        <button class="accordion-button <?= $_GET['type'] == 'component' ? '' : 'collapsed'?>"
                                type="button" data-bs-toggle="collapse" data-bs-target="#body-component" aria-expanded="true"
                                aria-controls="body-popup">
                            <?= __('UI Component')?>
                        </button>
                    </h2>
                    <div id="body-component" class="accordion-collapse collapse <?= $_GET['type'] == 'component' ? 'show' : ''?>" aria-labelledby="header-component">
                        <div class="accordion-body p-0">
                            <div class="list-group list-group-flush">
                                <?php foreach ($components as $component) {?>
                                    <a href="<?= yze_merge_query_string('',['page'=>$component->uuid,'module'=>'','type'=>'component'])?>" class="list-group-item list-group-item-action <?= $curr_page->id == $component->id ? "active" : ""?>">
                                        <?= $component->name;?>
                                    </a>
                                <?php }?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="preview-body pt-4">
        <?= $this->content_of_view()?>
    </div>
</div>
