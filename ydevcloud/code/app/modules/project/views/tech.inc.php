<?php
namespace app\project;
use app\common\Option_Model;
use app\vendor\Env;
use TencentCloud\Msp\V20180319\Models\Project;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$project = $this->get_data("project");
$this->layout = '';
$packages = Env::package();
$allFrontends = Env::getFrontendAndFramework();
$allBackends = Env::getBackendAndFramework();
$endKind = $project->end_kind;

foreach ($allFrontends as $name=>$frontInfo) {
    if ($endKind && !in_array($endKind, $frontInfo['endKind'])) continue; // 修改的情况下，只显示选择的终端类型支持的前端框架
    $env[$name] = [
        'framework'=> [],
        'name'=> $frontInfo['name'],
        'env'=>'front',
        'endKind'=> $frontInfo['endKind']
    ];
    foreach ($frontInfo['framework'] as $framework){
        $frameworkInfo = $packages[$framework];
        $_ = [
            'name'=>$frameworkInfo['name'],
            'version'=> array_keys(@(array)$frameworkInfo['version']),
            'ui'=>[],
            'language'=>[]
        ];

        foreach (@(array)$frameworkInfo['language'] as $language){
            $_['language'][$language] = [
                'name'=>$packages[$language]['name'],
                'version'=>array_keys($packages[$language]['version'])
            ];
        }
        foreach (@(array)$frameworkInfo['ui'] as $ui){
            $_['ui'][$ui] = [
                'name'=>$packages[$ui]['name'],
                'version'=>array_keys($packages[$ui]['version'])
            ];
        }

        $env[$name]['framework'][$framework] = $_;
    }
}
foreach ($allBackends as $name => $backendInfo) {
    $env[$name] = [
        'env'=>'backend',
        'name'=> $frontInfo['name'],
        'framework'=> []
    ];
    foreach ($backendInfo['framework'] as $framework){
        $frameworkInfo = $packages[$framework];
        $_ = [
            'name'=>$frameworkInfo['name'],
            'version'=> array_keys($frameworkInfo['version']),
            'language'=>[]
        ];
        foreach (@(array)$frameworkInfo['language'] as $language){
            $_['language'][$language] = [
                'name'=>$packages[$language]['name'],
                'version'=>array_keys($packages[$language]['version'])
            ];
        }
        $env[$name]['framework'][$framework] = $_;
    }
}

$setting = $project ? $project->get_setting() : [];
?>
<div class="row mb-1">
    <label class="col-sm-4 col-form-label"><?= __('Type:')?></label>
    <div class="col-sm-8">
        <div class="form-control-plaintext"><?= $project->end_kind?></div>
    </div>
</div>
<div class="row mb-1">
    <label class="col-sm-4 col-form-label"><?= __('Frontend:')?></label>
    <div class="col-sm-8">
        <select class="form-select front" data-env="front" name="s-frontend">
            <?php foreach ($allFrontends as $frontend => $frontendInfo){
                if ($endKind && !in_array($endKind, $frontendInfo['endKind'])) continue; // 修改的情况下，只显示选择的终端类型支持的前端框架
            ?>
                <option value="<?= $frontend?>" <?= $setting['frontend']==$frontend ? "selected" : ""?>><?= $frontendInfo['name']?></option>
            <?php }?>
        </select>
    </div>
</div>
<div class="row mb-1">
    <label class="col-sm-4 col-form-label"><?= __('Framework:')?></label>
    <div class="col-sm-8">
        <div class="input-group">
            <select class="form-select front-framework" data-env="front" name="s-frontend_framework">
                <option value="<?= $setting['frontend_framework']?>"><?= $env[$setting['frontend']]['framework'][$setting['frontend_framework']]['name']?></option>
            </select>
            <select class="form-select front-framework-version" name="s-frontend_framework_version">
                <option value="<?= $setting['frontend_framework_version']?>"><?= $setting['frontend_framework_version']?></option>
            </select>
        </div>
    </div>
</div>
<div class="row mb-1">
    <label class="col-sm-4 col-form-label"><?= __('UI:')?></label>
    <div class="col-sm-8">
        <div class="input-group">
            <select class="form-select ui" name="s-ui" data-env="front">
                <option value="<?= $setting['ui']?>"><?= $env[$setting['frontend']]['framework'][$setting['frontend_framework']]['ui'][$setting['ui']]['name']?></option>
            </select>
            <select class="form-select ui-version" name="s-ui_version">
                <option value="<?= $setting['ui_version']?>"><?= $setting['ui_version']?></option>
            </select>
        </div>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-4 col-form-label"><?= __('Frontend Language:')?></label>
    <div class="col-sm-8">
        <div class="input-group">
            <select class="form-select front-language" data-env="front" name="s-frontend_language">
                <option value="<?= $setting['frontend_language']?>"><?= $env[$setting['frontend']]['framework'][$setting['frontend_framework']]['language'][$setting['frontend_language']]['name']?></option>
            </select>
            <select class="form-select front-language-version" name="s-frontend_language_version">
                <option value="<?= $setting['frontend_language_version']?>"><?= $setting['frontend_language_version']?></option>
            </select>
        </div>
    </div>
</div>
<div class="row mb-1">
    <label class="col-sm-4 col-form-label"><?= __('Backend:')?></label>
    <div class="col-sm-8">
        <select class="form-select backend" data-env="backend" name="s-backend">
            <?php foreach ($allBackends as $backend => $backendInfo){?>
                <option value="<?= $backend?>" <?= $setting['backend']==$backend ? "selected" : ""?>><?= $backendInfo['name']?></option>
            <?php }?>
        </select>
    </div>
</div>
<div class="row mb-1">
    <label class="col-sm-4 col-form-label"><?= __('Framework:')?></label>
    <div class="col-sm-8">
        <div class="input-group">
            <select class="form-select backend-framework" data-env="backend" name="s-framework">
                <option value="<?= $setting['framework']?>"><?= $env[$setting['backend']]['framework'][$setting['framework']]['name']?></option>
            </select>
            <select class="form-select backend-framework-version" name="s-framework_version">
                <option value="<?= $setting['framework_version']?>"><?= $setting['framework_version']?></option>
            </select>
        </div>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-4 col-form-label"><?= __('Backend Language:')?></label>
    <div class="col-sm-8">
        <div class="input-group">
            <select class="form-select backend-language" data-env="backend" name="s-backend_language">
                <option value="<?= $setting['backend_language']?>"><?= $env[$setting['backend']]['framework'][$setting['framework']]['language'][$setting['backend_language']]['name']?></option>
            </select>
            <select class="form-select backend-language-version" name="s-backend_language_version">
                <option value="<?= $setting['backend_language_version']?>"><?= $setting['backend_language_version']?></option>
            </select>
        </div>
    </div>
</div>
<script>
    var envInfo = <?= json_encode($env)?>;
    $(function (){
        // 加载环境
        $(".front, .backend").on("change", function (){
            var env = $(this).attr('data-env')
            var framework = $(this).val();
            var options = '';
            $('.' + env + '-framework').empty();
            if (framework) {
                for (var key in envInfo[framework]['framework']) {
                    var item = envInfo[framework]['framework'][key]
                    options += `<option value="${key}">${item.name}</option>`;
                }
                $('.' + env + '-framework').append(options);
            }
            $('.' + env + '-framework').trigger('change');
        })

        // 框架联动
        $(".front-framework, .backend-framework").on("change", function (){
            var end = $(this).attr('data-env')
            var env = $("." + end).val();
            var framework = $(this).val();
            var versionOptions = '';
            $('.' + end + '-framework-version').empty();
            if (framework){
                for (var key of envInfo[env]['framework'][framework]["version"]) {
                    versionOptions += `<option value="${key}">${key}</option>`;
                }
                $('.' + end + '-framework-version').append(versionOptions);
            }

            if (end=='front'){
                // ui
                var ui = $('.ui').val()
                $('.ui').empty();
                if (framework){
                    var uiOption = '';
                    for (var key in envInfo[env]['framework'][framework]["ui"]) {
                        var item = envInfo[env]['framework'][framework]['ui'][key];
                        uiOption += `<option value="${key}" ${ui==key ? 'selected' : ''}>${item.name}</option>`;
                    }
                    $('.ui').append(uiOption);
                }
                $('.ui').trigger('change');
            }

            // language
            $('.' + end + '-language').empty();
            if (framework){
                var lanOptions = '';
                for (var key in envInfo[env]['framework'][framework]["language"]) {
                    var item = envInfo[env]['framework'][framework]['language'][key];
                    lanOptions += `<option value="${key}">${item.name}</option>`;
                }
                $('.' + end + '-language').append(lanOptions);
            }
            $('.' + end + '-language').trigger('change');
        })
        // UI版本联动
        $(".ui").on("change", function (){
            var env = $(".front").val();
            var ui = $(this).val();
            var framework = $('.front-framework').val();
            $('.ui-version').empty();
            if (!framework) return;
            if (!envInfo[env]['framework'][framework]["ui"][ui]?.['version']) return;
            var uiOption = '';
            for (var key of envInfo[env]['framework'][framework]["ui"][ui]['version']) {
                uiOption += `<option value="${key}">${key}</option>`;
            }
            $('.ui-version').append(uiOption);
        })

        // 语言版本联动
        $(".front-language, .backend-language").on("change", function (){
            var end = $(this).attr('data-env')
            var env = $("." + end).val();
            var language = $(this).val();
            var framework = $('.'+end+'-framework').val();
            $('.' + end + '-language-version').empty();
            if (!framework) return;
            if (!envInfo[env]['framework'][framework]["language"][language]?.['version']) return;
            var option = '';
            for (var key of envInfo[env]['framework'][framework]["language"][language]['version']) {
                option += `<option value="${key}">${key}</option>`;
            }
            $('.' + end + '-language-version').append(option);
        })

        $(".front, .backend").trigger('change')
    })

</script>
