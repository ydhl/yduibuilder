<?php
namespace app\build;

use app\vendor\Env;
use yangzie\YZE_Request;
use function yangzie\__;
use function yangzie\yze_merge_query_string;

/**
 * 输出代码预览框架页面，左边页面菜单，右边代码
 */
$this->set_data('type', 'code');
$module = $this->get_data('module');
$project = $this->get_data('project');
$this->master_view = 'master/preview';
$pages = $module ? $module->get_pages() : [];
$curr_page = $this->get_data('curr_page');
$curr_page = $curr_page ?: reset($pages);
$frontend_framework = $project->get_setting_value('frontend_framework');
$package = Env::package();
$codeTypes = @$package[$frontend_framework]['codeType'];

$codeType = !$codeTypes ? '' : (@$_GET['code_type']?:array_keys($codeTypes)[0]);
$offset = "0px";
?>
<link rel="stylesheet" data-name="vs/editor/editor.main" href="/editor/min/vs/editor/editor.main.css"/>
<script>
    var require = { paths: { vs: '/editor/min/vs' } };
</script>
<script src="/editor/min/vs/loader.js"></script>
<script src="/editor/min/vs/editor/editor.main.nls.js"></script>
<script src="/editor/min/vs/editor/editor.main.js"></script>
<?php if ($codeTypes){
    $offset = "42px";
?>
<ul class="nav nav-tabs mb-2">
<?php foreach (array_keys($codeTypes) as $type){?>
    <li class="nav-item">
        <a class="nav-link <?= $codeType==$type ? 'active' :''?>" href="<?= yze_merge_query_string('',['code_type'=>$type])?>"><?=$type?></a>
    </li>
<?php }?>
</ul>
<?php }?>
<div  id="exportedCodeEditor" style="height: calc(100% - <?=$offset?>)"></div>
<script>
    var editor;
    $(function () {
        editor = monaco.editor.create(document.getElementById('exportedCodeEditor'), {
            value: '',
            roundedSelection: true,
            scrollBeyondLastLine: false,
            readOnly: true,
            language: '<?= $codeTypes? $codeTypes[$codeType] :'html'?>'
        })
        $.get("/code/page/<?= $curr_page->uuid?>?code_type=<?=$codeType?>&api_env=<?= $_GET['api_env']?>",{},function (code) {
            editor.setValue(code);
        });

    })
</script>
