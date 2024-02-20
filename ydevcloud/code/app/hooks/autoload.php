<?php
namespace app;

use \yangzie\YZE_Hook;

YZE_Hook::add_hook(YZE_HOOK_AUTO_LOAD_CLASS, function ( $class ) {
    //echo $class;
});
?>
