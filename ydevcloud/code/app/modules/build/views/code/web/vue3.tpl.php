<?php
namespace app\build;

use app\modules\build\views\code\web\bootstrap_vue\Page_View;

/**
 * vue3 代码结构
 */
$page = $this->get_data('page');
$moudule = $page->get_module();
$project = $moudule->get_project();
$config = json_decode(html_entity_decode($page->config), true);
$rootPath = '';
$pageName = $page->file?:$page->name;

$build = new Build_Model($this->controller, $page, 1, $rootPath);
$build->set_img_Asset_Path($rootPath."assets/img/");

$view = Page_View::create_View($build);

$styles = $view->build_style(false);

?>
<template>
<?php $view->output();?>
    <teleport to="body">
<?php $view->build_popup_ui(); ?>
    </teleport>
</template>
<script lang="ts">
<?php

$codeFragment = $view->build_code();
foreach ((array)@$codeFragment->get_import() as $package => $imports){
    if (!$imports){
        echo "import '{$package}'\r\n";
        continue;
    }
    $brace = @$imports['{}'];
    if ($brace) unset($imports['{}']);

    echo "import ";
    if ($imports['default']) echo $imports['default'];
    if ($imports['import']) echo '{ '.join(', ',array_unique($imports['import'])).' }';
    echo " from '{$package}'\r\n";
}
$declares = $codeFragment->get_declare();

foreach ($declares as $declare){
    echo "$declare\r\n";
}

echo "export default {\r\n";
echo "  name: '{$pageName}'";
if ($codeFragment->get_components()){
    echo ",\r\n  components: {\r\n    ".join(",\r\n    ", $codeFragment->get_components())."\r\n  }";
}
if ($codeFragment->has_setup()){
    echo ",\r\n";
    echo "  setup (props: any, context: any) {\r\n";
    if ($codeFragment->get_ref()) {
        $build->output_code($codeFragment->get_ref(), 1)."\r\n";
    }
    if ($codeFragment->get_computed()) {
        $build->output_code($codeFragment->get_computed(), 1)."\r\n";
    }
    if ($codeFragment->get_function()) {
        $build->output_code($codeFragment->get_function(), 1)."\r\n";
    }
    foreach ($codeFragment->get_lifecycle() as $lifecycle => $codes){
        echo "    {$lifecycle}(() => {";
        $build->output_code($codes, 1);
        echo "    })";
    }
    if ($codeFragment->get_return()) {
        echo "    return {\r\n";
        echo "      ".join(",\r\n      ", $codeFragment->get_return())."\r\n";
        echo "    }\r\n";
    }
    echo "  }";
}
echo "\r\n}\r\n";
?>
</script>
<style scoped>
<?php foreach ($styles as $selector=>$style){
echo "{$selector} {\r\n  ".$style.";\r\n}\r\n";
}?>
</style>
