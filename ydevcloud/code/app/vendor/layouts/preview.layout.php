<?php
namespace yangzie;
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $this->get_data("yze_page_title")?> － <?php echo APPLICATION_NAME?></title>
    <script> var LANG='<?= YZE_Hook::do_hook(YZE_HOOK_GET_LOCALE)?>'</script>

    <link rel="stylesheet" href="/js/jstree/themes/default/style.css" />
    <?php
    yze_css_bundle("all");
    yze_module_css_bundle();
    yze_js_bundle("all");
    yze_module_js_bundle();
    ?>

    <script src="/js/jstree/jstree.js"></script>
</head>
<body>
<?php echo $this->content_of_view();?>
</body>
</html>
