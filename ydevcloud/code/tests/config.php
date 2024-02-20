<?php
putenv("TEST_PHP_EXECUTABLE=/usr/local/opt/php@7.4/bin/php");
putenv("TEST_PHP_DETAILED=0");//1或者0，设置日志的级别
//putenv("TEST_PHP_USER=./logs");//: 设置用户目录
putenv("TEST_PHP_LOG_FORMAT=LD");//: 日志的格式，LEOD。(.log .exp .out .diff)

?>
