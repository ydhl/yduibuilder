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
?>
<form id="recovery-form">
    <input type="hidden" name="page" value="<?= @$_GET['page']?>"/>
<table class="table table-bordered table-sm table-hover table-striped">
    <thead>
        <tr>
            <th></th>
            <th>
                <?= __('Module')?>
            </th>
            <th>
                <?= __('Function')?>
            </th>
        </tr>
    </thead>
<?php
foreach ($modules as $module){
    $functions = $module->get_functions();
    foreach ($functions as $function){
        if ($curr_page->function_id == $function->id) continue;
        ?>
        <tr>
            <td><input type="radio" name="to" value="<?= $function->uuid?>"></td>
            <td><?= $module->name.$curr_page->function_id?></td>
            <td><?= $function->name?></td>
        </tr>
<?php }
}?>
</table>
</form>
<?php //这里的</div>是为了让modal在对话框中显示成正确的modal结构而做的hack?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-primary" onclick="recovery()"><?= __("Confirm")?></button>
<script>
    function recovery() {
        $.post("/project/<?= $project->uuid?>/recovery", $("#recovery-form").serialize(), function (rst){
            if (rst && rst.success){
                window.document.location.reload();
            }else{
                YDJS.toast(rst.msg, YDJS.ICON_ERROR);
            }
        })
    }
</script>
