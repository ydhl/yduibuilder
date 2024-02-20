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
$curr_module = $_GET['uuid'] ? find_by_uuid(Module_Model::CLASS_NAME, $_GET['uuid']) : null;
$curr_func = $_GET['funcid'] ? find_by_uuid(Function_Model::CLASS_NAME, $_GET['funcid']) : null;
if ($curr_func){
    $curr_module = $curr_func->get_module();
    $pages = $curr_func->get_pages();
}else if(@$_GET['type']=='popup'){
    $pages = Page_Model::from()->where("page_type='popup' and project_id=:pid and is_deleted=0")->select([':pid'=>$project->id]);
}
$token = $this->get_Data('token');
$modules = $project->get_modules();
$this->layout = '';
?>

<form id="choose-form" onsubmit="return false">
    <style>
        .page-preview {
            height: 200px;
            background-repeat: no-repeat;
            background-size: contain;
            background-position: center;
        }
    </style>
    <div class="d-flex pb-5 mb-5 p-3 eventbinder">
        <nav class="d-flex flex-column align-items-stretch border flex-grow-0 flex-shrink-0" style="width: 15rem">
            <div class="nav nav-pills flex-column">
                <?php foreach ($modules as $module){
                    $functions = $module->get_functions();
                    ?>
                    <div class="d-flex justify-content-between border-bottom rounded-0 align-items-center
                    <?= $curr_module->id==$module->id && !@$_GET['funcid'] ? "bg-primary text-white" : ""?>"
                         onclick="refresh(this)"
                         data-url="<?= SITE_URI?>project/<?= $project->uuid?>/choosepage?uuid=<?= $module->uuid?>">
                        <div class="nav-link text-truncate <?= $curr_module->id==$module->id && !@$_GET['funcid'] ? "text-white" : "text-reset "?>">
                            <i class="iconfont icon-module"></i> <?= $module->name?>
                        </div>
                    </div>
                    <div class="nav nav-pills flex-column">
                        <?php
                        foreach($functions as $function){?>
                            <div onclick="refresh(this)" data-url="<?= SITE_URI?>project/<?= $project->uuid?>/choosepage?uuid=<?= $module->uuid?>&funcid=<?= $function->uuid?>"
                                 class="d-flex justify-content-between border-bottom rounded-0 align-items-center <?= @$_GET['funcid']==$function->uuid ? "bg-primary text-white" : ""?>">
                                <div class="text-truncate ms-3 nav-link <?= @$_GET['funcid']==$function->uuid ? "text-white" : "text-reset "?>">
                                    <i class="iconfont icon-function"></i> <?= $function->name?></div>
                            </div>
                        <?php }?>
                    </div>
                <?php }?>

                <div class="nav nav-pills flex-column">
                    <div onclick="refresh(this)" data-url="<?= SITE_URI?>project/<?= $project->uuid?>/choosepage?type=popup"
                         class="d-flex justify-content-between border-bottom rounded-0 align-items-center <?= @$_GET['type']=='popup' ? "bg-primary text-white" : ""?>">
                        <div class="text-truncate nav-link <?= @$_GET['type']=='popup' ? "text-white" : "text-reset "?>">
                            <i class="iconfont icon-popup"></i> <?= __("Popup")?></div>
                    </div>
                </div>
            </div>
        </nav>
        <div class="flex-fill ms-3">
            <div class=" d-flex flex-wrap">
                <?php foreach ((array)$pages as $page){ ?>
                    <div class="card me-3 mb-3" id="<?= $page->uuid?>-choose" onclick="choose('<?= $page->uuid?>','<?= urlencode($page->name)?>')" style="width: 20rem;cursor: pointer">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="text-truncate flex-grow-1"><?= $page->name?></div>
                        </div>
                        <div class="card-body p-0">
                            <div class="bg-light page-preview" style="background-image: url(<?= $page->screen ? $page->screen : ''?>?<?= time()?>)"></div>
                        </div>
                    </div>
                <?php }?>
            </div>
        </div>
    </div>
    <div class="border-top fixed-bottom flex-shrink-0 d-flex justify-content-end p-3 bg-light">
        <button type="button" class="btn btn-secondary" onclick="callback(0)"><?= __('Cancel')?></button>
        <button type="button" class="btn btn-primary ms-3" onclick="callback(1)"><?= __('OK')?></button>
    </div>
</form>
<script>
    var pageid = "";
    var pagetitle = "";
    function choose(_pageid, _pagetitle) {
        $(".card").removeClass('border-primary').removeClass('shadow');
        $("#"+_pageid+'-choose').addClass('border-primary').addClass('shadow');
        pageid = _pageid;
        pagetitle = _pagetitle;
    }
    function callback(rst) {
        var event = new CustomEvent('yze_ajax.submitted', {"detail":{ success: true, page_title: rst ? decodeURI(pagetitle) : '', page_uuid: rst ? pageid : '' }});
        event.target = document.getElementById("choose-form")
        document.getElementById("choose-form").dispatchEvent(event)
    }
    function refresh(obj) {
        $.ajax({
            url: $(obj).attr('data-url'),
            headers: {
                token: "<?= $token?>"
            },
            type: 'GET',
            data: {},
            success: function (data, textStatus, jqXHR) {
                $("#choose-form").html($(data).html())
            },
            dataType: 'html'
        })
    }
</script>
