<?php
namespace app\build;

use app\project\Project_Model;
use app\vendor\Env;

/**
 * web-html5 代码结构
 */

$code_type = strtolower($this->get_data('code_type'));
switch ($code_type) {
    case 'css':
        include 'html5-css.inc.php';
        break;
    case 'js':
        include 'html5-js.inc.php';
        break;
    case 'html':
    default:
        include 'html5-html.inc.php';
        break;
}
?>
