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
$curr_page= $this->get_data('curr_page');
$this->layout = "";
$modules = $project->get_modules();
$data = [];
foreach ($modules as $module){
    $data[] = [
        "id"=>$module->uuid,
        "parent"=>"#",
        "text"=>$module->name,
        "icon"=> 'iconfont icon-folder',
        "state"=>[
            "opened"=>$module->id == $curr_page->module_id,
            "disabled"=>true,
            "selected"=>false
        ]
    ];
    $functions = $module->get_functions();
    foreach ($functions as $function){
        $data[] = [
            "id"=>$function->uuid,
            "parent"=>$module->uuid,
            "text"=>$function->name,
            "icon"=> 'iconfont icon-folder',
            "state"=>[
                "disabled"=>false,
                "selected"=>$function->id == $curr_page->function_id
            ]
        ];
    }
}
?>
<div id="mf-tree" style="height: 500px;"></div>
<?php //这里的多的</div>和footer少的</div>是为了让modal在对话框中显示成正确的modal结构而做的hack?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-primary" onclick="recovery()"><?= __("Confirm")?></button>
<script>
    $(function () {
        $('#mf-tree').on('select_node.jstree', function (e, data) {
            // window.location.href = data.instance.get_node(data.selected[0]).original.url
        }).jstree({
            "core": {
                "data": <?= json_encode($data)?>

            },
            "plugins" : [ "wholerow" ]
        });
    });

    function recovery() {
        const selected = $('#mf-tree').jstree(true).get_selected(true);
        if (!selected) return
        $.post("/project/<?= $project->uuid?>/recovery", {page: "<?= $curr_page->uuid?>", to: selected[0].id}, function (rst){
            if (rst && rst.success){
                window.document.location.reload();
            }else{
                YDJS.toast(rst.msg, YDJS.ICON_ERROR);
            }
        })
    }
</script>
