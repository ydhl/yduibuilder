<?php
namespace yangzie;
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $this->get_data("yze_page_title")?> － <?php echo APPLICATION_NAME?></title>
        <?php
        yze_css_bundle("all");
        yze_module_css_bundle();
        yze_js_bundle("all");
        yze_module_js_bundle();
        ?>
    </head>
    <body>
        <?php echo $this->content_of_view();?>
    </body>
</html>
