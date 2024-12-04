<?php
namespace app\build;

use app\modules\build\views\code\web\bootstrap_vue\Page_View;
use app\modules\build\views\code\web\Vue_Code_Fragment;

/**
 * vue3 代码结构
 */
$page = $this->get_data('page');
$rootPath = '';

$build = new Build_Model($this->controller, $page, 1, $rootPath);
$build->set_img_Asset_Path($rootPath."assets/img/");

$view = Page_View::create_View($build);

$styles = $view->build_style(false);

ob_start();
$view->build_popup_ui();
$popup = ob_get_clean();
?>
<template>
<?php $view->output();
if ($popup){
?>
    <teleport to="body">
<?php echo $popup ?>
    </teleport>
<?php }?>
</template>
<script lang="ts" setup>
<?php

$codeFragment = $view->build_code();
foreach ((array)@$codeFragment->get_import() as $package => $imports){
    if (!$imports){
        echo "import '{$package}'\r\n";
        continue;
    }

    echo "import ";
    if ($imports['default']) echo $imports['default'];
    if ($imports['import']) echo '{ '.join(', ',array_unique($imports['import'])).' }';
    echo " from '{$package}'\r\n";
}
$declares = $codeFragment->get_declare();
foreach ($declares as $declare){
    echo "$declare\r\n";
}

$consts = $codeFragment->get_const();
foreach ($consts as $const => $config){
    echo "const {$const}".($config['type'] ? ":{$config['type']}" : '')." = {$config['value']}\r\n";
}
$lets = $codeFragment->get_let();
foreach ($lets as $let => $config){
    echo "let {$let}".($config['type'] ? ":{$config['type']}" : '')." = {$config['value']}\r\n";
}

$emits = $codeFragment->get_emit();
if ($emits){
    echo "const emit = defineEmits(".json_encode($emits).")".PHP_EOL;
}

$props = $codeFragment->get_prop();
if ($props){
    echo "const { ".join(', ', array_keys($props))." } = defineProps({".PHP_EOL;
    $codes = [];
    foreach ($props as $name => $prop){
        if (is_array($prop['type']) || $prop['default']){
            $config = $name.": {".PHP_EOL;
            if ($prop['default']){
                $config .= $build->space(1)."default: ".$prop['default'].','.PHP_EOL;
            }
            $config .= $build->space(1)."type: [".join(",", $prop['type']).'],'.PHP_EOL;
            $config .= "}".PHP_EOL;
            $codes[] = $config;
        }else{
            $codes[] = $name.": ".$prop['type'];
        }
    }
    $build->output_code(join(",".PHP_EOL, $codes), 1)."\r\n";
    echo "})".PHP_EOL;
}

if ($codeFragment->get_ref()) {
    $build->output_code($codeFragment->get_ref(), 0)."\r\n";
}
if ($codeFragment->get_computed()) {
    $build->output_code($codeFragment->get_computed(), 0)."\r\n";
}

$build->output_code($codeFragment->get_section_codes(Vue_Code_Fragment::SECTION_FUNCTION),0);

foreach ($codeFragment->get_lifecycle() as $lifecycle => $codes){
    echo "{$lifecycle}(() => {".PHP_EOL;
    $build->output_code($codes, 1);
    echo "})";
}
?>

</script>
<style>
<?php
foreach ($styles as $selector => $style){
    if (!trim($style)) continue;
    $build->output_code($selector.' {', 0);
    $build->output_code($style, 1);
    $build->output_code('}', 0);
}
?>
</style>
