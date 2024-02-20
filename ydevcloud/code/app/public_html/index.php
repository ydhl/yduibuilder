<?php
/**
 * 该文件是系统的入口
 *
 * @category Framework
 * @package  Yangzie
 * @author   liizii <libol007@gmail.com>
 * @version  SVN: $Id$
 * @link     http://yangzie.yidianhulian.com
 *
 */

// 初始化系统的一些配置信息，比如系统变量，yangzie的包含路径及一些php的配置问题
require 'init.php';

//开始处理请求
\yangzie\yze_handle_request();
?>
