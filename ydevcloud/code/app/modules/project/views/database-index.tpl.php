<?php
namespace app\project;
use \yangzie\YZE_Resource_Controller;
use \yangzie\YZE_Request;
use \yangzie\YZE_Redirect;
use \yangzie\YZE_RuntimeException;
use function yangzie\__;

$project = $this->get_data('project');
$menu = $this->get_data('menu');
$this->master_view = 'master/project';
?>
TODO 按功能展示所有的界面信息
