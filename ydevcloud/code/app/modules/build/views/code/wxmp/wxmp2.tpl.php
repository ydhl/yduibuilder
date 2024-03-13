<?php
namespace app\build;


/**
 * 小程序代码编译，根据code_type变成编译对应的代码，比如wxml界面，wcss样式，js代码，json设置
 */
$code_type = strtolower($this->get_data('code_type'));
switch ($code_type) {
    case 'wxss':
        include 'wxmp-wxss.inc.php';
        break;
    case 'js':
        include 'wxmp-js.inc.php';
        break;
    case 'json':
        include 'wxmp-json.inc.php';
        break;
    case 'wxml':
    default:
        include 'wxmp-wxml.inc.php';
        break;
}
?>
